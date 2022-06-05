<?php

namespace CodeIgniter\Shield\Commands\Setup;

class ContentReplacer
{
    /**
     * @param array $replaces [search => replace]
     */
    public function replace(string $content, array $replaces): string
    {
        foreach ($replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * @param string $text    Text to add.
     * @param string $pattern Regexp search pattern.
     * @param string $replace Regexp replacement including text to add.
     *
     * @return bool|string true: already updated, false: regexp error.
     */
    public function add(string $content, string $text, string $pattern, string $replace)
    {
        $return = preg_match('/' . preg_quote($text, '/') . '/u', $content);

        if ($return === 1) {
            // It has already been updated.

            return true;
        }

        if ($return === false) {
            // Regexp error.

            return false;
        }

        return preg_replace($pattern, $replace, $content);
    }
}
