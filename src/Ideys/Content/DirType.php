<?php

namespace Ideys\Content;

use Symfony\Component\Form\FormFactory;

/**
 * Dir form type.
 */
class DirType
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
     * Return the edit directory form.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function editForm($section)
    {
        $formBuilder = $this->formBuilder($section);

        return $formBuilder->getForm();
    }

    /**
     * Return dir form builder.
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder($entity)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $entity)
            ->add('title', 'text', array(
                'label'         => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
        ;

        return $formBuilder;
    }
}
