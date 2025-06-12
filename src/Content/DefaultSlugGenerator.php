<?php

namespace SolutionForest\InspireCms\Content;

class DefaultSlugGenerator implements SlugGeneratorInterface
{
    const LANG_CHINESE = 'chinese';

    const LANG_RUSSIAN = 'russian';

    const LANG_ARABIC = 'arabic';

    const LANG_JAPANESE = 'japanese';

    const LANG_ENGLISH = 'english';

    const LANG_AUTO = 'auto';

    private static $transliterationRules = [
        self::LANG_CHINESE  => 'Han-Latin; Latin-ASCII; Lower()',
        self::LANG_RUSSIAN  => 'Russian-Latin/BGN; Latin-ASCII; Lower()',
        self::LANG_ARABIC   => 'Arabic-Latin; Latin-ASCII; Lower()',
        // include Han-Latin to transliterate Kanji
        self::LANG_JAPANESE => 'Han-Latin; Hiragana-Latin; Katakana-Latin; Latin-ASCII; Lower()',
    ];

    public function generate($text, $language = self::LANG_AUTO, $separator = '-')
    {
        // Auto-detect language if not specified
        if ($language === self::LANG_AUTO) {
            $language = self::detectLanguage($text);
        }

        // Try transliteration first
        if (function_exists('transliterator_transliterate') && isset(self::$transliterationRules[$language])) {
            $transliterated = transliterator_transliterate(
                self::$transliterationRules[$language],
                $text
            );

            if ($transliterated) {
                $slug = preg_replace('/[^a-z0-9]+/', $separator, $transliterated);
                $slug = trim($slug, $separator);

                if (! empty($slug)) {
                    return $slug;
                }
            }
        }

        // Fallback methods for each language
        return self::fallbackTransliteration($text, $language, $separator);
    }

    private static function detectLanguage($text)
    {
        // Japanese Hiragana/Katakana
        if (preg_match('/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $text)) {
            return self::LANG_JAPANESE;
        }

        // Chinese characters (CJK)
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            return self::LANG_CHINESE;
        }

        // Cyrillic characters (Russian)
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            return self::LANG_RUSSIAN;
        }

        // Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return self::LANG_ARABIC;
        }

        return self::LANG_ENGLISH; // Default fallback to English
    }

    private static function fallbackTransliteration($text, $language, $separator)
    {
        switch ($language) {
            case self::LANG_RUSSIAN:
                return self::fallbackRussian($text, $separator);
            case self::LANG_CHINESE:
                return self::fallbackChinese($text, $separator);
            case self::LANG_JAPANESE:
                return self::fallbackJapanese($text, $separator);
            case self::LANG_ENGLISH:
                return self::fallbackGeneric($text, $separator);
            default:
                return self::fallbackGeneric($text, $separator);
        }
    }

    private static function fallbackRussian($text, $separator)
    {
        // Manual Russian transliteration map
        $russianMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            // Uppercase
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ];

        $result = strtr($text, $russianMap);
        $result = preg_replace('/[^a-zA-Z0-9\s]/', '', $result);
        $result = preg_replace('/\s+/', $separator, $result);

        return strtolower(trim($result, $separator));
    }

    private static function fallbackChinese($text, $separator)
    {
        // Use timestamp-based fallback for Chinese
        $hash = substr(md5($text), 0, 8);
        $timestamp = date('Ymd');

        return "content{$separator}{$timestamp}{$separator}{$hash}";
    }

    private static function fallbackJapanese($text, $separator)
    {
        // Generic ASCII-strip fallback for Japanese text
        return self::fallbackGeneric($text, $separator);
    }

    private static function fallbackGeneric($text, $separator)
    {
        $result = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $result = preg_replace('/\s+/', $separator, $result);

        return strtolower(trim($result, $separator));
    }
}
