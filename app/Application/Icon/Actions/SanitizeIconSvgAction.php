<?php

namespace App\Application\Icon\Actions;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;

class SanitizeIconSvgAction
{
    private const BLOCKED_ELEMENTS = [
        'script',
        'foreignobject',
        'iframe',
        'object',
        'embed',
        'audio',
        'video',
        'canvas',
        'style',
        'animate',
        'animatemotion',
        'animatetransform',
        'set',
        'mpath',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'button',
        'textarea',
        'select',
    ];

    private const BLOCKED_ATTRIBUTES = [
        'style',
        'src',
        'data',
        'formaction',
    ];

    public function execute(string $svg): string
    {
        $svg = trim($svg);

        if ($svg === '') {
            throw new RuntimeException('SVG markup is empty.');
        }

        if (!class_exists(DOMDocument::class)) {
            throw new RuntimeException('The PHP DOM extension is required to sanitize SVG icons.');
        }

        if (stripos($svg, '<!DOCTYPE') !== false || stripos($svg, '<!ENTITY') !== false) {
            throw new RuntimeException('SVG markup must not contain a document type or custom entities.');
        }

        if (strlen($svg) > 524288) {
            throw new RuntimeException('SVG markup is larger than 512 KB.');
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');

        try {
            $loaded = $document->loadXML(
                $svg,
                LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING,
            );

            if (!$loaded || !$document->documentElement instanceof DOMElement) {
                throw new RuntimeException('SVG markup is not valid XML.');
            }

            $root = $document->documentElement;

            if (strtolower($root->localName) !== 'svg') {
                throw new RuntimeException('The root element must be <svg>.');
            }

            $xpath = new DOMXPath($document);

            $this->removeComments($xpath);
            $this->removeBlockedElements($xpath);
            $this->sanitizeAttributes($xpath);

            $root->setAttribute('width', '1em');
            $root->setAttribute('height', '1em');
            $root->setAttribute('aria-hidden', 'true');
            $root->setAttribute('focusable', 'false');

            $sanitized = $document->saveXML($root);

            if (!is_string($sanitized) || trim($sanitized) === '') {
                throw new RuntimeException('SVG markup could not be serialized.');
            }

            return trim($sanitized);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function removeComments(DOMXPath $xpath): void
    {
        $comments = [];

        foreach ($xpath->query('//comment()') ?: [] as $comment) {
            $comments[] = $comment;
        }

        foreach ($comments as $comment) {
            $comment->parentNode?->removeChild($comment);
        }
    }

    private function removeBlockedElements(DOMXPath $xpath): void
    {
        $nodes = [];

        foreach ($xpath->query('//*') ?: [] as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if (in_array(strtolower($node->localName), self::BLOCKED_ELEMENTS, true)) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function sanitizeAttributes(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//*') ?: [] as $node) {
            if (!$node instanceof DOMElement || !$node->hasAttributes()) {
                continue;
            }

            /** @var array<int, DOMAttr> $attributesToRemove */
            $attributesToRemove = [];

            foreach ($node->attributes as $attribute) {
                if (!$attribute instanceof DOMAttr) {
                    continue;
                }

                $attributeName = strtolower($attribute->nodeName);
                $attributeLocalName = strtolower($attribute->localName ?: $attribute->nodeName);
                $attributeValue = trim($attribute->nodeValue ?? '');

                if (str_starts_with($attributeName, 'on')) {
                    $attributesToRemove[] = $attribute;
                    continue;
                }

                if (in_array($attributeLocalName, self::BLOCKED_ATTRIBUTES, true)) {
                    $attributesToRemove[] = $attribute;
                    continue;
                }

                if ($attributeLocalName === 'href' && !str_starts_with($attributeValue, '#')) {
                    $attributesToRemove[] = $attribute;
                    continue;
                }

                if (preg_match('/(?:javascript|vbscript|data):/i', $attributeValue) === 1) {
                    $attributesToRemove[] = $attribute;
                    continue;
                }

                if (
                    stripos($attributeValue, 'url(') !== false
                    && preg_match('/^url\(\s*#[^)]+\s*\)$/i', $attributeValue) !== 1
                ) {
                    $attributesToRemove[] = $attribute;
                }
            }

            foreach ($attributesToRemove as $attribute) {
                if ($attribute->ownerElement === $node) {
                    $node->removeAttributeNode($attribute);
                }
            }
        }
    }
}
