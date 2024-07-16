<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Helper;

use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Site;
use Statamic\Structures\CollectionTree;

class StringHelper
{
    private const LOWERCASE_WORDS = ['und', 'bis', 'von', 'mit', 'tun', 'oder', 'kann', 'man', 'er', 'es', 'kann'];

    private const UPPERCASE_WORDS = ['zorn', 'gesicht', 'muskel', 'pigment', 'lippe', 'lid', 'experte', 'akne'];

    private const WORD_MAPPING = ['dr' => 'Dr.'];

    public static function toTitleCase(string $string): string
    {
        $string = strtolower($string);
        $string = str_replace(['-', 'ae', 'oe', 'ue'], [' ', 'ä', 'ö', 'ü'], $string);
        return ucfirst(implode(' ', array_map(function ($word) {
            if ($mapped = self::WORD_MAPPING[$word] ?? null) {
                return $mapped;
            }
            if (in_array($word, self::LOWERCASE_WORDS)) {
                return $word;
            }
            if (preg_match('#.{3,}el?n$#', $word) && !starts_with($word, self::UPPERCASE_WORDS)) {
                return $word;
            }
            return ucfirst($word);
        }, preg_split('#\s+#', $string))));
    }
}
