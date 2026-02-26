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
        $this->initializeScanner();
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
        $this->initializeScanner();
    }

    private function initializeScanner(): void
    {
        if (false === $scanner = finfo_open(FILEINFO_MIME_TYPE)) {
            throw new Exceptions\FailedToOpenFileInfoScanner();
        }

        $this->scanner = $scanner;
    }

    /**
     * Sets the mimetype on the $file object itself and then returns it.
     */
    public function get(File $file): string
    {
        if ($file->mimetype === null) {
            $path = $this->temporaryFiles->create($file->content);
            $file->mimetype = $this->forFilePath($path);
        }

        return $file->mimetype;
    }

    public function forFilePath(string $path): string
    {
        $result = finfo_file($this->scanner, $path);

        if ($result === false) {
            throw new Exceptions\FailedToDetectMimeType($path);
        }

        return $result;
    }
}
