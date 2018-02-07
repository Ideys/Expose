<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Post;
use Ideys\Settings\Settings;

/**
 * Blog Post Item type.
 */
class PostType extends ItemType
{
    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Entity\Post $post
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Post $post)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $post)
            ->add('title', 'text', array(
                'label' => 'blog.post.title',
                'attr' => array(
                    'placeholder' => 'blog.post.title',
                ),
            ))
            ->add('postingDate', 'date', array(
                'label' => 'blog.post.date',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array(
                    'placeholder' => 'blog.post.date',
                    'data-date-format' => 'dd/mm/yyyy',
                ),
            ))
            ->add('author', 'text', array(
                'label' => 'blog.post.author',
                'attr' => array(
                    'placeholder' => 'blog.post.author',
                ),
            ))
            ->add('content', 'textarea', array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'blog.post.post',
                ),
            ))
            ->add('published', 'choice', array(
                'label' => 'blog.post.publish',
                'choices' => Settings::getIOChoices(),
            ))
        ;

        return $formBuilder;
    }
}
