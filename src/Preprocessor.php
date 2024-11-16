<?php

namespace MartinPL\BladeDirectivesNesting;

class Preprocessor
{
    public function __construct(private string $template) {}

    public function handle(): string
    {
        $position = 0;
        $length = strlen($this->template);
        $openingTags = [];

        while ($position < $length) {
            $tag = $this->findNextTag($position);
            if (! $tag) {
                break;
            }

            if ($tag->isSelfClosing()) {
                $this->wrapSelfClosingTag($tag);
                $position = $tag->ending();
                $length = strlen($this->template);

                continue;
            }

            if ($tag->isClosing()) {
                for ($i = count($openingTags) - 1; $i >= 0; $i--) {
                    if ($tag->name() == $openingTags[$i]->name()) {
                        $this->wrapPairedTag($openingTags[$i], $tag);
                        $length = strlen($this->template);
                        array_splice($openingTags, $i);
                        break;
                    }
                }
            } else {
                $openingTags[] = $tag;
            }

            $position = $tag->ending();
        }

        return $this->template;
    }

    protected function findNextTag(int $position): ?Tag
    {
        $start = strpos($this->template, '<', $position);
        $end = strpos($this->template, '>', $start);
        if ($start === false || $end === false) {
            return null;
        }

        $contentPosition = $end + 1;
        $tag = new Tag(substr($this->template, $start, $contentPosition - $start), $start, $end);
        while ($tag->hasFalseEnding()) {
            $end = strpos($this->template, '>', $contentPosition);
            $contentPosition = $end + 1;
            $tag = new Tag(substr($this->template, $start, $end + 1 - $start), $start, $end);
        }

        return $tag;
    }

    protected function wrapSelfClosingTag(Tag $tag): void
    {
        $wrapper = new Wrapper($tag);
        if ($wrapper->hasDirectives()) {
            $wrapped = $wrapper->wrapSelfClosingTag();
            $this->template = substr_replace($this->template, $wrapped, $tag->start, $tag->end - $tag->start + 1);
        }
    }

    protected function wrapPairedTag(Tag $openingTag, Tag $closingTag): void
    {
        $wrapper = new Wrapper($openingTag);
        if ($wrapper->hasDirectives()) {
            $content = substr($this->template, $openingTag->start, $closingTag->end - $openingTag->start + 1);
            $wrapped = $wrapper->wrapPairedTag($content);
            $this->template = substr_replace($this->template, $wrapped, $openingTag->start, $closingTag->end - $openingTag->start + 1);
        }
    }
}
