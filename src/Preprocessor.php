<?php

namespace MartinPL\BladeDirectivesNesting;

class Preprocessor
{
    private const directivePattern = '/(?<!\S)@(\w+)\s*(\([^)]*\))?/';

    public function __construct(private string $template) {}

    public function handle(): string
    {
        $position = 0;
        $length = strlen($this->template);
        $structure = [];

        while ($position < $length) {
            $start = strpos($this->template, '<', $position);
            if ($start === false) {
                break;
            }

            $end = strpos($this->template, '>', $start);
            if ($end === false) {
                break;
            }

            $contentPosition = $end + 1;
            $tag = new Tag(substr($this->template, $start, $contentPosition - $start));

            while ($tag->hasFalseEnding()) {
                $end = strpos($this->template, '>', $contentPosition);
                $contentPosition = $end + 1;
                $tag = new Tag(substr($this->template, $start, $end + 1 - $start));
            }

            if ($tag->isSelfClosing()) {
                $this->wrapSelfClosingTag($tag, $start, $end);
                $position = $contentPosition;

                continue;
            }

            if ($tag->isClosing()) {
                for ($i = count($structure) - 1; $i >= 0; $i--) {
                    if ($tag->name() == $structure[$i]['tag']) {
                        $this->wrapContentTag($structure[$i], $end);
                        array_splice($structure, $i);
                        break;
                    }
                }
            } else {
                $structure[] = [
                    'tag' => $tag->name(),
                    'start' => $start,
                    'content' => $contentPosition,
                ];
            }

            $position = $contentPosition;
        }

        return $this->template;
    }

    private function wrapSelfClosingTag(Tag $tag, int $start, int $end): void
    {
        if (preg_match(self::directivePattern, $tag, $matches)) {
            $directive = $matches[0];
            $directiveName = $matches[1];
            $cleanTag = str_replace(' '.$directive, '', $tag);
            $wrappedTag = "{$directive}{$cleanTag}@end{$directiveName} ";

            $this->template = substr_replace($this->template, $wrappedTag, $start, $end - $start + 1);
        }
    }

    private function wrapContentTag(array $structure, int $end): void
    {
        $openingTag = substr($this->template, $structure['start'], $structure['content'] - $structure['start']);
        if (preg_match(self::directivePattern, $openingTag, $matches)) {
            $directive = $matches[0];
            $directiveName = $matches[1];
            $content = substr($this->template, $structure['start'], $end - $structure['start'] + 1);
            $cleanOpeningTag = str_replace(' '.$directive, '', $openingTag);
            $cleanContent = str_replace($openingTag, $cleanOpeningTag, $content);
            $wrappedTag = "{$directive}{$cleanContent}@end{$directiveName} ";

            $this->template = substr_replace($this->template, $wrappedTag, $structure['start'], $end - $structure['start'] + 1);
        }
    }
}
