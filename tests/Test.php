<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;

class Test extends \Tests\TestCase
{
    public function testbaseFunctionality()
    {
        $output = Blade::render('<div #foreach([0, 1] as $index)><div #if($loop->index == 1)>Hello {{ $loop->index }}</div></div>');
        $this->assertEquals('<div> </div><div><div>Hello 1</div> </div> ', $output);
    }

    public function testDirectiveWithSpace()
    {
        $output = Blade::render('<div #if (false)>content</div><div #if (false)>content</div>');
        $this->assertEquals('', $output);
    }

    public function testDirectiveWithoutArgument()
    {
        $output = Blade::render('<div #guest>content</div>');
        $this->assertEquals('<div>content</div> ', $output);
    }

    public function testDirectivesInRow()
    {
        $output = Blade::render('<div #if(false)>content</div><div #if(false)>content</div>');
        $this->assertEquals('', $output);
    }

    public function testDirectiveBetweenAttributes()
    {
        $output = Blade::render('<div left="attribute" #if(true) right="attribute">content</div>');
        $this->assertEquals('<div left="attribute" right="attribute">content</div> ', $output);
    }

    public function testSelfClosingComponent()
    {
        Blade::component('test', Component::class);
        $output = Blade::render('<x-test left="attribute" #if(true) />');
        $this->assertEquals('<div left="attribute">content</div> ', $output);
    }

    public function testMultipleDirectivesInOnTag()
    {
        $output = Blade::render('<div #foreach([0, 1] as $index) attribute="center" #if($loop->index == 1)>Hello @if($loop->index == 1){{ $loop->index }}@endif</div>');
        $this->assertEquals('<div attribute="center">Hello 1</div>  ', $output);
    }
}

class Component extends \Illuminate\View\Component
{
    public function render()
    {
        return <<<'blade'
            <div {{ $attributes }}>content</div>
        blade;
    }
}
