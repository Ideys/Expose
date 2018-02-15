<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Page;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->createBuilder(FormType::class, $page)
            ->add('title', TextType::class, array(
                'label' => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'section.description',
                ),
            ))
        ;

        return $formBuilder;
    }
}
