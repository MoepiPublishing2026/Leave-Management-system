<?php

namespace App\Support;

class RunsArtisanInBackground
{
    public static function call(string $command, string ...$arguments): void
    {
        $tokens = array_merge(
            [escapeshellarg(PHP_BINARY), 'artisan', $command],
            array_map(static fn (string $argument) => escapeshellarg($argument), $arguments)
        );
        $inner = implode(' ', $tokens);

        if (PHP_OS_FAMILY === 'Windows') {
            $line = sprintf(
                'start "leave-mail" /B /D %s %s',
                escapeshellarg(base_path()),
                $inner
            );
            pclose(popen($line, 'r'));

            return;
        }

        exec('cd '.escapeshellarg(base_path()).' && '.$inner.' > /dev/null 2>&1 &');
    }
}
