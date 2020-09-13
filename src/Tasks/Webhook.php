<?php

namespace Sammyjo20\Lasso\Tasks;

use Illuminate\Support\Facades\Http;

final class Webhook
{
    /**
     * @param string $url
     * @param string $event
     * @param array $data
     * @return bool
     */
    public static function send(string $url, string $event, array $data = [])
    {
        rescue(function () use ($url, $event, $data) {
            Http::post($url, array_merge(['event' => $event], $data));
        }, false, false);

        return true;
    }
}
