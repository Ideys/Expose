<?php

namespace Ideys\Content\Section\Type;

use Doctrine\DBAL\Connection;
use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\FormFactory;

/**
 * Section type factory.
 * Generate a new form related to Section type.
 */
class SectionTypeFactory
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    public function __construct(
        Connection  $db,
        FormFactory $formFactory
    )
    {
        $this->db = $db;
        $this->formFactory = $formFactory;
    }

    /**
     * Return the add section form.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\Form
     *
     * @throws \Exception If section type is not found
     */
    public function createForm(Section $section)
    {
        $sectionType = $section->getType();
        $typeClassName = '\Ideys\Content\Section\Type\\'.ucfirst($sectionType).'Type';
        $type = new $typeClassName($this->db, $this->formFactory);

        if ( ! $type instanceof SectionType) {
            throw new \Exception(sprintf('Unable to find a form type for Section "%s"', $sectionType));
        }

        $formBuilder = $type->formBuilder($section);

        return $formBuilder->getForm();
    }
}
