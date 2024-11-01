<?php

namespace MartinPL\BladeDirectivesNesting;

class Tag
{
    public function __construct(public string $tag) {}

    public function name(): string
    {
        return trim(strtok($this->tag, ' '), '</>');
    }

    public function isSelfClosing(): bool
    {
        return substr($this->tag, -2) == '/>';
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
