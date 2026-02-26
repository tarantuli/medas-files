<?php

declare(strict_types=1);

namespace Medas\Files\Exceptions;

use Medas\Core\Exceptions\BaseException;

class FailedToDetectMimeType extends BaseException
{
    public function __construct(string $path)
    {
        parent::__construct($path);
    }

    public function pattern(): string
    {
        return 'Failed to detect mime type for file "%s"';
    }
}
