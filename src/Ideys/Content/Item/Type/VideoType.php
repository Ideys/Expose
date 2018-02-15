<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Section\Entity\Channel;
use Ideys\Content\Item\Entity\Video;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Video Item type.
 */
class VideoType extends ItemType
{
    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Entity\Video $video
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Video $video)
    {
        $formBuilder = $this->formFactory
            ->createBuilder(FormType::class, $video)
            ->add('category', ChoiceType::class, array(
                'label' => 'channel.provider.choice',
                'choices' => Channel::getProviderChoice(),
            ))
            ->add('title', TextType::class, array(
                'label' => 'channel.video.title',
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'channel.video.code',
            ))
        ;

        return $formBuilder;
    }
}
