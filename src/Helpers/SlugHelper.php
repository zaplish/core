<?php

namespace Zaplish\Core\Helpers;

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
            // 🇩🇪 German
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ä' => 'ae',
            'Ö' => 'oe',
            'Ü' => 'ue',
            'ß' => 'ss',
            'ẞ' => 'ss',

            // 🇩🇰 / 🇳🇴 Danish / Norwegian
            'æ' => 'ae',
            'Æ' => 'ae',
            'ø' => 'oe',
            'Ø' => 'oe',
            'å' => 'aa',
            'Å' => 'aa',

            // 🇮🇸 Icelandic
            'ð' => 'd',
            'Ð' => 'd',
            'þ' => 'th',
            'Þ' => 'th',

            // 🇹🇷 Turkish
            'ı' => 'i',
            'İ' => 'i',
            'ğ' => 'g',
            'Ğ' => 'g',
            'ş' => 's',
            'Ş' => 's',
            'ö' => 'o',
            'Ö' => 'o',
            'ü' => 'u',
            'Ü' => 'u',
            'ç' => 'c',
            'Ç' => 'c',

            // 🇵🇱 Polish
            'ł' => 'l',
            'Ł' => 'l',

            // 🇭🇺 Hungarian
            'ő' => 'o',
            'Ő' => 'o',
            'ű' => 'u',
            'Ű' => 'u',

            // 🇷🇺 / 🇧🇬 / 🇺🇦 Cyrillic (basic Latin transliteration)
            'ж' => 'zh',
            'Ж' => 'zh',
            'ч' => 'ch',
            'Ч' => 'ch',
            'ш' => 'sh',
            'Ш' => 'sh',
            'щ' => 'shch',
            'Щ' => 'shch',
            'ю' => 'yu',
            'Ю' => 'yu',
            'я' => 'ya',
            'Я' => 'ya',
            'х' => 'kh',
            'Х' => 'kh',
            'ц' => 'ts',
            'Ц' => 'ts',
            'й' => 'i',
            'Й' => 'i',
            'ё' => 'e',
            'Ё' => 'e',
            'ы' => 'y',
            'Ы' => 'y',

            // 🇬🇷 Greek (basic Latin transliteration)
            'θ' => 'th',
            'Θ' => 'th',
            'ψ' => 'ps',
            'Ψ' => 'ps',
            'χ' => 'ch',
            'Χ' => 'ch',
            'ξ' => 'x',
            'Ξ' => 'x',
            'η' => 'i',
            'Η' => 'i',
            'υ' => 'y',
            'Υ' => 'y',
            'ω' => 'o',
            'Ω' => 'o',
            'β' => 'v',
            'Β' => 'v',
            'ρ' => 'r',
            'Ρ' => 'r',
            'σ' => 's',
            'ς' => 's',
            'Σ' => 's',
        ];
        $value = strtr($value, $replacements);

        // Fallback: transliterate other non-ASCII chars (é → e, ñ → n, etc.)
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
