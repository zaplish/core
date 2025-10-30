<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Remove multiple, leading or trailing spaces
     */
    public static function removeSpaces($value = '', $all = false)
    {
        return preg_replace('/\s+/', ($all ? '' : ' '), trim($value));
    }

    /**
     * Sanitise input
     */
    // TODO ask ChatGPT to be more clear
    public static function sanitise($html)
    {
        if (empty($html)) {
            return $html;
        }
        $html = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $html);
        return $html;
    }
}
