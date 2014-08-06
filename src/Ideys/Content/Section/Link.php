<?php

namespace Ideys\Content\Section;

use Symfony\Component\Form\FormFactory;

/**
 * Link section manager.
 *
 * A link section is used to display an external link into menu.
 */
class Link extends Section implements SectionInterface
{
    private $url = 'http://';

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(FormFactory $formFactory)
    {
        $formBuilder = $this->settingsFormBuilder($formFactory)
            ->add('url', 'url', array(
                'label' => 'link.url',
            ))
        ;

        return $formBuilder->getForm();
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
