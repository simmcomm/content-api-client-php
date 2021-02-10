<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\ContentApiClient;
use Flowly\Content\ContentApiClientInterface;
use Flowly\Content\Request\GetScenesRequest;
use Flowly\Content\Response\Scene;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 * @covers \Flowly\Content\ContentApiClient
 * @covers \Flowly\Content\SceneLinkDenormalizer
 */
class ContentApiClientTest extends TestCase
{

    public function test__construct(): void
    {
        self::assertInstanceOf(ContentApiClientInterface::class, $this->createClient());
    }

    private function createClient(): ContentApiClient
    {
        $httpClient = new MockHttpClient(new MockResponseLoader());

        return new ContentApiClient('', '', $httpClient);
    }

    public function testGetScenesLanding()
    {

    }

    public function testGetScene()
    {

    }

    public function testGetActors()
    {

    }

    public function testSubmitRating()
    {

    }

    public function testGetScenesSuggest()
    {

    }

    public function testGetCategories()
    {

    }

    public function testGetScenes200(): void
    {
        $client = $this->createClient();
        $request = new GetScenesRequest();

        $response = $client->getScenes($request);

        self::assertNull($response->error);
        self::assertEquals(500, $response->count);

        self::assertCount($request->getLimit(), $response->scenes);

        self::assertContainsOnly(Scene::class, $response->scenes);
    }

    public function testGetScenes200Links(): void
    {
        $client = $this->createClient();
        $request = new GetScenesRequest();

        $response = $client->getScenes($request->setLinks(true));

        self::assertNull($response->error);
        self::assertEquals(500, $response->count);

        self::assertCount($request->getLimit(), $response->scenes);

        self::assertContainsOnly('string', $response->scenes);
    }

}
