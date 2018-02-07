<?php

namespace App\Content\Item;

/**
 * Gallery Slide class.
 */
class Slide extends Item
{
    public function __construct()
    {
        $this->type = self::ITEM_SLIDE;
    }

    /**
     * Set real extension
     *
     * @param string $realExtension
     *
     * @return Slide
     */
    public function setRealExtension($realExtension)
    {
        $this->addParameter('real_ext', $realExtension);

        return $this;
    }

    /**
     * Get real extension
     *
     * @return string
     */
    public function getRealExtension()
    {
        return $this->retrieveParameter('real_ext');
    }

    /**
     * Set fileSize
     *
     * @param float $fileSize
     *
     * @return Slide
     */
    public function setFileSize($fileSize)
    {
        $this->addParameter('file_size', $fileSize);

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return float
     */
    public function getFileSize()
    {
        return $this->retrieveParameter('file_size');
    }

    /**
     * Set originalName
     *
     * @param float $originalName
     *
     * @return Slide
     */
    public function setOriginalName($originalName)
    {
        $this->addParameter('original_name', $originalName);

        return $this;
    }

    /**
     * Get originalName
     *
     * @return float
     */
    public function getOriginalName()
    {
        return $this->retrieveParameter('original_name');
    }

    /**
     * Set meta data.
     *
     * @param array $metaData
     *
     * @return Slide
     */
    public function setMetaData($metaData)
    {
        $this->addParameter('metaData', (array) $metaData);

        return $this;
    }

    /**
     * Get metaData
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->retrieveParameter('metaData', array());
    }
}
