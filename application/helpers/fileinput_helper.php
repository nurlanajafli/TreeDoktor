<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function fileTypeFromMime($mime, $name)
{
    switch (true) {
        case (preg_match('/image.*/i', $mime)
                && preg_match('/(tiff?|wmf)$/i', $mime))
            || preg_match('/\.(gif|png|jpe?g)$/i', $name):
            return 'image';
        case $mime === 'text/html' || preg_match('/\.(htm|html)$/i', $name):
            return 'html';
        case $mime === 'application/pdf' || preg_match('/\.(pdf)$/i', $name):
            return 'pdf';
        case preg_match('/(word|excel|powerpoint|office)$/i',
                $mime) || preg_match('/\.(docx?|xlsx?|pptx?|pps|potx?)$/i', $name):
            return 'office';

        case preg_match('/(word|excel|powerpoint|office|iwork-pages|tiff?)$/i', $mime)
            || preg_match('/\.(rtf|docx?|xlsx?|pptx?|pps|potx?|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i', $name):
            return 'gdocs';
        case preg_match('/text.*/i', $mime) || preg_match('/\.(txt|md|csv|nfo|php|ini)$/i', $name):
            return 'text';
        case preg_match('/\.video\/(ogg|mp4|webm)$/i', $mime) || preg_match('/\.(og?|mp4|webm)$/i', $name):
            return 'video';
        case preg_match('/\.audio\/(ogg|mp3|wav)$/i', $mime) || preg_match('/\.(ogg|mp3|wav)$/i', $name):
            return 'audio';
        case $mime === 'application/x-shockwave-flash' || preg_match('/\.(swf)$/i', $name):
            return 'flash';
        default:
            return 'other';
    }
}