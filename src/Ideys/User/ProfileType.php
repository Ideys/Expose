<?php

namespace Ideys\User;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
            ->createBuilder(FormType::class, $profile)
            ->add('username', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 4)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.name',
            ))
            ->add('gender', ChoiceType::class, array(
                'constraints'   => array(
                    new Assert\Choice(array(
                        'choices' => Profile::getGenderChoice(),
                    )),
                ),
                'choices'       => Profile::getGenderChoice(),
                'label'         => 'user.gender',
            ))
            ->add('firstName', TextType::class, array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.first.name',
            ))
            ->add('lastName', TextType::class, array(
                'constraints'   => array(
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.last.name',
            ))
            ->add('organization', TextType::class, array(
                'required'      => false,
                'label'         => 'user.organization',
            ))
            ->add('email', EmailType::class, array(
                'constraints'   => array(
                    new Assert\Email(),
                ),
                'required'      => false,
                'label'         => 'user.email',
            ))
            ->add('phone', TextType::class, array(
                'required'      => false,
                'label'         => 'user.phone',
            ))
            ->add('mobile', TextType::class, array(
                'required'      => false,
                'label'         => 'user.mobile',
            ))
            ->add('website', UrlType::class, array(
                'required'      => false,
                'label'         => 'user.website',
            ))
            ->add('address', TextareaType::class, array(
                'required'      => false,
                'label'         => 'user.address',
                'attr'          => array(
                    'rows' => 5,
                )
            ))
            ->add('plainPassword', PasswordType::class, array(
                'constraints'   => ($profile->getId() > 0) ? array() : array(
                    new Assert\NotBlank()
                ),
                'required'      => false,
                'label'         => 'user.password',
            ))
        ;

        if ($this->fullForm) {
            $formBuilder
                ->add('roles', ChoiceType::class, array(
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
                ->add('groupsId', ChoiceType::class, array(
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
