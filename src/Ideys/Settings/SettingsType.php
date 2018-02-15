<?php

namespace Ideys\Settings;

use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Website settings form type.
 */
class SettingsType
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the settings form.
     *
     * @param Settings $settings
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form(Settings $settings)
    {
        $formBuilder = $this->formBuilder($settings);

        return $formBuilder->getForm();
    }

    /**
     * Return settings form builder.
     *
     * @param Settings $settings
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Settings $settings)
    {
        $formBuilder = $this->formFactory
            ->createBuilder(FormType::class, $settings)
            ->add('name', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 3)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.name',
            ))
            ->add('description', TextareaType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.description',
            ))
            ->add('authorName', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 3)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.author',
            ))
            ->add('subDomain', ChoiceType::class, array(
                'choices'       => Settings::getSubDomainChoices(),
                'label'         => 'site.sub.domain',
            ))
            ->add('languages', ChoiceType::class, array(
                'multiple'      => true,
                'choices'       => Settings::getLanguagesChoices(),
                'label'         => 'language.languages',
            ))
            ->add('maintenance', ChoiceType::class, array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'site.maintenance.mode',
            ))
            ->add('analyticsKey', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.analytics.key',
            ))
            ->add('verificationKey', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.verification.key',
            ))
            ->add('mapsKey', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.maps.key',
            ))
            ->add('googleFonts', TextType::class, array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 5)),
                ),
                'required'      => false,
                'label'         => 'google.fonts',
            ))
            ->add('layoutBackground', ChoiceType::class, array(
                'choices'       => Settings::getLayoutBackgroundChoices(),
                'label'         => 'site.background.background',
            ))
            ->add('customStyle', TextareaType::class, array(
                'required'      => false,
                'label'         => 'site.style.custom',
            ))
            ->add('customJavascript', TextareaType::class, array(
                'required'      => false,
                'label'         => 'site.js.custom',
            ))
            ->add('adminLink', ChoiceType::class, array(
                'choices'       => Settings::getAdminLinkChoices(),
                'label'         => 'admin.link',
            ))
            ->add('contactContent', TextareaType::class, array(
                'required'      => false,
                'label'         => 'contact.content',
            ))
            ->add('contactSection', ChoiceType::class, array(
                'choices'       => Settings::getContactSectionChoices(),
                'label'         => 'contact.section',
            ))
            ->add('contactSendToEmail', EmailType::class, array(
                'label'         => 'contact.send.to.email',
                'required'      => false,
            ))
            ->add('menuPosition', ChoiceType::class, array(
                'choices'       => Settings::getMenuPositionChoices(),
                'label'         => 'site.menu.position',
            ))
            ->add('hideMenuOnHomepage', ChoiceType::class, array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'site.menu.hide.on.homepage',
            ))
            ->add('shareFiles', ChoiceType::class, array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'file.enabled',
            ))
            ->add('newSectionDefaultVisibility', ChoiceType::class, array(
                'choices'       => Section::getVisibilityChoices(),
                'label'         => 'section.visibility.default',
            ))
        ;

        return $formBuilder;
    }
}
