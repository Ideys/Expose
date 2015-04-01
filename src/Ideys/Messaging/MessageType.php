<?php

namespace Ideys\Messaging;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message form type.
 */
class MessageType
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
     * Return the contact message form.
     *
     * @param \Ideys\Messaging\Message $message
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Message $message)
    {
        $formBuilder = $this->formBuilder($message);

        return $formBuilder->getForm();
    }

    /**
     * Return contact message form builder.
     *
     * @param \Ideys\Messaging\Message $message
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Message $message)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $message)
            ->add('name', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 3)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'contact.name',
            ))
            ->add('email', 'email', array(
                'constraints'   => array(
                    new Assert\Email(),
                    new Assert\NotBlank(),
                ),
                'label'         => 'contact.email',
            ))
            ->add('subject', 'text', array(
                'label'         => 'contact.subject',
                'required'      => false,
            ))
            ->add('message', 'textarea', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 10)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'contact.message',
            ))
            ->add('spicedHamQuestion', 'hidden')
            ->add('spicedHamAnswer', 'text', array(
                'label'         => 'contact.anti.spam',
            ))
        ;

        return $formBuilder;
    }
}
