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
            ->add('content', 'textarea', array(
                'label' => 'blog.post.post',
                'attr' => array(
                    'placeholder' => 'blog.post.post',
                ),
                
            ))
        ;

        return $formBuilder->getForm();
    }
}
