<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Ideys\Settings\Settings;
use Doctrine\DBAL\Connection;
use Symfony\Component\Form\FormFactory;

/**
 * Section default type.
 */
class SectionType
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection             $db
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     */
    public function __construct(Connection  $db,
                                FormFactory $formFactory)
    {
        $this->db = $db;
        $this->formFactory = $formFactory;
    }

    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $section)
            ->add('type', 'choice', array(
                'choices'       => Section::getTypeChoices(),
                'label'         => 'content.type',
            ))
            ->add('title', 'text', array(
                'label'         => 'section.title',
                'attr'          => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('description', 'textarea', array(
                'required'      => false,
                'label'         => 'section.description',
                'attr'          => array(
                    'placeholder' => 'section.description',
                ),
            ))
            ->add('customCss', 'textarea', array(
                'required'      => false,
                'label'         => 'section.custom.css',
                'attr'          => array(
                    'placeholder' => 'section.custom.css',
                ),
            ))
            ->add('customJs', 'textarea', array(
                'required'      => false,
                'label'         => 'section.custom.js',
                'attr'          => array(
                    'placeholder' => 'section.custom.js',
                ),
            ))
            ->add('exposeSectionId', 'choice', array(
                'choices'       => $this->getDirChoices(),
                'required'      => false,
                'label'         => 'dir.dir',
                'empty_value'   => 'dir.root',
            ))
            ->add('menuPos', 'choice', array(
                'choices'       => Section::getMenuPosChoices(),
                'label'         => 'section.menu.menu',
            ))
            ->add('targetBlank', 'choice', array(
                'label'         => 'link.target.blank',
                'choices'       => Settings::getIOChoices(),
            ))
            ->add('visibility', 'choice', array(
                'choices'       => Section::getVisibilityChoices(),
                'label'         => 'section.visibility.visibility',
            ))
            ->add('shuffle', 'choice', array(
                'choices'       => Settings::getIOChoices(),
                'label'         => 'gallery.slide.shuffle',
            ))
            ->add('tag', 'text', array(
                'label'         => 'section.tag',
                'required'      => false,
            ))
        ;

        return $formBuilder;
    }

    /**
     * Return directories sections.
     *
     * @return array
     */
    private function getDirChoices()
    {
        $sql =
            'SELECT s.id, t.title
             FROM '.TABLE_PREFIX.'section AS s
             LEFT JOIN '.TABLE_PREFIX.'section_trans AS t
             ON t.expose_section_id = s.id
             WHERE s.type = ?
             ORDER BY s.hierarchy ASC';

        $sections = $this->db->fetchAll($sql, array('dir'));

        $choice = array();
        foreach ($sections as $section) {
            $choice[$section['id']] = $section['title'];
        }

        return $choice;
    }
}
