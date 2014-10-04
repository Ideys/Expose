<?php

namespace Ideys;

/**
 * Picture tools.
 */
class Picture
{
    /**
     * Extract meta data from picture.
     *
     * @param string $picturePath
     *
     * @return array
     */
    public static function getMetaData($picturePath) {
        $metaData = array();

        if (function_exists('exif_read_data')) {
            $exifData = @exif_read_data($picturePath);

            // Picture could not be analyzed
            if (! $exifData) {
                return $metaData;
            }

            if (array_key_exists('DateTimeOriginal', $exifData)) {
                $metaData['captureDate'] = $exifData['DateTimeOriginal'];
            }

            if (array_key_exists('Copyright', $exifData)) {
                $metaData['copyright'] = $exifData['Copyright'];
            }

            if (array_key_exists('Author', $exifData)) {
                $metaData['author'] = $exifData['Author'];
            }

            if (array_key_exists('COMPUTED', $exifData)) {
                if (array_key_exists('IsColor', $exifData['COMPUTED'])) {
                    $metaData['isColor'] = ($exifData['COMPUTED']['IsColor'] == 1);
                }
                if (array_key_exists('Copyright', $exifData['COMPUTED'])) {
                    $metaData['copyright'] = $exifData['COMPUTED']['Copyright'];
                }
            }
        }

        return $metaData;
    }
}
