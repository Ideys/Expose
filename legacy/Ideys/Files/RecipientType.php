<?php

namespace Ideys\Files;

use Symfony\Component\Form\FormFactory;

/**
 * File recipient form type.
 */
class RecipientType
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
     * @param Recipient $recipient
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Recipient $recipient)
    {
        $formBuilder = $this->formBuilder($recipient);

        return $formBuilder->getForm();
    }

    /**
     * Return contact message form builder.
     *
     * @param Recipient $recipient
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Recipient $recipient)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $recipient)
            ->add('name', 'text', array(
                'label'         => 'file.recipient',
            ))
        ;

        return $formBuilder;
    }
}
