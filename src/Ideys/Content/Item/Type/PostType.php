<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Post;
use Ideys\Settings\Settings;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->createBuilder(FormType::class, $post)
            ->add('title', TextType::class, array(
                'label' => 'blog.post.title',
                'attr' => array(
                    'placeholder' => 'blog.post.title',
                ),
            ))
            ->add('postingDate', DateType::class, array(
                'label' => 'blog.post.date',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array(
                    'placeholder' => 'blog.post.date',
                    'data-date-format' => 'dd/mm/yyyy',
                ),
            ))
            ->add('author', TextType::class, array(
                'label' => 'blog.post.author',
                'attr' => array(
                    'placeholder' => 'blog.post.author',
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'blog.post.post',
                ),
            ))
            ->add('published', ChoiceType::class, array(
                'label' => 'blog.post.publish',
                'choices' => Settings::getIOChoices(),
            ))
        ;

        return $formBuilder;
    }
}
