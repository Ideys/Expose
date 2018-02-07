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
     * @param \Ideys\User\Profile $profile
     * @param \Ideys\User\Group[] $groups
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Profile $profile, $groups = [])
    {
        $formBuilder = $this->formBuilder($profile, $groups);

        return $formBuilder->getForm();
    }

    /**
     * Return dir form builder.
     *
     * @param \Ideys\User\Profile $profile
     * @param \Ideys\User\Group[] $groups
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Profile $profile, $groups = [])
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
            ->add('firstName', 'text', array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.first.name',
            ))
            ->add('lastName', 'text', array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.last.name',
            ))
            ->add('organization', 'text', array(
                'required'      => false,
                'label'         => 'user.organization',
            ))
            ->add('email', 'email', array(
                'constraints'   => array(
                    new Assert\Email(),
                ),
                'required'      => false,
                'label'         => 'user.email',
            ))
            ->add('phone', 'text', array(
                'required'      => false,
                'label'         => 'user.phone',
            ))
            ->add('mobile', 'text', array(
                'required'      => false,
                'label'         => 'user.mobile',
            ))
            ->add('website', 'url', array(
                'required'      => false,
                'label'         => 'user.website',
            ))
            ->add('address', 'textarea', array(
                'required'      => false,
                'label'         => 'user.address',
                'attr'          => array(
                    'rows' => 5,
                )
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
                ->add('groupsId', 'choice', array(
                    'label'         => 'user.groups',
                    'choices'       => $this->groupsAsChoice($groups),
                    'multiple'      => true,
                    'constraints'   => array(
                        new Assert\Choice(array(
                            'choices' => $this->groupsAsKeys($groups),
                            'multiple' => true,
                        )),
                    ),
                ))
            ;
        }

        return $formBuilder;
    }

    /**
     * @param Group[] $groups
     *
     * @return array
     */
    private function groupsAsChoice($groups)
    {
        $choices = [];

        foreach ($groups as $group) {
            $choices[$group->getId()] = $group->getName();
        }

        return $choices;
    }

    /**
     * @param Group[] $groups
     *
     * @return array
     */
    private function groupsAsKeys($groups)
    {
        $keys = [];

        foreach ($groups as $group) {
            $keys[] = $group->getId();
        }

        return $keys;
    }
}
