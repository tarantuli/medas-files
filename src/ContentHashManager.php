<?php

declare(strict_types=1);

namespace Medas\Files;

use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class ContentHashManager
{
    public function get(File $file): string
    {
        if ($file->contentHash === null) {
            $file->contentHash = sha1($file->content);
        }

        return $file->contentHash;
    }
}
