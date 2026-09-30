<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Job description fields hold either legacy plain text (newline-delimited) or
 * HTML from the admin's rich-text editor. Every read and write goes through
 * here so the two shapes render the same way and HTML is always sanitized.
 */
class RichText
{
    // Tags the editor produces; anything matching is treated as HTML rather than plain text.
    private const HTML_PATTERN = '/<\/?(div|p|br|strong|b|em|i|u|del|h[1-6]|ul|ol|li|blockquote|pre|a|span)\b[^>]*>/i';

    private static ?HtmlSanitizer $sanitizer = null;

    public static function isHtml(?string $value): bool
    {
        return $value !== null && preg_match(self::HTML_PATTERN, $value) === 1;
    }

    public static function sanitize(string $html): string
    {
        return self::sanitizer()->sanitize($html);
    }

    /** Clean for storage; plain text is kept as-is and escaped at render time instead. */
    public static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = self::isHtml($value) ? self::sanitize($value) : $value;

        return trim(self::plain($value)) === '' ? null : $value;
    }

    /** Safe HTML for display. */
    public static function render(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        return self::isHtml($value)
            ? self::sanitize($value)
            : '<p>' . nl2br(e($value)) . '</p>';
    }

    /** HTML for loading into the editor; legacy plain text keeps its line breaks. */
    public static function toEditor(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return self::isHtml($value)
            ? self::sanitize($value)
            : '<div>' . nl2br(e($value), false) . '</div>';
    }

    /** Unformatted text for excerpts and meta descriptions. */
    public static function plain(?string $value): string
    {
        if ($value === null || ! self::isHtml($value)) {
            return strip_tags((string) $value);
        }

        // Keep words from separate blocks apart once the tags are gone.
        $spaced = preg_replace('/<br\b[^>]*>|<\/(div|p|li|h[1-6]|blockquote|pre)>/i', '$0 ', self::sanitize($value));

        return Str::squish(html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private static function sanitizer(): HtmlSanitizer
    {
        if (self::$sanitizer) {
            return self::$sanitizer;
        }

        $config = (new HtmlSanitizerConfig())
            // Validation caps the fields at 20,000 chars; don't truncate before it can.
            ->withMaxInputLength(100_000)
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer nofollow')
            ->allowElement('a', ['href']);

        foreach (['div', 'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'del', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre'] as $tag) {
            $config = $config->allowElement($tag);
        }

        // Unlisted tags are dropped with their text; these wrappers only lose the tag.
        foreach (['span', 'font', 'h1', 'h2', 'h4', 'h5', 'h6', 'section', 'article'] as $tag) {
            $config = $config->blockElement($tag);
        }

        return self::$sanitizer = new HtmlSanitizer($config);
    }
}
