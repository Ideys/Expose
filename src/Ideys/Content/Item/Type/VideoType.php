<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Section\Entity\Channel;
use Ideys\Content\Item\Entity\Video;

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
            ->createBuilder('form', $video)
            ->add('category', 'choice', array(
                'label' => 'channel.provider.choice',
                'choices' => Channel::getProviderChoice(),
            ))
            ->add('title', 'text', array(
                'label' => 'channel.video.title',
            ))
            ->add('content', 'textarea', array(
                'label' => 'channel.video.code',
            ))
        ;

        return $formBuilder;
    }
}
