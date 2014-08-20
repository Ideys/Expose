<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Page;

/**
 * Html Page Item type.
 */
class PageType extends ItemType
{
    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Entity\Page $page
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Page $page)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $page)
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

        return $formBuilder;
    }
}
