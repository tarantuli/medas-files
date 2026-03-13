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
        private \WeakMap       $hashes = new \WeakMap(),
    )
    {
        $this->initializeScanner();
    }

    public function __serialize(): array
    {
        return [
            'temporaryFiles' => $this->temporaryFiles,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->temporaryFiles = $data['temporaryFiles'];
        $this->hashes = new \WeakMap();

        $this->initializeScanner();
    }

    private function initializeScanner(): void
    {
        if (false === $scanner = finfo_open(FILEINFO_MIME_TYPE)) {
            throw new Exceptions\FailedToOpenFileInfoScanner();
        }

        $this->scanner = $scanner;
    }

    public function get(File $file): string
    {
        if (!$this->hashes->offsetExists($file)) {
            $path = $this->temporaryFiles->create($file->content);

            $this->hashes->offsetSet($file, $this->forFilePath($path));
        }

        return $this->hashes->offsetGet($file);
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
