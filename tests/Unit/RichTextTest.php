<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_sanitize_keeps_formatting_and_strips_scripts_handlers_and_bad_links(): void
    {
        $html = RichText::clean(
            '<div>Hi <strong onclick="x()">there</strong><script>alert(1)</script></div>'
            . '<ul><li><a href="javascript:alert(1)">bad</a> <a href="https://gst.test">ok</a></li></ul>'
        );

        $this->assertStringContainsString('<strong>there</strong>', $html);
        $this->assertStringContainsString('<ul><li>', $html);
        $this->assertStringContainsString('href="https://gst.test"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_underline_from_the_editor_is_kept(): void
    {
        $this->assertSame('<div><u>under</u> text</div>', RichText::clean('<div><u>under</u> text</div>'));
    }

    public function test_unknown_wrappers_lose_the_tag_but_keep_the_text(): void
    {
        $this->assertSame('<div>kept and heading</div>', RichText::clean('<div><span>kept</span> and <h1>heading</h1></div>'));
    }

    public function test_an_empty_editor_counts_as_blank(): void
    {
        $this->assertNull(RichText::clean('<div><br></div>'));
        $this->assertNull(RichText::clean('   '));
    }

    public function test_plain_text_is_stored_as_is_and_escaped_on_render(): void
    {
        $text = "Line & one\n<script>x()</script>";

        $this->assertSame($text, RichText::clean($text));
        $this->assertSame("<p>Line &amp; one<br />\n&lt;script&gt;x()&lt;/script&gt;</p>", RichText::render($text));
    }

    public function test_plain_text_loads_into_the_editor_with_its_line_breaks(): void
    {
        $this->assertSame("<div>A &amp; B<br>\nC</div>", RichText::toEditor("A & B\nC"));
    }

    public function test_plain_extracts_readable_text_from_html(): void
    {
        $this->assertSame(
            'Intro & more. One Two',
            RichText::plain('<div>Intro &amp; <strong>more</strong>.</div><ul><li>One</li><li>Two</li></ul>')
        );
    }
}
