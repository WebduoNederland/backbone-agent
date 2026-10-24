<?php

namespace WebduoNederland\BackboneAgent\Actions\PhpFileScanner;

use Symfony\Component\Finder\Finder;

class FindPublicPhpFiles
{
    public function find(): array
    {
        $publicPath = public_path();

        if (! is_dir($publicPath)) {
            return [];
        }

        /** @var array<int, string> $extensions */
        $extensions = config()->array('backbone-agent.php_file_scanner.extensions');

        /** @var array<int, string> $allowedFiles */
        $allowedFiles = config()->array('backbone-agent.php_file_scanner.allowed_files');

        $pattern = '/\.('.implode('|', array_map(fn (string $extension): string => preg_quote($extension, '/'), $extensions)).')$/i';

        $finder = Finder::create()
            ->files()
            ->in($publicPath)
            ->name($pattern)
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->ignoreUnreadableDirs()
            ->followLinks();

        $files = [];

        foreach ($finder as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            if (in_array($relativePath, $allowedFiles, true)) {
                continue;
            }

            $files[] = $relativePath;
        }

        sort($files);

        return $files;
    }
}
