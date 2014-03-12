<?php

namespace Ideys\Settings;

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
     * @param array $settings
     *
     * @return \Symfony\Component\Form\Form
     */
    public function form($settings)
    {
        $formBuilder = $this->formBuilder($settings);

        return $formBuilder->getForm();
    }

    /**
     * Return settings form builder.
     *
     * @param array $settings
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder($settings)
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
            ->add('googleFonts', 'text', array(
                'constraints'   => array(
                    new Assert\Length(array('min' => 5)),
                ),
                'required'      => false,
                'label'         => 'google.fonts',
            ))
            ->add('layoutBackground', 'choice', array(
                'choices'   => array(
                    'black' => 'site.background.black',
                    'white' => 'site.background.white',
                ),
                'label'         => 'site.background.background',
            ))
            ->add('customStyle', 'textarea', array(
                'required'      => false,
                'label'         => 'site.style.custom',
            ))
            ->add('customJavascript', 'textarea', array(
                'required'      => false,
                'label'         => 'site.style.customjs',
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
            ->add('newSectiondefaultVisibility', 'choice', array(
                'choices'       => \Ideys\Content\SectionType::getSectionVisibilityChoice(),
                'label'         => 'section.visibility.default',
            ))
        ;

        return $formBuilder;
    }
}
