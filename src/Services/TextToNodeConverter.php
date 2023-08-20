<?php

namespace SaveToInstapaperBot\Services;

use DiDom\Document;

class TextToNodeConverter
{
    public function convert($text)
    {
        $html = new Document($text);
        $nodes = [];

        $root = $html->first('p');
        $nodes = $this->collectElemenets($root);

        $nodes = [[
            'tag' => 'p',
            'children' => $nodes,
        ]];

        return $nodes;
    }


    private function collectElemenets($parent)
    {
        $nodes = [];
        $elements = $parent->children();

        foreach ($elements as $element) {
            if ($element->isTextNode()) {
                $nodes[] = [
                    'tag' => 'span',
                    'children' => $this->collectElemenets($element) ? $this->collectElemenets($element) : [$element->text()],
                ];
            } elseif ($element->isElementNode()) {
                if ($element->tagName() === 'a') {
                    $nodes[] = [
                        'tag' => 'a',
                        'attrs' => [
                            'href' => $element->href,
                        ],
                        'children' => $this->collectElemenets($element) ? $this->collectElemenets($element) : [$element->text()],
                    ];
                } else {
                    $nodes[] = [
                        'tag' => $element->tagName(),
                        'children' => $this->collectElemenets($element) ? $this->collectElemenets($element) : [$element->text()],
                    ];
                }
            }
        }

        return $nodes;
    }
}
