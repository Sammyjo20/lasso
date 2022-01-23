<?php

namespace Sammyjo20\Lasso\Helpers;

class CompilerOutputFormatter
{
    private const PERCENTAGE_REGEX = '/\b(?<!\.)(?!0+(?:\.0+)?%)(?:\d|[1-9]\d|100)(?:(?<!100)\.\d+)?%/';

    /**
     * Attempt to find the progress percentage from the line returned by the compiler.
     *
     * @param string $line
     * @return int
     */
    public static function getWebpackProgress(string $line): int
    {
        $line = trim($line);

        if (empty($line)) {
            return 0;
        }

        if (! str_contains($line, '<s> [webpack.Progress]')) {
            return 0;
        }

        preg_match(self::PERCENTAGE_REGEX, $line, $matches, PREG_OFFSET_CAPTURE);

        $percentage = $matches[0][0] ?? null;

        return (int)str_replace('%', '', $percentage);
    }
}
