<?php

namespace Ideys\Content\Section;

use Ideys\Content\Item\Video;
use Ideys\Content\ContentInterface;
use Symfony\Component\Form\FormFactory;

/**
 * Channel content manager.
 */
class Channel extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * Add form
     */
    public function addForm(FormFactory $formFactory, Video $video)
    {
        $formBuilder = $formFactory
            ->createBuilder('form', $video)
            ->add('category', 'choice', array(
                'label' => 'channel.provider.choice',
                'choices' => static::getProviderChoice(),
            ))
            ->add('title', 'text', array(
                'label' => 'channel.video.title',
            ))
            ->add('content', 'textarea', array(
                'label' => 'channel.video.code',
            ))
        ;

        return $formBuilder->getForm();
    }

    /**
     * @return array
     */
    public static function getProviderChoice()
    {
        return array(
            Video::PROVIDER_VIMEO => Video::PROVIDER_VIMEO,
            Video::PROVIDER_DAILYMOTION => Video::PROVIDER_DAILYMOTION,
            Video::PROVIDER_YOUTUBE => Video::PROVIDER_YOUTUBE,
        );
    }

    public function guessVideoCode(Video $video)
    {
        switch ($video->category) {
            case Video::PROVIDER_VIMEO :
                // Filter the full iframe to extract the vimeo video code
                //<iframe src="//player.vimeo.com/video/12345678?title=0&amp;byline=0&...
                $code = (int)preg_filter('!.*video/(\d+)\?.*!', '$1', $video->content);

                if (0 == $code) {
                    // If is not an iframe could be only code or vimeo link
                    //http://vimeo.com/12345678
                    $code = filter_var($video->content, FILTER_SANITIZE_NUMBER_INT);
                }
            break;
            case Video::PROVIDER_DAILYMOTION :
                $code = $video->content;
            break;
            case Video::PROVIDER_YOUTUBE :
                $code = $video->content;
            break;
        }

        $video->path = $code;
    }
}
