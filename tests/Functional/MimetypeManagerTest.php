<?php

declare(strict_types=1);

namespace Medas\FilesTest\Functional;

use Medas\Core\File;
use Medas\Files\MimetypeManager;
use PHPUnit\Framework\TestCase;

class MimetypeManagerTest extends TestCase
{
    public function testBasicFunctionality(): void
    {
        $file = new File(file_get_contents(__DIR__ . '/../MockUps/example.jpg'));
        $manager = service(MimetypeManager::class);

        self::assertEquals('image/jpeg', $manager->get($file));
    }
}
