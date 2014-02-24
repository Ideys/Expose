<?php

namespace Ideys\User;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile form type.
 */
class ProfileType
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var boolean
     */
    protected $fullForm;


    /**
     * Constructor.
     *
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     * @param boolean                               $fullForm
     */
    public function __construct(FormFactory $formFactory, $fullForm = false)
    {
        $this->formFactory = $formFactory;
        $this->fullForm = $fullForm;
    }

    /**
     * Return the edit directory form.
     *
     * @param \Ideys\User\Profile   $profile
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Profile $profile)
    {
        $formBuilder = $this->formBuilder($profile);

        return $formBuilder->getForm();
    }

    /**
     * Return dir form builder.
     *
     * @param \Ideys\User\Profile   $profile
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Profile $profile)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $profile)
            ->add('username', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 4)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.name',
            ))
            ->add('gender', 'choice', array(
                'constraints'   => array(
                    new Assert\Choice(array(
                        'choices' => array_flip(Profile::getGenderChoice()),
                    )),
                ),
                'choices'       => Profile::getGenderChoice(),
                'label'         => 'user.gender',
            ))
            ->add('firstname', 'text', array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.firstname',
            ))
            ->add('lastname', 'text', array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.lastname',
            ))
            ->add('email', 'email', array(
                'constraints'   => array(
                    new Assert\Email(),
                ),
                'required'      => false,
                'label'         => 'user.email',
            ))
            ->add('plainPassword', 'password', array(
                'constraints'   => ($profile->getId() > 0) ? array() : array(
                    new Assert\NotBlank()
                ),
                'required'      => false,
                'label'         => 'user.password',
            ))
        ;

        if ($this->fullForm) {
            $formBuilder
                ->add('roles', 'choice', array(
                    'label'         => 'user.role.role',
                    'choices'       => Profile::getRolesChoice(),
                    'multiple'      => true,
                    'constraints'   => array(
                        new Assert\Choice(array(
                            'choices' => Profile::getRolesKeys(),
                            'multiple' => true,
                            'min' => 1,
                        )),
                    ),
                ))
            ;
        }

        return $formBuilder;
    }
}
