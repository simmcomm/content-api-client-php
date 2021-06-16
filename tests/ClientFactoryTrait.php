<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\ContentApiClient;
use Symfony\Component\HttpClient\MockHttpClient;

trait ClientFactoryTrait
{
    protected function createClient(): ContentApiClient
    {
        $httpClient = new MockHttpClient(new MockResponseLoader());

        $client = new ContentApiClient('', '', null, $httpClient);

        return $client->setAuthAlias('TESTING');
    }

}