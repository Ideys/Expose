<?php

namespace Ideys\Content\Item\Provider;

use Ideys\Content\Item\Entity\Video;

/**
 * Video item provider.
 */
class VideoProvider extends ItemProvider
{
    /**
     * Guess video code from user input.
     *
     * @param Video $video
     */
    public function guessVideoCode(Video $video)
    {
        switch (true) {
            case (strpos($video->getContent(), Video::PROVIDER_VIMEO) > -1) :
                $video->setCategory(Video::PROVIDER_VIMEO);
                //http://vimeo.com/12345678
                if (strpos($video->getContent(), 'http') === 0) {
                    $video->setPath('//player.vimeo.com/video/'.filter_var($video->getContent(), FILTER_SANITIZE_NUMBER_INT));
                    break;
                }
                //<iframe src="//player.vimeo.com/video/12345678?title=0&amp;byline=0&...
                $videoSrc = $this->extractIframeSrc($video->getContent());
                $video->setPath(preg_filter('!([^\?]+)(.*)?!', '$1', $videoSrc));
                break;

            case (strpos($video->getContent(), Video::PROVIDER_DAILYMOTION) > -1) :
                $video->setCategory(Video::PROVIDER_DAILYMOTION);
                //http://www.dailymotion.com/video/abc12ef_lorem-ipsum...
                if (strpos($video->getContent(), 'http') === 0) {
                    $parse = explode('video/', $video->getContent());
                    $code = preg_filter('!([^_]+)(.*)?!', '$1', $parse[1]);
                    $video->setPath('http://www.dailymotion.com/embed/video/'.$code);
                    break;
                }
                //<iframe frameborder="0" src="http://www.dailymotion.com/embed/video/abc12ef
                $video->setPath($this->extractIframeSrc($video->getContent()));
                break;

            case (strpos(str_replace('.', '', $video->getContent()), Video::PROVIDER_YOUTUBE) > -1) :
                $video->setCategory(Video::PROVIDER_YOUTUBE);
                //http://youtu.be/ABC_123...
                if (strpos($video->getContent(), 'http://youtu.be/') === 0) {
                    $video->setPath(str_replace('http://youtu.be/', '//www.youtube.com/embed/', $video->getContent()));
                    break;
                    //http://www.youtube.com/watch?v=ABC_123...
                } elseif (strpos($video->getContent(), 'http') === 0) {
                    $parse = explode('?v=', $video->getContent());
                    $code = preg_filter('!([^&]+)(.*)?!', '$1', $parse[1]);
                    $video->setPath('//www.youtube.com/embed/'.$code);
                    break;
                }
                //<iframe width="960" height="720" src="//www.youtube.com/embed/ABC_123...
                $video->setPath($this->extractIframeSrc($video->getContent()));
                break;
        }
    }

    /**
     * Extract src attribute from an iframe HTML tag.
     *
     * @param string $content
     */
    private function extractIframeSrc($content)
    {
        // Remove useless HTML after iframe block
        $e = explode('</iframe>', $content);
        $content = $e[0] . '</iframe>';

        $dom = new \DOMDocument();
        $dom->loadHTML($content);

        $iframe = $dom->getElementsByTagName('iframe');

        return $iframe->item(0)->getAttribute('src');
    }
}
