<?php

namespace App\Content\Section;

/**
 * Link section manager.
 *
 * A link section is used to display an external link into menu.
 */
class Link extends Section
{
    public function __construct()
    {
        $this->type = Section::SECTION_LINK;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->retrieveParameter('url', 'http://');
    }

    /**
     * @param string $url
     *
     * @return Link
     */
    public function setUrl($url)
    {
        $this->addParameter('url', $url);

        return $this;
    }
}
