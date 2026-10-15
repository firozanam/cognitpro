<?php

namespace Tests\Feature\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CiWorkflowBranchFilterTest extends TestCase
{
    #[Test]
    public function workflows_trigger_on_master(): void
    {
        foreach (['lint.yml', 'tests.yml'] as $file) {
            $content = (string) file_get_contents(dirname(__DIR__, 3).'/.github/workflows/'.$file);

            $this->assertStringContainsString('- master', $content, "{$file} must include master in its branch filters (CFG-01).");
        }
    }
}
