<?php

namespace Ideys\Content;

use Doctrine\DBAL\Connection;
use Symfony\Component\Form\FormFactory;

/**
 * Section form type.
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
     * Return the add section form.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($section)
    {
        $formBuilder = $this->formBuilder($section);

        return $formBuilder->getForm();
    }

    /**
     * Return section form builder.
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder($entity)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $entity)
            ->add('type', 'choice', array(
                'choices'       => static::getSectionTypesChoice(),
                'label'         => 'content.type',
            ))
            ->add('title', 'text', array(
                'label'         => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('description', 'textarea', array(
                'required'      => false,
                'label'         => 'section.description',
                'attr' => array(
                    'placeholder' => 'section.description',
                ),
            ))
            ->add('custom_css', 'textarea', array(
                'required'      => false,
                'label'         => 'section.custom.css',
                'attr' => array(
                    'placeholder' => 'section.custom.css',
                ),
            ))
            ->add('custom_js', 'textarea', array(
                'required'      => false,
                'label'         => 'section.custom.js',
                'attr' => array(
                    'placeholder' => 'section.custom.js',
                ),
            ))
            ->add('expose_section_id', 'choice', array(
                'choices'       => $this->getDirChoices(),
                'required'      => false,
                'label'         => 'dir.dir',
                'empty_value'   => 'dir.root',
            ))
            ->add('menu_pos', 'choice', array(
                'choices'       => static::getMenuPosChoice(),
                'label'         => 'section.menu.menu',
            ))
            ->add('visibility', 'choice', array(
                'choices'       => static::getSectionVisibilityChoice(),
                'label'         => 'section.visibility.visibility',
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
             FROM expose_section AS s
             LEFT JOIN expose_section_trans AS t
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

    /**
     * Return content types keys and trans values.
     *
     * @return array
     */
    public static function getSectionTypesChoice()
    {
        $keys = ContentFactory::getSectionTypes();
        $values = array_map(function($item){
            return 'section.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * Return menu position choices.
     *
     * @return array
     */
    public static function getMenuPosChoice()
    {
        return array(
            'main' => 'section.menu.main',
            'second' => 'section.menu.second',
        );
    }

    /**
     * Return content visibility choices.
     *
     * @return array
     */
    public static function getSectionVisibilityChoice()
    {
        return array(
            'public' => 'section.visibility.public',
            'private' => 'section.visibility.private',
            'hidden' => 'section.visibility.hidden',
            'closed' => 'section.visibility.closed',
        );
    }
}
