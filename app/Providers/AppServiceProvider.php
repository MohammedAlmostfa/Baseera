<?php

namespace App\Providers;

use App\Contracts\File\FileParserInterface;
use App\Services\File\FileParserResolver;
use App\Services\File\Parsers\CsvFileParser;
use App\Services\File\Parsers\ExcelFileParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileParserResolver::class, function ($app) {
            return new FileParserResolver([
                $app->make(CsvFileParser::class),
                $app->make(ExcelFileParser::class),
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}
