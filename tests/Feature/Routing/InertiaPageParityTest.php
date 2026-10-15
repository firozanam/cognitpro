<?php

namespace Tests\Feature\Routing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every backend Inertia::render() name must resolve to a page component
 * under resources/js/pages — component resolution happens client-side
 * via import.meta.glob, so a missing file 200s on the server and fails
 * only in the browser (FEA-01 / GF-1-02).
 */
class InertiaPageParityTest extends TestCase
{
    public static function renderNameProvider(): array
    {
        $appPath = dirname(__DIR__, 3).'/app/Modules';
        $pagesPath = dirname(__DIR__, 3).'/resources/js/pages';

        $renderNames = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match_all("/Inertia::render\(\s*'([^']+)'/", $contents, $matches)) {
                foreach ($matches[1] as $name) {
                    $renderNames[$name] = [$name, $pagesPath];
                }
            }
        }

        if ($renderNames === []) {
            self::fail('No Inertia::render() calls found under app/Modules — the scan is broken.');
        }

        return $renderNames;
    }

    #[DataProvider('renderNameProvider')]
    public function test_render_name_resolves_to_a_page_component(string $name, string $pagesPath): void
    {
        $extensions = ['tsx', 'ts', 'jsx', 'js', 'vue'];

        foreach ($extensions as $extension) {
            if (is_file("{$pagesPath}/{$name}.{$extension}")) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $this->fail("Inertia page '{$name}' has no component under resources/js/pages.");
    }
}
