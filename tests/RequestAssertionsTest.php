<?php


namespace Flowly\Content\ApiClientTest;


use Flowly\Content\Request\GetSceneRequest;
use Flowly\Content\Request\GetScenesLandingRequest;
use Flowly\Content\Request\GetScenesRequest;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \Flowly\Content\ContentApiClient
 */
class RequestAssertionsTest extends TestCase
{
    use ClientFactoryTrait;

    public function testGetScenesInvalidRequest(): void
    {
        $client = $this->createClient();
        $request = new GetScenesRequest();
        $request->setLimit(-1);


        $this->expectException(UnexpectedValueException::class);
        $client->getScenes($request);
    }

    public function testGetSceneInvalidRequest()
    {
        $client = $this->createClient();
        $request = new GetSceneRequest('9fe133b0-daf3-49ab-a1db-dec10d038458');

        $request->setImageResolution(666);
        $this->expectException(UnexpectedValueException::class);
        $client->getScene($request);
    }

    public function testGetSceneLandingInvalidRequest()
    {
        $client = $this->createClient();
        $request = new GetScenesLandingRequest();

        $request->setBlockSize(-1);
        $this->expectException(UnexpectedValueException::class);
        $client->getScenesLanding($request);

    }
}