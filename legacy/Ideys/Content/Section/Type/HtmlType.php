<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;

/**
 * Section HTML type.
 */
class HtmlType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
        ;

        return $formBuilder;
    }
}
