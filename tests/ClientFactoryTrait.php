<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\ContentApiClient;
use Flowly\Content\ContentApiClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;

trait ClientFactoryTrait
{
    protected function createClient(): ContentApiClientInterface
    {
        $httpClient = new MockHttpClient(new MockResponseLoader());

        $client = new ContentApiClient('', '', null, $httpClient);

        return $client->setAuthAlias('TESTING');
    }

}