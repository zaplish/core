<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SlugHelper
{
    static function getSlug(string $value): string
    {
        // Trim
        $value = trim($value);

        // Decode HTML entities
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Lowercase
        $value = mb_strtolower($value, 'UTF-8');

        // Replace German umlauts / ß
        $replacements = [
            'ö' => 'oe',
            'ä' => 'ae',
            'ü' => 'ue',
            'ß' => 'ss',
            'ẞ' => 'ss',
        ];
        $value = strtr($value, $replacements);
        
        // Fallback: transliterate other non-ASCII chars (é → e, ł → l, ñ → n, etc.)
        $value = Str::ascii($value);

        // Replace spaces and underscores with hyphens
        $value = preg_replace('/[\s_]+/', '-', $value);

        // Remove everything except a-z, 0-9, hyphen
        $value = preg_replace('/[^a-z0-9\-]/', '', $value);

        // Replace multiple hyphens with one
        $value = preg_replace('/-+/', '-', $value);

        // Trim leading/trailing hyphens
        $value = trim($value, '-');

        return $value;
    }
}