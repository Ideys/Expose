<?php

namespace Ideys\User;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group form type.
 */
class GroupType
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the edit directory form.
     *
     * @param \Ideys\User\Group $group
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Group $group)
    {
        $formBuilder = $this->formBuilder($group);

        return $formBuilder->getForm();
    }

    /**
     * Return dir form builder.
     *
     * @param \Ideys\User\Group $group
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Group $group)
    {
        $formBuilder = $this->formFactory
            ->createBuilder(FormType::class, $group)
            ->add('name', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 4)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'user.group',
            ))
        ;

        return $formBuilder;
    }
}
