<?php

function thumbnail_for_string(string $url, int $maxWidth = 200): string
{
    // if string contains 'thumb' or 'thumbnail', return it as is
    if (strpos($url, 'thumb') !== false || strpos($url, 'thumbnail') !== false) {
        return $url;
    }

    // if string includes b.robnugen start a block
    if (strpos($url, 'b.robnugen') !== false) {
        if (preg_match('/.*?(_1000)?\./', $url)) {
            return preg_replace('/(_1000)?\./', '.', $url);
        }

        // insert thumb before the file extension
        $parts = pathinfo($url);
        return $parts['dirname'] . '/' .
                $parts['filename'] .
                '_thumb.' .
                $parts['extension'];
    }

    return $url;
}
