<?php
namespace Domain;

trait HasPhotos
{
    public function thumbnailFor(string $url, int $maxWidth = 200): string
    {
        if (strpos($url, 'thumb') !== false || strpos($url, 'thumbnail') !== false) {
            return $url;
        }

        if (strpos($url, 'b.robnugen') !== false) {
            if (preg_match('/.*?(_1000)?\./', $url)) {
                return preg_replace('/(_1000)?\./', '.', $url);
            }

            $parts = pathinfo($url);
            return $parts['dirname'] . '/' .
                    $parts['filename'] .
                    '_thumb.' .
                    $parts['extension'];
        }

        // if string includes cloudfront.net start a block
        if (strpos(haystack: $url, needle: 'cloudfront.net') !== false) {
            // Split after cloudfront.net/ and insert W$maxWidth after cloudfront.net/
            $parts = explode(separator: 'cloudfront.net/', string: $url);
            if (count(value: $parts) > 1) {
                return "https://d2f8m59m4mubfx.cloudfront.net/W{$maxWidth}/{$parts[1]}";
            }
        }

        return $url; // fallback
    }
}