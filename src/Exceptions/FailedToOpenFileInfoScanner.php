<?php

declare(strict_types=1);

namespace Medas\Files\Exceptions;

use Medas\Core\Exceptions\BaseException;

class FailedToOpenFileInfoScanner extends BaseException
{
    public function pattern(): string
    {
        return 'Failed to open file info scanner';
    }
}
