<?php

namespace App\Nexus\Modules\Sitemap\Enums;

enum SitemapChangeFrequency: string
{
    case ALWAYS = 'always';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case NEVER = 'never';

    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => 'Always',
            self::HOURLY => 'Hourly',
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
            self::NEVER => 'Never',
        };
    }
}
