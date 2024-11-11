<?php

namespace MartinPL\BladeDirectivesNesting;

class Tag
{
    private $name;

    /**
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Void_element
     */
    private $voidElements = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    public function __construct(public string $tag)
    {
        $this->name = strtolower(trim(strtok($this->tag, ' '), '</>'));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isSelfClosing(): bool
    {
        return substr($this->tag, -2) == '/>' || in_array($this->name, $this->voidElements);
    }

    public function isClosing(): bool
    {
        return $this->tag[1] === '/';
    }

    public function hasFalseEnding(): bool
    {
        return substr($this->tag, -2) == '->';
    }

    public function __toString(): string
    {
        return $this->tag;
    }
}
