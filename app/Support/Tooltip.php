<?php

namespace App\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Js;

class Tooltip implements Htmlable
{
    public function __construct(
        private string|Htmlable $content,
        private string $tooltip,
    ) {}

    public function toHtml(): string
    {
        $content = (string) $this->content;

        return sprintf(
            '<span class="fi-tooltip cursor-help underline decoration-dotted decoration-gray-400 underline-offset-4" x-tooltip="{%s}">%s</span>',
            'content: '.Js::from($this->tooltip).', theme: $store.theme, allowHTML: false',
            $content,
        );
    }
}
