<?php

namespace MartinPL\BladeDirectivesNesting;

class Tag
{
    protected $name;

    public function __construct(public string $tag, public int $start, public int $end)
    {
        $this->name = strtolower(trim(strtok($this->tag, ' '), '</>'));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isSelfClosing(): bool
    {
        /**
         * @see https://developer.mozilla.org/en-US/docs/Glossary/Void_element
         */
        $voidElements = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

        return substr($this->tag, -2) == '/>' || in_array($this->name, $voidElements);
    }

    public function isClosing(): bool
    {
        return $this->tag[1] === '/';
    }

    public function hasFalseEnding(): bool
    {
        return substr($this->tag, -2) == '->';
    }

    public function ending(): int
    {
        return $this->end + 1;
    }

    public function __toString(): string
    {
        return $this->tag;
    }
}
