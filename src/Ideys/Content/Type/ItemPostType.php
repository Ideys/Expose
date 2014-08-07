<?php

namespace Ideys\Content\Type;

use Ideys\Content\Item\Post;
use Ideys\Settings\Settings;
use Symfony\Component\Form\FormFactory;

/**
 * Blog Post Item type.
 */
class ItemPostType
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
     * @param \Ideys\Content\Item\Post $post
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(Post $post)
    {
        $formBuilder = $this->formBuilder($post);

        return $formBuilder->getForm();
    }

    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Post $post
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

        return $formBuilder;
    }
}
