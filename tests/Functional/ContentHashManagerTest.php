<?php

declare(strict_types=1);

namespace Medas\FilesTest\Functional;

use Medas\Core\File;
use Medas\Files\ContentHashManager;
use PHPUnit\Framework\TestCase;

class ContentHashManagerTest extends TestCase
{
    public function testBasicFunctionality(): void
    {
        $file = new File(file_get_contents(__DIR__ . '/../MockUps/example.jpg'));
        $manager = service(ContentHashManager::class);

        self::assertEquals('055947e487d3630a43d57f38b01e29edd9d7ac79', $manager->get($file));
    }
}
