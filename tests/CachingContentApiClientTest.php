<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\CachingContentApiClient;
use Flowly\Content\ContentApiClientInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 * @covers \Flowly\Content\CachingContentApiClient
 */
class CachingContentApiClientTest extends ContentApiClientTest
{
    protected function createClient(): ContentApiClientInterface
    {
        $client = parent::createClient();

        return new CachingContentApiClient($client, new ArrayAdapter());
    }
}