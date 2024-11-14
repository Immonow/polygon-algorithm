<?php

declare(strict_types=1);

namespace Tests;

use \PHPUnit\Framework\TestCase as BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

abstract class TestCase extends BaseTestCase
{
    use MatchesSnapshots;

    protected function getSnapshotDirectory(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '/__snapshots__';
    }

    protected function getTestDataPath(string $path): string
    {
        return __DIR__ . '/__data__/' . $path;
    }

    protected function getTestData(string $path): string
    {
        return file_get_contents($this->getTestDataPath($path));
    }

    protected function storeTestData(string $path, string $data): static
    {
        file_put_contents($this->getTestDataPath($path), $data);

        return $this;
    }
}
