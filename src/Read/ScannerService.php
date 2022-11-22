<?php

namespace UseLocale\LocaleLaravel\Read;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ScannerService
{
    private array $phpKeys = [];
    private array $jsonKeys = [];

    private function getTranslationFunctions(): array
    {
        return [
            'trans',
            'trans_choice',
            'Lang::get',
            'Lang::choice',
            'Lang::trans',
            'Lang::transChoice',
            '@lang',
            '@choice',
            '__',
            '$trans.get',
        ];
    }

    public function scan(): void
    {
        // https://github.com/barryvdh/laravel-translation-manager/blob/b21c18afdb1315ab616005b6d33104802a405ebc/src/Manager.php#L161
        $path = base_path();
        $functions = $this->getTranslationFunctions();

        $phpKeys = [];
        $jsonKeys = [];

        $phpKeysPattern =                         // See https://regex101.com/r/WEJqdL/6
            "[^\w|>]" .                          // Must not have an alphanum or _ or > before real method
            '(' . implode('|', $functions) . ')' .  // Must start with one of the functions
            "\(" .                               // Match opening parenthesis
            "[\'\"]" .                           // Match " or '
            '(' .                                // Start a new group to match:
            '[\/a-zA-Z0-9_-]+' .                 // Must start with group
            "([.](?! )[^\1)]+)+" .               // Be followed by one or more items/keys
            ')' .                                // Close group
            "[\'\"]" .                           // Closing quote
            "[\),]";                             // Close parentheses or new parameter

        $jsonKeysPattern =
            "[^\w]" .                                     // Must not have an alphanum before real method
            '(' . implode('|', $functions) . ')' .             // Must start with one of the functions
            "\(\s*" .                                       // Match opening parenthesis
            "(?P<quote>['\"])" .                            // Match " or ' and store in {quote}
            "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" . // Match any string that can be {quote} escaped
            "\k{quote}" .                                   // Match " or ' previously matched
            "\s*[\),]";                                    // Close parentheses or new parameter

        // Find all PHP + Twig files in the app folder, except for storage
        $finder = new Finder();
        $finder->in($path)->exclude('storage')->exclude('vendor')->name('*.php')->name('*.vue')->files();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            // Search the current file for the pattern
            if (preg_match_all("/$phpKeysPattern/siU", $file->getContents(), $matches)) {
                // Get all matches
                foreach ($matches[2] as $key) {
                    $phpKeys[] = $key;
                }
            }

            if (preg_match_all("/$jsonKeysPattern/siU", $file->getContents(), $matches)) {
                foreach ($matches['string'] as $key) {
                    if (preg_match("/(^[\/a-zA-Z0-9_-]+([.][^\1)\ ]+)+$)/siU", $key, $groupMatches)) {
                        // group{.group}.key format, already in $phpKeys but also matched here
                        // do nothing, it has to be treated as a group
                        continue;
                    }

                    //TODO: This can probably be done in the regex, but I couldn't do it.
                    //skip keys which contain namespacing characters, unless they also contain a
                    //space, which makes it JSON.
                    if (! (Str::contains($key, '::') && Str::contains($key, '.'))
                        || Str::contains($key, ' ')) {
                        $jsonKeys[] = $key;
                    }
                }
            }
        }

        // Remove duplicates
        $this->phpKeys = array_unique($phpKeys);
        $this->jsonKeys = array_unique($jsonKeys);
    }

    public function getPhpKeys(): array
    {
        return $this->phpKeys;
    }

    public function getJsonKeys(): array
    {
        return $this->jsonKeys;
    }
}
