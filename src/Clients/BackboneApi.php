<?php

namespace WebduoNederland\BackboneAgent\Clients;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BackboneApi
{
    public function http(): PendingRequest
    {
        $baseUrl = config()->string('backbone-agent.base_url');

        if (blank($baseUrl)) {
            throw new Exception('No Backbone base URL configured in env!');
        }

        $bearerToken = config()->string('backbone-agent.api_key');

        if (blank($bearerToken)) {
            throw new Exception('No Backbone API key configured in env!');
        }

        return Http::baseUrl($baseUrl.'/api')
            ->withToken($bearerToken)
            ->asJson()
            ->acceptJson();
    }
}
