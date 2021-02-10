<?php

namespace Flowly\Content\ApiClientTest;

use Flowly\Content\ContentApiClient;
use Flowly\Content\ContentApiClientInterface;
use Flowly\Content\Request\GetScenesRequest;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 * @covers \Flowly\Content\ContentApiClient
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

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $validator = Validation::createValidator();

        return new ContentApiClient($httpClient, $serializer, $validator, '', '');
    }

    public function testCreate(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ContentApiClientInterface::class, ContentApiClient::create('', ''));
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
    }

    public function provideClient(): Generator
    {
        yield [$this->createClient()];
    }

}
