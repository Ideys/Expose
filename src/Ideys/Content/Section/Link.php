<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Settings\Settings;
use Symfony\Component\Form\FormFactory;

/**
 * Link section manager.
 *
 * A link section is used to display an external link into menu.
 */
class Link extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'url' => 'http://',
        );
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
}
