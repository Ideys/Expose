<?php

namespace Ideys\Files;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * File recipient form type.
 */
class RecipientType
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
     * Return the file upload form.
     *
     * @param \Ideys\Files\Recipient $recipient
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
     * @param \Ideys\Files\Recipient $recipient
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
