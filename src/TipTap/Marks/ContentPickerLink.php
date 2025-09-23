<?php

namespace SolutionForest\InspireCms\TipTap\Marks;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

class ContentPickerLink extends Mark
{
    public static $name = 'contentPickerLink';

    public static $priority = 101;

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [
                'target' => '_blank',
                'rel' => 'noopener noreferrer nofollow',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'href' => [],
            'target' => [],
            'rel' => [],
            'class' => [],
            'data-content-id' => [],
            'data-content-slug' => [],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return [
            'a',
            HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes),
            0,
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'a[href]',
                'getAttrs' => function ($DOMNode) {
                    $href = $DOMNode->getAttribute('href');

                    if (empty($href)) {
                        return false;
                    }

                    return null;
                },
            ],
        ];
    }
}
