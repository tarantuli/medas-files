<?php

declare(strict_types=1);

namespace Medas\Files;

use Medas\Core\{AsSingleton, BasePackage};
use Medas\FileSystem\FileSystemPackage;

class FilesPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            FileSystemPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
