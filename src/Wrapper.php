<?php

namespace MartinPL\BladeDirectivesNesting;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Wrapper
{
    private $directives;

    private $directivesNames;

    public function __construct(private string $tag)
    {
        [$this->directives, $this->directivesNames] = $this->directives($tag);
    }

    public function hasDirectives()
    {
        return ! empty($this->directives);
    }

    public function wrapPairedTag($content)
    {
        $wrap = $this->addStart($this->tag);
        $cleanContent = str_replace($this->tag, $wrap, $content);

        return $this->addEnd($cleanContent);
    }

    public function wrapSelfClosingTag()
    {
        $wrap = $this->addStart($this->tag);

        return $this->addEnd($wrap);
    }

    protected function addStart($tag)
    {
        $directivesWithSpaceOnStart = array_map(fn ($xd) => ' '.$xd, $this->directives);
        $cleanTag = str_replace($directivesWithSpaceOnStart, '', $tag);
        $directivesAsString = implode(' ', $this->directives);

        return "{$directivesAsString}{$cleanTag}";
    }

    protected function addEnd($content)
    {
        $ends = '@end'.implode(' @end', array_reverse($this->directivesNames));

        return "{$content}{$ends} ";
    }

    /**
     * @source https://github.com/illuminate/view/blob/11.x/Compilers/BladeCompiler.php - compileStatements()
     */
    private function directives($template): array
    {
        $directive = [];
        $directiveName = [];

        preg_match_all('/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x', $template, $matches);
        for ($i = 0; isset($matches[0][$i]); $i++) {
            $match = [
                $matches[0][$i],
                $matches[1][$i],
                $matches[2][$i],
                $matches[3][$i] ?: null,
                $matches[4][$i] ?: null,
            ];

            // Here we check to see if we have properly found the closing parenthesis by
            // regex pattern or not, and will recursively continue on to the next ")"
            // then check again until the tokenizer confirms we find the right one.
            while (isset($match[4]) &&
                   Str::endsWith($match[0], ')') &&
                   ! $this->hasEvenNumberOfParentheses($match[0])) {
                if (($after = Str::after($template, $match[0])) === $template) {
                    break;
                }

                $rest = Str::before($after, ')');

                if (isset($matches[0][$i + 1]) && Str::contains($rest.')', $matches[0][$i + 1])) {
                    unset($matches[0][$i + 1]);
                    $i++;
                }

                $match[0] = $match[0].$rest.')';
                $match[3] = $match[3].$rest.')';
                $match[4] = $match[4].$rest;
            }

            $directive[] = $match[0];
            $directiveName[] = $match[1];
        }

        return [$directive, $directiveName];
    }

    /**
     * @source https://github.com/illuminate/view/blob/11.x/Compilers/BladeCompiler.php
     */
    protected function hasEvenNumberOfParentheses(string $expression): bool
    {
        $tokens = token_get_all('<?php '.$expression);

        if (Arr::last($tokens) !== ')') {
            return false;
        }

        $opening = 0;
        $closing = 0;

        foreach ($tokens as $token) {
            if ($token == ')') {
                $closing++;
            } elseif ($token == '(') {
                $opening++;
            }
        }

        return $opening === $closing;
    }
}
