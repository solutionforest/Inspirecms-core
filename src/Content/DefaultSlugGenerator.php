<?php

namespace SolutionForest\InspireCms\Content;

use Illuminate\Support\Str;

class DefaultSlugGenerator implements SlugGeneratorInterface
{
    public function generate($text) 
    {
        // return Str::slug($text);
        
        $maxLength = 50;
        
        // Method 1: Try ICU transliterator (best option)
        if (function_exists('transliterator_transliterate')) {
            $slug = self::useTransliterator($text);
            if ($slug && strlen($slug) > 0) {
                return self::limitLength($slug, $maxLength);
            }
        }
        
        // Method 2: Try iconv transliteration
        $slug = self::useIconv($text);
        if ($slug && strlen($slug) > 0) {
            return self::limitLength($slug, $maxLength);
        }
        
        // Method 3: Fallback to meaningful slug
        return self::fallbackSlug($text);
    }
    
    private static function useTransliterator($text) {
        try {
            $transliterated = transliterator_transliterate(
                'Han-Latin; Latin-ASCII; Lower()', 
                $text
            );
            return self::cleanSlug($transliterated);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private static function useIconv($text) {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($transliterated === false) {
            return false;
        }
        return self::cleanSlug($transliterated);
    }
    
    private static function cleanSlug($text) {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    private static function fallbackSlug($text) {
        // Create a meaningful fallback
        $hash = substr(md5($text), 0, 8);
        $timestamp = date('Ymd');
        return "article-{$timestamp}-{$hash}";
    }
    
    private static function limitLength($slug, $maxLength) {
        if (strlen($slug) <= $maxLength) {
            return $slug;
        }
        
        // Cut at word boundary
        $slug = substr($slug, 0, $maxLength);
        $lastDash = strrpos($slug, '-');
        
        if ($lastDash !== false && $lastDash > $maxLength * 0.7) {
            $slug = substr($slug, 0, $lastDash);
        }
        
        return rtrim($slug, '-');
    }
}
