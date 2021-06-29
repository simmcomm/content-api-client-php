<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\ContentApiClientInterface;
use Flowly\Content\Request\GetSceneRequest;
use Flowly\Content\Request\GetScenesLandingRequest;
use Flowly\Content\Request\GetScenesRequest;
use Flowly\Content\Response\Block;
use Flowly\Content\Response\Descriptor;
use Flowly\Content\Response\Scene;
use PHPUnit\Framework\TestCase;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 * @covers \Flowly\Content\ContentApiClient
 * @covers \Flowly\Content\SceneLinkDenormalizer
 */
class ContentApiClientTest extends TestCase
{
    use ClientFactoryTrait;

    public function test__construct(): void
    {
        self::assertInstanceOf(ContentApiClientInterface::class, $this->createClient());
    }

    public function testGetScenesLanding()
    {
        $client = $this->createClient();
        $request = new GetScenesLandingRequest();

        $response = $client->getScenesLanding($request);

        self::assertNull($response->error);
        self::assertContainsOnly(Block::class, $response->blocks);

        self::assertStringStartsWith('https://content-dn.com/proxy/TESTING', $response->blocks[0]->scenes[0]->videos->full[0]['uri']);
    }

    public function testGetScene()
    {
        $client = $this->createClient();
        $request = new GetSceneRequest('9fe133b0-daf3-49ab-a1db-dec10d038458');

        $response = $client->getScene($request);

        self::assertNull($response->error);

        self::assertInstanceOf(Scene::class, $response->scene);

        self::assertEquals('9fe133b0-daf3-49ab-a1db-dec10d038458', $response->scene->id);

        self::assertStringStartsWith('https://content-dn.com/proxy/TESTING', $response->scene->videos->full[0]['uri']);
    }

    public function testGetActors()
    {
        $client = $this->createClient();

        $response = $client->getActors();

        self::assertNull($response->error);
        self::assertContainsOnly(Descriptor::class, $response->actors);
    }

    public function testSubmitRating()
    {
        self::markTestSkipped('@TODO');
    }

    public function testGetScenesSuggest()
    {
        self::markTestSkipped('@TODO');
    }

    public function testGetCategories()
    {
        $client = $this->createClient();

        $response = $client->getCategories();

        self::assertNull($response->error);
        self::assertContainsOnly(Descriptor::class, $response->categories);
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

        self::assertStringStartsWith('https://content-dn.com/proxy/TESTING', $response->scenes[0]->videos->full[0]['uri']);
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
