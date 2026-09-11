<?php

namespace App\Enums;

enum FileStatus: string
{
    case UPLOADED = 'uploaded';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case ANALYZED = 'analyzed';
    case FAILED = 'failed';
}
