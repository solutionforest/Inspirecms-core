<?php

namespace SolutionForest\InspireCms\Tests\Models;

use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\Template;

class TemplateTest extends TestCase
{
    public function test_it_can_auto_create_view()
    {
        $this->withoutExceptionHandling();

        /** @var Template */
        $template = Template::create(['slug' => 'test']);

        $this->assertFileExists($template->getFileFullPath());
    }
}
