<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section;
use Ideys\Content\Item\Video;
use Symfony\Component\Form\FormFactory;

/**
 * Blog Video Item type.
 */
class ItemVideoType
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
     * Return the item form.
     *
     * @param \Ideys\Content\Item\Video $post
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(Video $post)
    {
        $formBuilder = $this->formBuilder($post);

        return $formBuilder->getForm();
    }

    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Video $video
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Video $video)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $video)
            ->add('category', 'choice', array(
                'label' => 'channel.provider.choice',
                'choices' => Section\Channel::getProviderChoice(),
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
