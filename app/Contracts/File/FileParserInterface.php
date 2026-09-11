<?php

namespace App\Contracts\File;


use App\DTOs\File\ParsedFileDTO;
use App\Models\File;

interface FileParserInterface
{
    public function supports(string $type): bool;

    public function parse(File $file): ParsedFileDTO;
}
