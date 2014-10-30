<?php

namespace Ideys\Settings;

use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Website settings form type.
 */
class SettingsType
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
            ->createBuilder('form', $settings)
            ->add('name', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 3)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.name',
            ))
            ->add('description', 'textarea', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.description',
            ))
            ->add('authorName', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 3)),
                    new Assert\NotBlank(),
                ),
                'label'         => 'site.author',
            ))
            ->add('subDomain', 'choice', array(
                'choices'       => Settings::getSubDomainChoices(),
                'label'         => 'site.sub.domain',
            ))
            ->add('languages', 'choice', array(
                'multiple'      => true,
                'choices'       => Settings::getLanguagesChoices(),
                'label'         => 'language.languages',
            ))
            ->add('maintenance', 'choice', array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'site.maintenance.mode',
            ))
            ->add('analyticsKey', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.analytics.key',
            ))
            ->add('verificationKey', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.verification.key',
            ))
            ->add('mapsKey', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 6)),
                ),
                'required'      => false,
                'label'         => 'google.maps.key',
            ))
            ->add('googleFonts', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 5)),
                ),
                'required'      => false,
                'label'         => 'google.fonts',
            ))
            ->add('layoutBackground', 'choice', array(
                'choices'       => Settings::getLayoutBackgroundChoices(),
                'label'         => 'site.background.background',
            ))
            ->add('customStyle', 'textarea', array(
                'required'      => false,
                'label'         => 'site.style.custom',
            ))
            ->add('customJavascript', 'textarea', array(
                'required'      => false,
                'label'         => 'site.js.custom',
            ))
            ->add('adminLink', 'choice', array(
                'choices'       => Settings::getAdminLinkChoices(),
                'label'         => 'admin.link',
            ))
            ->add('contactContent', 'textarea', array(
                'required'      => false,
                'label'         => 'contact.content',
            ))
            ->add('contactSection', 'choice', array(
                'choices'       => Settings::getContactSectionChoices(),
                'label'         => 'contact.section',
            ))
            ->add('contactSendToEmail', 'email', array(
                'label'         => 'contact.send.to.email',
                'required'      => false,
            ))
            ->add('menuPosition', 'choice', array(
                'choices'       => Settings::getMenuPositionChoices(),
                'label'         => 'site.menu.position',
            ))
            ->add('hideMenuOnHomepage', 'choice', array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'site.menu.hide.on.homepage',
            ))
            ->add('shareFiles', 'choice', array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'file.enabled',
            ))
            ->add('newSectionDefaultVisibility', 'choice', array(
                'choices'       => Section::getVisibilityChoices(),
                'label'         => 'section.visibility.default',
            ))
        ;

        return $formBuilder;
    }
}
