<?php

namespace Ideys\Content\Section\Entity;

/**
 * Link section manager.
 *
 * A link section is used to display an external link into menu.
 */
class Link extends Section
{
    private $url = 'http://';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_LINK;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Link
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
