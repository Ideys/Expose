<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Symfony\Component\Form\FormFactory;
use Ideys\Content\Item\Post;

/**
 * Blog section manager.
 */
class Blog extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * New post form.
     */
    public function newPostForm(FormFactory $formFactory, Post $post)
    {
        $formBuilder = $formFactory
            ->createBuilder('form', $post)
            ->add('title', 'text', array(
                'label' => 'blog.post.title',
                'attr' => array(
                    'placeholder' => 'blog.post.title',
                ),
            ))
            ->add('posting_date', 'date', array(
                'label' => 'blog.post.date',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array(
                    'placeholder' => 'blog.post.date',
                    'data-date-format' => 'dd/mm/yyyy',
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
                'choices' => \Ideys\Settings\Settings::getIOChoices(),
            ))
        ;

        return $formBuilder->getForm();
    }
}
