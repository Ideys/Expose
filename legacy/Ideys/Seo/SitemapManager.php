<?php

namespace Ideys\Seo;

use Ideys\Content\Section\Entity\Section;
use Ideys\Settings\SettingsManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Sitemap manager.
 */
class SitemapManager
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    public function __construct(
        Connection $connection,
        UrlGenerator $urlGenerator,
        SettingsManager $settingsManager)
    {
        $this->db = $connection;
        $this->urlGenerator = $urlGenerator;
        $this->settingsManager = $settingsManager;
    }

    /**
     * Retrieve settings parameters from database.
     *
     * @return SitemapUrl[]
     */
    public function generateSitemapData()
    {
        $now = (new \DateTime())->format('Y-m-d');
        $languagesEnabled = $this->settingsManager->getSettings()->getLanguages();

        $urls = [];

        foreach ($languagesEnabled as $language) {
            $urls[] = (new SitemapUrl())
                ->setPath($this->urlGenerator->generate('homepage', array('_locale' => $language), true))
                ->setLastModification($now)
                ->setChangeFrequency(SitemapUrl::FREQUENCY_YEARLY)
                ->setPriority(0.8);
            $urls[] = (new SitemapUrl())
                ->setPath($this->urlGenerator->generate('contact', array('_locale' => $language), true))
                ->setLastModification($now)
                ->setChangeFrequency(SitemapUrl::FREQUENCY_YEARLY)
                ->setPriority(0.8);
        }

        $sections = $this->db->fetchAll(
            'SELECT s.id, s.slug, t.language FROM '.TABLE_PREFIX.'section AS s '.
            'LEFT JOIN '.TABLE_PREFIX.'section_trans AS t '.
            'ON t.expose_section_id = s.id '.
            'WHERE s.visibility NOT IN (?, ?) '.
            'AND s.type NOT IN (?, ?) '.
            'AND s.archive = 0 '.
            'ORDER BY s.hierarchy ASC ',
            array(
                Section::VISIBILITY_PRIVATE, Section::VISIBILITY_CLOSED,
                Section::SECTION_LINK, Section::SECTION_DIR
            ));

        foreach ($sections as $section) {
            $urls[] = (new SitemapUrl())
                ->setPath($this->urlGenerator->generate('section', array(
                    '_locale' => $section['language'],
                    'slug' => $section['slug']
                ), true))
                ->setLastModification($now)
                ->setChangeFrequency(SitemapUrl::FREQUENCY_MONTHLY)
                ->setPriority(1);
        }

        return $urls;
    }
}
