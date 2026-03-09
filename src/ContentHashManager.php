<?php

declare(strict_types=1);

namespace Medas\Files;

use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class ContentHashManager
{
    public function __construct(
        private \WeakMap $hashes = new \WeakMap(),
    )
    {
    }

    public function get(File $file): string
    {
        if (!$this->hashes->offsetExists($file)) {
            $this->hashes->offsetSet($file, sha1($file->content));
        }

        return $this->hashes->offsetGet($file);
    }
}
