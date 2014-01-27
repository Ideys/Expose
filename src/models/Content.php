<?php

use Symfony\Component\Security\Core\SecurityContext;

/**
 * App content manager.
 */
class Content
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var array
     */
    protected $sections = array();

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var string
     */
    private $sqlSelectSection =
       'SELECT s.id, s.expose_section_id, s.type, s.slug,
               s.homepage, s.visibility, s.hierarchy,
               t.title, t.description, t.parameters
        FROM expose_section AS s
        LEFT JOIN expose_section_trans AS t
        ON t.expose_section_id = s.id ';

    /**
     * @var string
     */
    private $sqlSelectItem =
       'SELECT i.*, t.title, t.description, t.content, t.parameters
        FROM expose_section_item AS i
        LEFT JOIN expose_section_item_trans AS t
        ON t.expose_section_item_id = i.id ';

    const CONTENT_GALLERY   = 'gallery';
    const CONTENT_VIDEO     = 'video';
    const CONTENT_PAGE      = 'page';
    const CONTENT_FORM      = 'form';
    const CONTENT_DIR       = 'dir';

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param \Silex\Application $app
     */
    public function __construct(\Silex\Application $app)
    {
        $this->db = $app['db'];
        $this->translator = $app['translator'];
        $this->formFactory = $app['form.factory'];
        $this->language = $this->translator->getLocale();
    }

    /**
     * Return default section model
     *
     * @return array
     */
    public function getSectionModel()
    {
        return array(
            'expose_section_id' => null,
            'type' => self::CONTENT_GALLERY,
            'title' => null,
            'description' => null,
            'parameters' => array(),
            'visibility' => 'public',
        );
    }

    /**
     * Return default item model
     *
     * @return array
     */
    public function getItemModel()
    {
        return array(
            'expose_section_id' => null,
            'type' => self::CONTENT_PAGE,
            'title' => null,
            'description' => null,
            'content' => null,
            'path' => null,
            'parameters' => array(),
        );
    }

    /**
     * Return sections.
     *
     * @return array
     */
    public function findSections()
    {
        if (!empty($this->sections)) {
            return $this->sections;
        }

        $sql = $this->sqlSelectSection .
           'WHERE t.language = ?
            ORDER BY s.hierarchy ASC';
        $sections = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and add sections tree
        foreach ($sections as $section) {
            $this->sections[$section['id']] = $section + array('sections' => array());
        }

        // Generate tree structure from raw datas
        foreach ($this->sections as $id => $section) {
            $parentSectionId = $section['expose_section_id'];
            if ($parentSectionId > 0) {
                $this->sections[$parentSectionId]['sections'][] = $section;
                unset($this->sections[$id]);
            }
        }

        return $this->sections;
    }

    /**
     * Return all items.
     *
     * @return array
     */
    public function findItems()
    {
        if (!empty($this->items)) {
            return $this->items;
        }

        $sql = $this->sqlSelectItem .
           'WHERE t.language = ?
            ORDER BY i.hierarchy ASC';
        $items = $this->db->fetchAll($sql, array($this->language));

        //Group by section ids
        foreach ($items as $item) {
            $this->items[(int)$item['expose_section_id']][] = $item;
        }

        return $this->items;
    }

    /**
     * Return a section.
     *
     * @param integer $id
     * @return array
     */
    public function findSection($id)
    {
        $sql = $this->sqlSelectSection .
           'WHERE s.id = ?
            AND t.language = ?
            ORDER BY s.hierarchy ASC';
        $section = $this->db->fetchAssoc($sql, array($id, $this->language));

        static::hydrateParameters($section);

        return $section;
    }

    /**
     * Return a section.
     *
     * @param string $slug Section slug
     * @return array
     */
    public function findSectionBySlug($slug)
    {
        $sql = $this->sqlSelectSection .
           'WHERE s.slug = ?
            AND t.language = ?
            ORDER BY s.hierarchy ASC';
        $section = $this->db->fetchAssoc($sql, array($slug, $this->language));

        static::hydrateParameters($section);

        return $section;
    }

    /**
     * Return a section.
     *
     * @return array
     */
    public function findHomepage()
    {
        $sql = $this->sqlSelectSection .
           'WHERE s.homepage = 1
            AND t.language = ?
            ORDER BY s.hierarchy ASC';
        $homepageSection = $this->db->fetchAssoc($sql, array($this->language));

        // Generate default homepage
        if (false === $homepageSection) {
            $settings = new Settings($this->db);
            $section = $this->addSection(array(
                'type' => self::CONTENT_PAGE,
                'title' => $settings->name,
            ));
            $this->addItem(array(
                'expose_section_id' => $section['id'],
                'type' => self::CONTENT_PAGE,
                'title' => $settings->name,
                'content' => '<div id="homepage"><h1>'.$settings->name.'</h1></div>',
            ));
            $this->defindHomepage($section['id']);
            $homepageSection = $this->findHomepage();
        }

        return $homepageSection;
    }

    /**
     * Define the homepage section.
     *
     * @param integer $sectionId
     */
    public function defindHomepage($sectionId)
    {
        // Reset old homepage
        $this->db->update(
            'expose_section',
            array('homepage' => 0, 'visibility' => 'hidden'),
            array('homepage' => 1)
        );
        $this->db->update(
            'expose_section',
            array('homepage' => 1, 'visibility' => 'public'),
            array('id' => $sectionId)
        );
    }

    /**
     * Return a section.
     *
     * @param integer $id Section id
     * @return array
     */
    public function findSectionItems($id)
    {
        $sql = $this->sqlSelectItem .
           'WHERE i.expose_section_id = ?
            AND t.language = ?
            ORDER BY i.hierarchy ASC';
        $entities = $this->db->fetchAll($sql, array($id, $this->language));

        $items = array();
        foreach ($entities as $entity) {
            $items[$entity['id']] = $entity;
            static::hydrateParameters($items[$entity['id']]);
        }

        return $items;
    }

    /**
     * Create a new section.
     *
     * @return array $section
     */
    public function addSection($section)
    {
        $section = array_merge($this->getSectionModel(), $section);

        $this->db->insert('expose_section', array(
            'expose_section_id' => $section['expose_section_id'],
            'type' => $section['type'],
            'slug' => $this->uniqueSlug($section['title']),
            'visibility' => $section['visibility'],
        ) + $this->blameAndTimestampData(0));

        $section['id'] = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $section['id'],
            'title' => $section['title'],
            'description' => $section['description'],
            'language' => $this->language,
        ));

        return $section;
    }

    /**
     * Edit a section.
     *
     * @return array Section
     */
    public function updateSection($section)
    {
        $section = array_merge($this->getSectionModel(), $section);
        static::refreshParameters($section);

        // Update section
        $this->db->update('expose_section', array(
            'slug' => $this->uniqueSlug($section['title'], $section['id']),
            'expose_section_id' => $section['expose_section_id'],
        ) + $this->blameAndTimestampData($section['id']),
        array('id' => $section['id']));

        // Update translated section attributes
        $this->db->update('expose_section_trans', array(
            'title' => $section['title'],
            'description' => $section['description'],
            'parameters' => serialize($section['parameters']),
        ), array('expose_section_id' => $section['id'], 'language' => $this->language));
    }

    /**
     * Create a new section.
     *
     * @return integer Section id
     */
    public function deleteSection($id)
    {
        // Delete section items
        $items = $this->findSectionItems($id);
        foreach ($items as $item) {
            $this->deleteItem($item['id']);
        }

        // Delete section's translations
        $this->db->delete('expose_section_trans', array('expose_section_id' => $id));
        // Delete section
        $rows = $this->db->delete('expose_section', array('id' => $id));

        return (0 < $rows);
    }

    /**
     * Toggle section frontend visibility.
     *
     * @param integer $id
     * @return boolean
     */
    public function toggleSection($id, $visibility)
    {
        if (!in_array($visibility, static::getSectionVisibilities())) {
            return false;
        }

        $rows = $this->db->update('expose_section', array(
            'visibility' => $visibility,
        ), array('id' => $id));

        return $rows > 0;
    }

    /**
     * Increments slugs for identical name sections:
     * new-section / new-section-2 / new-section-4 => new-section-5
     *
     * @param string $title
     * @return string
     */
    protected function uniqueSlug($title, $id = 0)
    {
        $slug = slugify($title);

        $sections = $this->db->fetchAll(
            'SELECT slug FROM expose_section WHERE slug LIKE ? AND id != ?',
            array($slug.'%', $id)
        );

        $namesakes = array();
        foreach($sections as $section) {
            $e = explode('-', $section['slug']);
            $prefix = array_pop($e);
            $namesakes[] = (int)$prefix;
        }

        if (!empty($namesakes)) {
            sort($namesakes);
            $lastIncr = array_pop($namesakes);
            $slug .= '-' . (++$lastIncr);
        }

        return $slug;
    }

    /**
     * Insert a new content.
     *
     * @return array $item
     */
    public function addItem($item)
    {
        $item = array_merge($this->getItemModel(), $item);

        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $item['expose_section_id'],
            'type' => $item['type'],
            'slug' => slugify($item['title']),
            'path' => $item['path'],
        ) + $this->blameAndTimestampData(0));

        static::refreshParameters($item);
        $item['id'] = $this->db->lastInsertId();
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $item['id'],
            'title' => $item['title'],
            'description' => $item['description'],
            'content' => $item['content'],
            'parameters' => serialize($item['parameters']),
            'language' => $this->language,
        ));

        return $item;
    }

    /**
     * Update a content.
     *
     * @return integer Item id
     */
    public function editItem($item)
    {
        $item = array_merge($this->getItemModel(), $item);
        static::refreshParameters($item);

        $this->db->update(
            'expose_section_item',
            array(
                'path' => $item['path'],
            ) + $this->blameAndTimestampData($item['id']),
            array('id' => $item['id'])
        );
        $this->db->update(
            'expose_section_item_trans',
            array(
                'title' => $item['title'],
                'description' => $item['description'],
                'parameters' => serialize($item['parameters']),
                'content' => $item['content'],
            ),
            array(
                'expose_section_item_id' => $item['id'],
                'language' => $this->language,
            )
        );
    }

    /**
     * Update item title and description.
     *
     * @param integer $id
     * @param string  $title
     * @param string  $description
     */
    public function updateItemTitle($id, $title, $description = null)
    {
        $data = array('title' => $title);
        if (null !== $description) {
            $data += array('description' => $description);
        }

        $this->db->update(
            'expose_section_item_trans',
            $data,
            array(
                'expose_section_item_id' => $id,
                'language' => $this->language,
            )
        );
    }

    /**
     * Delete an item.
     *
     * @param integer $id
     * @return boolean
     */
    public function deleteItem($id)
    {
        // Delete item's translations
        $this->db->delete('expose_section_item_trans', array('expose_section_item_id' => $id));
        // Delete item
        $rows = $this->db->delete('expose_section_item', array('id' => $id));

        return (0 < $rows);
    }

    /**
     * Return the create section form.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm()
    {
        $form = $this->sectionForm($this->getSectionModel());

        return $form->getForm();
    }

    /**
     * Return the section edit form.
     *
     * @param array $section
     * @return \Symfony\Component\Form\Form
     */
    public function editForm($section)
    {
        $form = $this->sectionForm($section)
            ->remove('type')
        ;

        return $form->getForm();
    }

    /**
     * Return section form builder.
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function sectionForm($entity)
    {
        $dirsChoice = array();
        foreach ($this->findSections() as $section) {
            if ('dir' === $section['type']) {
                $dirsChoice[$section['id']] = $section['title'];
            }
        }

        $form = $this->formFactory->createBuilder('form', $entity)
            ->add('type', 'choice', array(
                'choices'       => Content::getContentTypesChoice(),
                'label'         => 'content.type',
            ))
            ->add('title', 'text', array(
                'label'         => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('description', 'textarea', array(
                'required'      => false,
                'label'         => 'section.description',
                'attr' => array(
                    'placeholder' => 'section.description',
                ),
            ))
            ->add('expose_section_id', 'choice', array(
                'choices'       => $dirsChoice,
                'required'      => false,
                'label'         => 'content.dir',
                'empty_value'   => 'content.root',
            ))
            ->add('visibility', 'choice', array(
                'choices'       => Content::getSectionVisibilityChoice(),
                'label'         => 'section.visibility',
            ))
        ;

        return $form;
    }

    /**
     * Hydrate custom parameters attributes.
     *
     * @param array $entity
     */
    protected static function hydrateParameters(&$entity)
    {
        if (null === $entity['parameters']) {
            $entity['parameters'] = array();
        } elseif (!is_array($entity['parameters'])) {
            $entity['parameters'] = unserialize($entity['parameters']);
        }

        foreach ($entity['parameters'] as $paramLabel => $paramValue) {
            $entity['parameter_'.$paramLabel] = $paramValue;
        }
    }

    /**
     * Refresh entity custom parameters attributes.
     *
     * @param array $entity
     */
    protected static function refreshParameters(&$entity)
    {
        foreach ($entity as $paramLabel => $paramValue) {

            if (false !== strstr($paramLabel, 'parameter_')) {
                $entity['parameters']
                       [str_replace('parameter_', '', $paramLabel)] = $paramValue;
            }
        }
    }

    /**
     * Return content types keys.
     *
     * @return array
     */
    public static function getContentTypes()
    {
        return array(
            self::CONTENT_GALLERY,
            self::CONTENT_VIDEO,
            self::CONTENT_PAGE,
            self::CONTENT_FORM,
            self::CONTENT_DIR,
        );
    }

    /**
     * Return content types keys and trans values
     * Used on select forms.
     *
     * @return array
     */
    public static function getContentTypesChoice()
    {
        $keys = static::getContentTypes();
        $values = array_map(function($item){
            return 'content.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * Return content visibility states.
     *
     * @return array
     */
    public static function getSectionVisibilities()
    {
        return array('public', 'private' ,'hidden' ,'closed');
    }

    /**
     * Return content visibility choices.
     *
     * @return array
     */
    public static function getSectionVisibilityChoice()
    {
        return array(
            'public' => 'section.visibility.public',
            'private' => 'section.visibility.private',
            'hidden' => 'section.visibility.hidden',
            'closed' => 'section.visibility.closed',
        );
    }

    /**
     * Define user id to blame next persisted data.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext  $security
     * @return Content
     */
    public function blame(SecurityContext $security)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Define user author and timestamp for persisted data.
     *
     * @param integer $id
     * @return array
     */
    private function blameAndTimestampData($id)
    {
        $datetime = (new \DateTime())->format('c');
        if ($this->security instanceof SecurityContext) {
            $loggedUser = $this->security->getToken()->getUser();
            $user = $this->db->fetchAssoc('SELECT id FROM expose_user WHERE username = ?', array(
                $loggedUser->getUsername(),
            ));
            $userId = $user['id'];
        } else {
            $userId = null;
        }

        return array(
            'updated_by' => $userId,
            'updated_at' => $datetime,
        ) + (($id == 0) ? array(
            'created_by' => $userId,
            'created_at' => $datetime,
        ) : array());
    }
}
