<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\TipTapExtensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class CmsContentLinkExtension extends Node
{
    /**
     * @var string
     */
    public static $name = 'cmsContentLink';

    public function addOptions(): array
    {
        return [
            'HTMLAttributes' => [
                'class' => 'trix-attachment-contentpicker',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'span.trix-attachment-contentpicker',
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function addAttributes(): array
    {
        return [
            'href' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-url'),
            ],
        ];
    }

    /**
     * @param  object  $node
     * @param  array<string, mixed>  $HTMLAttributes
     * @return array<mixed>
     */
    public function renderHTML($node, array $HTMLAttributes = [])
    {
        $url = $node->attrs?->url ?? '';
        $title = $node->attrs?->title ?? '';
        $target = $node->attrs?->target ?? null;

        // Build link attributes
        $linkAttrs = [
            'href' => $url,
        ];

        if ($target) {
            $linkAttrs['target'] = $target;
            if ($target === '_blank') {
                $linkAttrs['rel'] = 'noopener noreferrer';
            }
        }

        $content = sprintf(
            '<a %s>%s</a>',
            collect(HTML::mergeAttributes(
                $this->options['HTMLAttributes'],
                [
                    ...$HTMLAttributes,
                    'data-content-id' => $node->attrs?->id ?? null,
                    'data-content-slug' => $node->attrs?->slug ?? null,
                    ...$linkAttrs,
                ]
            ))->map(fn ($value, $key) => sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value)))->implode(' '),
            $title ?: 'Content Link'
        );

        return [
            'content' => $content,
        ];
    }
}
