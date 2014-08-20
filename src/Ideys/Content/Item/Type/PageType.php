<?php

namespace Ideys\Content\Type;

use Ideys\Content\Item\Entity\Page;
use Symfony\Component\Form\FormFactory;

/**
 * Html Page Item type.
 */
class PageType
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the item form.
     *
     * @param \Ideys\Content\Item\Entity\Page $page
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(Page $page)
    {
        $formBuilder = $this->formBuilder($page);

        return $formBuilder->getForm();
    }

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
