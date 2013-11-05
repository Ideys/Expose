<?php

/**
 * Guess client language, relies on browser data.
 *
 * @param array $app
 * @return string
 */
function client_language_guesser($app) {
    $acceptLanguage = $app['request']->headers->get('accept-language');
    $userLanguage   = strtolower(substr($acceptLanguage, 0, 2));
    $language       = (in_array($userLanguage, $app['languages']))
                      ? $userLanguage : $app['locale_fallback'];
    return $language;
}

/**
 * Slugify strings.
 *
 * @param string $string
 * @return string
 */
function slugify($string) {
    return
        preg_replace('#[^-\w]+#', '',
        // to lowercase
        strtolower(
            // remove accents
            iconv('utf-8', 'us-ascii//TRANSLIT',
                // trim and replace spaces by an hyphen
                trim(
                    // replace non letter or digits by an hyphen
                    preg_replace('#[^\\pL\d]+#u', '-',
                        $string
                    ),
                    '-'
                )
            )
        )
    );
}
