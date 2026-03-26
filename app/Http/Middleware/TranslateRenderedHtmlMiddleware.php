<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DOMNode;
use DOMXPath;
use Symfony\Component\HttpFoundation\Response;

class TranslateRenderedHtmlMiddleware
{
    private static ?array $cachedMap = null;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->getLocale() !== 'en') {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && !str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        $map = $this->getReplacementMap();
        if ($map === []) {
            return $response;
        }

        $translated = $this->translateHtmlContent($content, $map);
        $response->setContent($translated);

        return $response;
    }

    private function getReplacementMap(): array
    {
        if (self::$cachedMap !== null) {
            return self::$cachedMap;
        }

        $map = (array) config('runtime_locale_map.ro_to_en', []);

        uksort($map, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        self::$cachedMap = $map;

        return self::$cachedMap;
    }

    private function translateHtmlContent(string $html, array $map): string
    {
        if (!class_exists(\DOMDocument::class)) {
            return strtr($html, $map);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return strtr($html, $map);
        }

        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//text()[normalize-space()]') as $node) {
            if (!$node instanceof DOMNode) {
                continue;
            }

            if ($this->hasIgnoredAncestor($node)) {
                continue;
            }

            $node->nodeValue = strtr((string) $node->nodeValue, $map);
        }

        foreach ($xpath->query('//*[@placeholder or @title or @aria-label]') as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            foreach (['placeholder', 'title', 'aria-label'] as $attributeName) {
                if (!$node->hasAttribute($attributeName)) {
                    continue;
                }

                $node->setAttribute($attributeName, strtr($node->getAttribute($attributeName), $map));
            }
        }

        $result = $dom->saveHTML();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!is_string($result) || $result === '') {
            return $html;
        }

        return (string) preg_replace('/^<\?xml[^>]+\?>\s*/', '', $result);
    }

    private function hasIgnoredAncestor(DOMNode $node): bool
    {
        $current = $node->parentNode;

        while ($current) {
            $name = strtolower((string) $current->nodeName);
            if (in_array($name, ['script', 'style'], true)) {
                return true;
            }

            $current = $current->parentNode;
        }

        return false;
    }
}
