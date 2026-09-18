<?php

namespace App\Nexus\Modules\Advertising\Enums;

/**
 * `Html` covers both a Google AdSense unit and any other custom HTML/JS
 * snippet (AdSense is not a separate case — an AdSense `<ins>`/`<script>`
 * block is just pasted into `content_html` like any other embed code).
 * `Banner` is the image+link case with no embed code at all.
 */
enum AdvertisingType: string
{
    case Html = 'html';
    case Banner = 'banner';
}
