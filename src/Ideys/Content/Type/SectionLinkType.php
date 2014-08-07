<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section;

/**
 * Section Link type.
 */
class SectionLinkType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section\Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
            ->add('url', 'url', array(
                'label' => 'link.url',
            ))
        ;

        return $formBuilder;
    }
}
