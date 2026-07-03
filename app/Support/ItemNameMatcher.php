<?php

namespace App\Support;

use Closure;

// Groups free-text Arabic item names that are really the same thing but were
// typed slightly differently each time ("جردل بوية" / "جردل البويه" /
// "جردل بوييه") — used by price tracking, supplier comparison, and search.
// No external dependency: normalization is deterministic string rewriting,
// and fuzzy matching is a plain multi-byte-safe Levenshtein distance.
class ItemNameMatcher
{
    private const SIMILARITY_THRESHOLD = 0.8;

    // Deterministic cleanup: strips diacritics/tatweel, unifies alef/ya/ta-marbuta
    // variants, drops a leading "ال" from each word, collapses whitespace.
    public static function normalize(string $name): string
    {
        $s = trim($name);
        $s = preg_replace('/\s+/u', ' ', $s);

        // Arabic diacritics (tashkeel) and tatweel (ـ)
        $s = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06DC}\x{06DF}-\x{06E8}\x{06EA}-\x{06ED}\x{0640}]/u', '', $s);

        $s = preg_replace('/[إأآا]/u', 'ا', $s);
        $s = str_replace('ى', 'ي', $s);
        $s = str_replace('ة', 'ه', $s);

        $words = explode(' ', $s);
        $words = array_map(function ($w) {
            $stripped = preg_replace('/^ال/u', '', $w);
            return $stripped !== '' ? $stripped : $w;
        }, $words);

        $s = implode(' ', array_filter($words, fn ($w) => $w !== ''));

        return mb_strtolower($s);
    }

    // 0..1 similarity between two names, after normalization
    public static function similarity(string $a, string $b): float
    {
        return static::normalizedSimilarity(static::normalize($a), static::normalize($b));
    }

    private static function normalizedSimilarity(string $normA, string $normB): float
    {
        if ($normA === $normB) {
            return 1.0;
        }

        $maxLen = max(mb_strlen($normA), mb_strlen($normB));
        if ($maxLen === 0) {
            return 1.0;
        }

        return 1 - (static::mbLevenshtein($normA, $normB) / $maxLen);
    }

    // Levenshtein distance over UTF-8 characters (not bytes) — PHP's built-in
    // levenshtein() operates on bytes, which miscounts multi-byte Arabic letters
    private static function mbLevenshtein(string $a, string $b): int
    {
        $a = mb_str_split($a);
        $b = mb_str_split($b);
        $la = count($a);
        $lb = count($b);

        $prev = range(0, $lb);
        for ($i = 1; $i <= $la; $i++) {
            $cur = [$i];
            for ($j = 1; $j <= $lb; $j++) {
                $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;
                $cur[$j] = min($prev[$j] + 1, $cur[$j - 1] + 1, $prev[$j - 1] + $cost);
            }
            $prev = $cur;
        }

        return $prev[$lb];
    }

    // Substring containment after normalizing both sides — used for search
    public static function contains(string $haystack, string $needle): bool
    {
        $needle = static::normalize($needle);
        if ($needle === '') {
            return false;
        }

        return str_contains(static::normalize($haystack), $needle);
    }

    // Clusters $items by fuzzy name similarity. $nameResolver extracts the
    // name to match on from each item. Returns a list of
    // ['canonical' => string, 'variants' => string[], 'items' => array].
    public static function group(iterable $items, Closure $nameResolver): array
    {
        $groups = [];

        foreach ($items as $item) {
            $rawName = trim($nameResolver($item));
            if ($rawName === '') {
                continue;
            }

            $normalized = static::normalize($rawName);
            $matchedIndex = null;

            foreach ($groups as $i => $g) {
                if (static::normalizedSimilarity($g['normalized'], $normalized) >= self::SIMILARITY_THRESHOLD) {
                    $matchedIndex = $i;
                    break;
                }
            }

            if ($matchedIndex === null) {
                $groups[] = [
                    'canonical'  => $rawName,
                    'normalized' => $normalized,
                    'variants'   => [$rawName],
                    'items'      => [$item],
                ];
            } else {
                $groups[$matchedIndex]['items'][] = $item;
                if (! in_array($rawName, $groups[$matchedIndex]['variants'], true)) {
                    $groups[$matchedIndex]['variants'][] = $rawName;
                }
            }
        }

        return $groups;
    }
}
