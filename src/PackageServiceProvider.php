<?php

namespace MartinPL\BladeDirectivesNesting;

use Illuminate\Support\Facades\Blade;

class PackageServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        Blade::prepareStringsForCompilationUsing(fn ($template) => (new Preprocessor($template))->handle());
    }
}
