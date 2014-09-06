<?php

namespace Ideys\Content;

use Ideys\Content\Section\Provider\SectionProvider;
use Ideys\Content\Section\Entity\SectionInterface;
use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Entity\Html;
use Ideys\Content\Item\Entity\Item;
use Ideys\Content\Item\Entity\Page;
use Ideys\Settings\Settings;
use Silex\Application;

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
     * @var string
     */
    protected $language;

    /**
     * @var array
     */
    protected $sections = array();

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
        $this->translator = $app['translator'];
        $this->security = $app['security'];
        $this->language = $this->translator->getLocale();
    }

    /**
     * Return the first viewable section.
     *
     * @return Section
     */
    public function findFirstSection()
    {
        $sql = SectionProvider::baseQuery()
            . "WHERE s.type NOT IN ('link', 'dir') "
            . "AND t.language = ? "
            . "AND s.visibility NOT IN ('homepage', 'closed') ";
        $entities = $this->db->fetchAll($sql, array($this->language));

        if (empty($entities)) {
            return null;
        }

        $data = array_pop($entities);

        $sectionProvider = new SectionProvider($this->db, $this->security);
        $sectionProvider->setLanguage($this->language);

        return $sectionProvider->hydrateSection($data);
    }

    /**
     * Find the homepage section, create it if not exists.
     *
     * @return Section
     */
    public function findHomepage()
    {
        $sql = SectionProvider::baseQuery()
           . 'WHERE s.visibility = ? '
           . 'AND t.language = ? '
           . 'ORDER BY s.hierarchy ASC ';
        $entities = $this->db->fetchAll($sql, array(Section::VISIBILITY_HOMEPAGE, $this->language));

        // Generate default homepage
        if (empty($entities)) {
            $settings = new Settings($this->db);
            $section = $this->addSection(new Html($this->db, array(
                'type' => Section::SECTION_HTML,
                'title' => $settings->getName(),
                'visibility' => Section::VISIBILITY_HOMEPAGE,
            )));
            $page = new Page(array(
                'type' => Item::ITEM_PAGE,
                'title' => $settings->getName(),
                'content' => '<div id="homepage"><h1>'.$settings->getName().'</h1></div>',
            ));
            $this->addItem($section, $page);
        } else {
            $sectionProvider = new SectionProvider($this->db, $this->security);
            $sectionProvider->setLanguage($this->language);
            $data = array_pop($entities);
            $section = $sectionProvider->hydrateSection($data);
        }

        return $section;
    }

    /**
     * Replace sections keys replacement for composite sections.
     *
     * - Gallery integration
     * - Video integration
     *
     * @param SectionInterface  $section
     * @param \Twig_Environment $twig
     */
    public function composeSectionItems(SectionInterface $section, \Twig_Environment $twig)
    {
        if ($section->isComposite()) {

            $sectionProvider = new SectionProvider($this->db, $this->security);
            $sectionProvider->setLanguage($this->language);

            $items = $section->getDefaultItems();

            // A: extract replacement keys
            $sectionSlugs = array();
            $galleries = array();
            foreach ($items as $item) {
                if ($item instanceof Item) {
                    $content = $item->getContent();
                    $countMatch = preg_match_all('/__(slides|video):([\w\@-]+)__/', $content, $matches);
                    if ((int)$countMatch > 0) {
                        $keys = $matches[0];
                        $contentType = $matches[1];
                        foreach ($matches[2] as $row => $slug) {
                            $sectionSlugs[$contentType[$row]][$keys[$row]] = $slug;
                        }
                        $galleries = $sectionSlugs['slides'];
                    }
                }
            }

            // B: retrieve related sections
            $replacementValues = array();
            if (!empty($galleries)) {
                $sanitizedSlugs = filter_var_array($galleries, FILTER_SANITIZE_STRING);
                $sql = SectionProvider::baseQuery()
                    . 'WHERE s.slug IN (\''. implode("', '", $sanitizedSlugs) .'\') '
                    . 'AND t.language = ? '
                    . "AND s.type IN ('gallery', 'channel') ";

                $sectionsToInclude = $this->db->fetchAll($sql, array($this->language));

                $replacementStrings = array_flip($galleries);
                foreach ($sectionsToInclude as $s) {
                    $sectionToInclude = $sectionProvider->hydrateSection($s);
                    $replacementValues[$replacementStrings[$sectionToInclude->getSlug()]] = $sectionToInclude;
                }
            }

            // C: replace keys by sections content
            foreach ($items as $item) {
                if ($item instanceof Item) {
                    $content = $item->getContent();

                    // Insert extracted contents
                    foreach ($replacementValues as $key => $replacementSection) {
                        $replacementTemplate = $twig->render('frontend/'.$replacementSection->getType().'/_embed.html.twig', array(
                            'section' => $replacementSection,
                        ));
                        $content = str_replace($key, $replacementTemplate, $content);
                    }

                    // Remove no replaced keys
                    foreach ($galleries as $key => $slug) {
                        $content = str_replace($key, '', $content);
                    }

                    $item->setContent($content);
                }
            }
        }
    }

    /**
     * Update all sections common parameters with identical tag.
     *
     * @param Section $section
     */
    public function updateGroupedSections(Section $section)
    {
        $this->db->update('expose_section', array(
            'custom_css' => $section->getCustomCss(),
            'custom_js' => $section->getCustomJs(),
            'shuffle' => $section->getShuffle(),
        ),
        array('tag' => $section->getTag(), 'type' => $section->getType()));

        // Update translated sections parameters
        $sectionsIds = $this->db->fetchAll(
              'SELECT id FROM expose_section '
            . 'WHERE tag = ? AND type = ?',
            array($section->getTag(), $section->getType())
        );

        foreach ($sectionsIds as $id) {
            $this->db->update('expose_section_trans', array(
                'parameters' => serialize($section->getParameters()),
            ), array('expose_section_id' => $id['id'], 'language' => $this->language));
        }
    }
}
