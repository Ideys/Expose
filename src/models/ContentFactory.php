<?php

/**
 * App content manager.
 */
class ContentFactory
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
     * @var string
     */
    private $sqlSelectSection =
       'SELECT s.id, s.expose_section_id, s.type, s.slug,
               s.homepage, s.visibility, s.hierarchy,
               t.title, t.description, t.parameters, t.language
        FROM expose_section AS s
        LEFT JOIN expose_section_trans AS t
        ON t.expose_section_id = s.id ';

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
        $this->security = $app['security'];
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
            'homepage' => '0',
            'language' => $this->language,
        );
    }

    /**
     * Return default item model
     *
     * @param \ContentPrototype $section
     * @return array
     */
    public function getItemModel(\ContentPrototype $section = null)
    {
        $itemModel =  array(
            'expose_section_id' => null,
            'type' => self::CONTENT_PAGE,
            'title' => null,
            'description' => null,
            'content' => null,
            'path' => null,
            'parameters' => array(),
            'language' => $this->language,
        );

        if (null !== $section) {
            $itemModel['expose_section_id'] = $section->id;
            $itemModel['title'] = $section->title;
        }

        return $itemModel;
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
     * Return a section.
     *
     * @param integer $id
     * @return array
     */
    public function findSection($id)
    {
        $sql = $this->sqlSelectSection .
           'WHERE s.id = ?
            ORDER BY s.hierarchy ASC';
        $sectionTranslations = $this->db->fetchAll($sql, array($id));

        return $this->hydrateContentPrototype($sectionTranslations);
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
            ORDER BY s.hierarchy ASC';
        $sectionTranslations = $this->db->fetchAll($sql, array($slug));

        return $this->hydrateContentPrototype($sectionTranslations);
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
            ORDER BY s.hierarchy ASC';
        $sectionTranslations = $this->db->fetchAll($sql);
        $section = $this->hydrateContentPrototype($sectionTranslations);

        // Generate default homepage
        if (!$section) {
            $settings = new Settings($this->db);
            $section = $this->addSection(array(
                'type' => self::CONTENT_PAGE,
                'title' => $settings->name,
                'homepage' => '1',
            ));
            $this->addItem($section, array(
                'type' => self::CONTENT_PAGE,
                'title' => $settings->name,
                'content' => '<div id="homepage"><h1>'.$settings->name.'</h1></div>',
            ));
        }

        return $section;
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
            'homepage' => $section['homepage'],
            'visibility' => $section['visibility'],
        ) + $this->blameAndTimestampData(0));

        $section['id'] = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $section['id'],
            'title' => $section['title'],
            'description' => $section['description'],
            'language' => $this->language,
        ));

        return $this->hydrateContentPrototype(array($section));
    }

    /**
     * Edit a section.
     *
     * @return array Section
     */
    public function updateSection(\ContentPrototype $section)
    {
        // Update section
        $this->db->update('expose_section', array(
            'slug' => $this->uniqueSlug($section->title, $section->id),
            'visibility' => $section->visibility,
            'expose_section_id' => $section->expose_section_id,
        ) + $this->blameAndTimestampData($section->id),
        array('id' => $section->id));

        // Update translated section attributes
        $this->db->update('expose_section_trans', array(
            'title' => $section->title,
            'description' => $section->description,
            'parameters' => serialize($section->parameters),
        ), array('expose_section_id' => $section->id, 'language' => $this->language));
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
    public function addItem(\ContentPrototype $section, array $item = array())
    {
        $item = array_merge($this->getItemModel($section), $item);

        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $section->id,
            'type' => $item['type'],
            'slug' => slugify($item['title']),
            'path' => $item['path'],
        ) + $this->blameAndTimestampData(0));

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
                'choices'       => static::getContentTypesChoice(),
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
                'choices'       => static::getSectionVisibilityChoice(),
                'label'         => 'section.visibility',
            ))
        ;

        return $form;
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
     * Instanciate a related content object from database entity.
     *
     * @param array $sectionTranslations
     * @return \ContentPrototype
     */
    private function hydrateContentPrototype(array $sectionTranslations)
    {
        if (empty($sectionTranslations)) {
            return false;
        }

        $section = $this->retrieveLanguage($sectionTranslations);

        if (!in_array($section['type'], self::getContentTypes())) {
            $section['type'] = self::CONTENT_PAGE;
        }

        $contentClass = 'Content'.strtoupper($section['type']);
        $content = new $contentClass($this->db, $section);
        $content->setLanguage($this->language);

        return $content;
    }

    /**
     * Retrieve section in current language or fallback to default one.
     *
     * @param array $sectionTranslations
     */
    private function retrieveLanguage(array $sectionTranslations)
    {
        foreach ($sectionTranslations as $section) {
            if ($section['language'] == $this->language) {
                return $section;
            }
        }

        return $sectionTranslations[0];
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

        $securityToken = $this->security->getToken();
        if (!empty($securityToken)) {
            $loggedUser = $securityToken->getUser();
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
