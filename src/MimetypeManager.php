<?php

declare(strict_types=1);

namespace Medas\Files;

use Medas\Core\{Attributes\Service, File};
use Medas\FileSystem\TemporaryFiles;

#[Service]
readonly class MimetypeManager
{
    private \finfo $scanner;

    public function __construct(
        private TemporaryFiles $temporaryFiles,
    )
    {
        if (false === $scanner = finfo_open(FILEINFO_MIME_TYPE)) {
            throw new \Exception('failed to open a file info scanner');
        }

        $this->scanner = $scanner;
    }

    public function __destruct()
    {
        finfo_close($this->scanner);
    }

    public function get(File $file): string
    {
        if ($file->mimetype === null) {
            $path = $this->temporaryFiles->create($file->content);
            $file->mimetype = finfo_file($this->scanner, $path);
        }

        return $file->mimetype;
    }
}
