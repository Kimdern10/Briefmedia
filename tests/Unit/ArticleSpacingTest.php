<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class ArticleSpacingTest extends TestCase
{
    private function format(string $content): string
    {
        return (new Post(['content' => $content]))->formattedContent();
    }

    public function test_empty_paragraphs_are_removed(): void
    {
        $this->assertSame(
            '<p>One</p><p>Two</p>',
            $this->format("<p>One</p><p>&nbsp;</p><p> </p><p><br></p><p>\u{00A0}</p><p>Two</p>")
        );
    }

    public function test_double_line_breaks_split_the_paragraph(): void
    {
        $this->assertSame('<p>Line one</p><p>Line two</p>', $this->format('<p>Line one<br><br>Line two</p>'));
        $this->assertSame('<p>Line one<br>Line two</p>', $this->format('<p>Line one<br>Line two</p>'));
    }

    public function test_double_line_breaks_outside_paragraphs_are_left_alone(): void
    {
        $this->assertSame('<ul><li>A<br><br>B</li></ul>', $this->format('<ul><li>A<br><br>B</li></ul>'));
    }

    public function test_line_breaks_at_the_edges_of_a_paragraph_are_dropped(): void
    {
        $this->assertSame('<p>Text</p>', $this->format('<p><br>Text<br></p>'));
    }

    public function test_plain_text_is_split_into_paragraphs(): void
    {
        $this->assertSame(
            "<p>First paragraph<br>\nstill first</p>\n<p>Second paragraph</p>",
            $this->format("First paragraph\nstill first\r\n\r\n\r\nSecond paragraph")
        );
    }

    public function test_normal_content_is_unchanged(): void
    {
        $html = '<p>One <strong>bold</strong></p><h2>Heading</h2><p>Two</p>';

        $this->assertSame($html, $this->format($html));
        $this->assertSame('', $this->format(''));
    }
}
