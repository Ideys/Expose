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
    public function addForm(FormFactory $formFactory, $item)
    {
        $newVideo = new Video(array_merge($item, array(
            'type' => \Ideys\Content\ContentFactory::ITEM_VIDEO,
            'parameters' => serialize(Video::getParameters()),
        )));

        $formBuilder = $formFactory
            ->createBuilder('form', $newVideo)
            ->add('provider', 'choice', array(
                'label' => 'channel.provider.choice',
                'choices' => static::getProviderChoice(),
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

    private function getVimeoCode($string)
    {
        // Filter the full iframe to extract the vimeo video code
        //<iframe src="//player.vimeo.com/video/12345678?title=0&amp;byline=0&...
        $code = (int)preg_filter('!.*video/(\d+)\?.*!', '$1', $string);

        if (0 == $code) {
            // If is not an iframe could be only code or vimeo link
            //http://vimeo.com/12345678
            $code = filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        }

        return $code;
    }
}
