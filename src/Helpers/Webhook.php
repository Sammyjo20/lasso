<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Support\Facades\Http;

/**
 * @internal
 */
class Webhook
{
    /**
     * Send a Webhook to a URL
     */
    public static function send(string $url, string $event, array $data = []): void
    {
        rescue(
            callback: static function () use ($url, $event, $data) {
                Http::post($url, array_merge(['event' => $event], $data));
            },
            rescue: false,
            report: false
        );
    }
}
