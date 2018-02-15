<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * Section Link type.
 */
class LinkType extends SectionType
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
            ->add('url', UrlType::class, array(
                'label' => 'link.url',
            ))
        ;

        return $formBuilder;
    }
}
