<?php

namespace Ideys\Seo;

/**
 * App settings entity.
 */
class SitemapUrl
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $lastModification;

    /**
     * @var string
     */
    private $changeFrequency = self::FREQUENCY_MONTHLY;

    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_YEARLY = 'yearly';

    /**
     * @var float
     */
    private $priority = 0.1;

    /**
     * @param string $path
     *
     * @return SitemapUrl
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $lastModification
     *
     * @return SitemapUrl
     */
    public function setLastModification($lastModification)
    {
        $this->lastModification = $lastModification;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastModification()
    {
        return $this->lastModification;
    }

    /**
     * @param string $changeFrequency
     *
     * @return SitemapUrl
     */
    public function setChangeFrequency($changeFrequency)
    {
        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    /**
     * @return string
     */
    public function getChangeFrequency()
    {
        return $this->changeFrequency;
    }

    /**
     * Return sub-domain choices for automatic redirection.
     *
     * @return array
     */
    public static function listFrequencies()
    {
        return array(
            self::FREQUENCY_MONTHLY => self::FREQUENCY_MONTHLY,
            self::FREQUENCY_YEARLY  => self::FREQUENCY_YEARLY,
        );
    }

    /**
     * @param float $priority
     *
     * @return SitemapUrl
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
