<?php

namespace Tests\Handler;

use App\Handler\PageHandler;
use Countable;
use PHPUnit\Framework\TestCase;

class PageHandlerTest extends TestCase
{
    public function test_it_supports_pages()
    {
        $handler = new PageHandler();

        $this->assertTrue($handler->supports('page'));
    }

    public function test_it_does_not_support_other_types()
    {
        $handler = new PageHandler();

        $this->assertFalse($handler->supports('header'));
    }

    public function test_it_receives_the_correct_amount_of_items()
    {
        $handler = new PageHandler();

        $handleResponse = $handler->handle('page-value');

        $this->assertInstanceOf(Countable::class, $handleResponse);
        $this->assertCount(4, $handleResponse);
    }

    public function test_it_contains_the_expected_foo_item()
    {
        $handler = new PageHandler();

        $handleResponse = $handler->handle('page-value');

        $this->assertIsIterable($handleResponse);
        $this->assertContains('foo', $handleResponse);
    }
}
