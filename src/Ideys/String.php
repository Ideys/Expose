<?php

namespace Ideys;

/**
 * String tools.
 */
class String
{
    /**
     * Slugify strings.
     *
     * @param string $string
     * @param string $sep
     *
     * @return string
     */
    public static function slugify($string, $sep = '-') {
        return
            preg_replace('#[^-\w]+#', '',
            // to lowercase
            strtolower(
                // remove accents
                iconv('utf-8', 'us-ascii//TRANSLIT',
                str_replace(
                    array('à','â','ä','ç','é','è','ê','ë','î','ï','ô','ö','û','ü'),
                    array('a','a','a','c','e','e','e','e','i','i','o','o','u','u'),
                    // trim and replace spaces by an hyphen
                    trim(
                        // replace non letter or digits by an hyphen
                        preg_replace('#[^\\pL\d]+#u', $sep,
                            $string
                        ),
                        $sep
                    )
                )
                )
            )
        );
    }

    /**
     * Generates a token.
     *
     * @return string
     */
    public static function generateToken()
    {
        $bytes = false;
        $strong = false;

        if (function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win')) {
            $bytes = openssl_random_pseudo_bytes(32, $strong);

            if (true !== $strong) {
                $bytes = false;
            }
        }

        // let's just hope we got a good seed
        if (false === $bytes) {
            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
