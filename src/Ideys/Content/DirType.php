<?php

namespace Ideys\Content;

use Symfony\Component\Form\FormFactory;
use Ideys\Content\Section\Section;

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
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\Form
     */
    public function editForm(Section $section)
    {
        $formBuilder = $this->formBuilder($section);

        return $formBuilder->getForm();
    }

    /**
     * Return dir form builder.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $section)
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
