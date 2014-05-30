<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Content\Item\Post;
use Ideys\Content\SectionInterface;
use Ideys\Settings\Settings;
use Symfony\Component\Form\FormFactory;

/**
 * Blog section manager.
 */
class Blog extends Section implements ContentInterface, SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Post';
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return true;
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

        return $formBuilder->getForm();
    }
}
