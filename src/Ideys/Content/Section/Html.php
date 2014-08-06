<?php

namespace Ideys\Content\Section;

use Symfony\Component\Form\FormFactory;
use Ideys\Content\Item\Page;

/**
 * HTML content manager.
 */
class Html extends Section implements SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Page';
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isComposite()
    {
        return true;
    }

    /**
     * Return page content first page.
     */
    public function getFirstPage()
    {
        $items = $this->getItems('Page');

        return array_pop($items);
    }

    /**
     * New post form.
     */
    public function addPageForm(FormFactory $formFactory, Page $page)
    {
        $formBuilder = $formFactory->createBuilder('form', $page)
            ->add('title', 'text', array(
                'label' => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('content', 'textarea', array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'section.description',
                ),
            ))
        ;

        return $formBuilder->getForm();
    }
}
