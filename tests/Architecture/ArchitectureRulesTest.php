<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ArchitectureRulesTest extends TestCase
{
    private string $baseAppPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseAppPath = realpath(__DIR__ . '/../../app') ?: '';
    }

    /**
     * R-01: Domain no importa nada de Laravel ni de terceros (Illuminate\).
     */
    public function test_r01_domain_does_not_import_illuminate(): void
    {
        $domainPath = $this->baseAppPath . DIRECTORY_SEPARATOR . 'Domain';
        $violations = $this->scanDirectoryForForbiddenImports($domainPath, ['Illuminate\\', 'DB::']);

        $this->assertEmpty(
            $violations,
            "Violación de regla R-01: Domain tiene dependencias de Laravel:\n" . implode("\n", $violations)
        );
    }

    /**
     * R-03: Presentation no importa directamente clases de Infrastructure.
     */
    public function test_r03_presentation_does_not_import_infrastructure(): void
    {
        $presentationPath = $this->baseAppPath . DIRECTORY_SEPARATOR . 'Presentation';
        $violations = $this->scanDirectoryForForbiddenImports($presentationPath, ['App\\Infrastructure\\Persistence\\Models\\']);

        $this->assertEmpty(
            $violations,
            "Violación de regla R-03: Presentation importa directamente modelos de persistencia:\n" . implode("\n", $violations)
        );
    }

    /**
     * Regla adicional: Application no debe usar DB:: directamente (debe usar UnitOfWork).
     */
    public function test_application_does_not_use_db_facade(): void
    {
        $applicationPath = $this->baseAppPath . DIRECTORY_SEPARATOR . 'Application';
        $violations = $this->scanDirectoryForForbiddenImports($applicationPath, ['DB::', 'Illuminate\\Support\\Facades\\DB']);

        $this->assertEmpty(
            $violations,
            "Violación de regla Onion: Application usa DB:: directamente en vez de UnitOfWork:\n" . implode("\n", $violations)
        );
    }

    /**
     * @param string[] $forbidden
     * @return string[]
     */
    private function scanDirectoryForForbiddenImports(string $directory, array $forbidden): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $violations = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname()) ?: '';
            foreach ($forbidden as $forbiddenPattern) {
                if (str_contains($content, $forbiddenPattern)) {
                    $violations[] = "{$file->getPathname()} contiene '{$forbiddenPattern}'";
                }
            }
        }

        return $violations;
    }
}
