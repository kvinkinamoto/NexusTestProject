<?php

namespace App\Nexus\Modules\Demo\Enums;

enum DemoStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
