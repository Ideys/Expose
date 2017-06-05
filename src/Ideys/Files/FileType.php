<?php

namespace Ideys\Files;

use Symfony\Component\Form\FormFactory;

/**
 * File form type.
 */
class FileType
{
    /**
     * @var FormFactory
     */
    protected $formFactory;


    /**
     * @param FormFactory   $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the file upload form.
     *
     * @param File $file
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(File $file)
    {
        $formBuilder = $this->formBuilder($file);

        return $formBuilder->getForm();
    }

    /**
     * Return the edit file title form.
     *
     * @param File $file
     *
     * @return \Symfony\Component\Form\Form
     */
    public function editForm(File $file)
    {
        $formBuilder = $this->formBuilder($file);
        $formBuilder->remove('file');

        return $formBuilder->getForm();
    }

    /**
     * Return contact message form builder.
     *
     * @param File $file
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(File $file)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $file)
            ->add('title', 'text', array(
                'label'         => 'file.title',
            ))
            ->add('file', 'file', array(
                'label'         => 'file.file',
            ))
        ;

        return $formBuilder;
    }
}
