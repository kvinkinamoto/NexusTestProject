<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Contracts;

interface SocialProviderInterface
{
    public function fetchPosts(): array;
}
