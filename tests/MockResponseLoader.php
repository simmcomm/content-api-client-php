<?php

namespace Flowly\Content\ApiClientTest;

use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class MockResponseLoader
{
    private const MAP = [
        'https://api-content.flowly.com/scenes?offset=0&limit=25&rating=%3E%3D1.0%20%3C%3D10.0&orderBy=added&orderDir=desc&links=0&videoResolution=1080&imageResolution=1080' => 'getScenes200.json',
        'https://api-content.flowly.com/scenes?offset=0&limit=25&rating=%3E%3D1.0%20%3C%3D10.0&orderBy=added&orderDir=desc&links=1&videoResolution=1080&imageResolution=1080' => 'getScenes200Links.json',
        'https://api-content.flowly.com/scenes/9fe133b0-daf3-49ab-a1db-dec10d038458?imageResolution=1080&videoResolution=1080' => 'getScene_9fe133b0-daf3-49ab-a1db-dec10d038458.json',
        'https://api-content.flowly.com/actors' => 'getActors.json',
        'https://api-content.flowly.com/categories' => 'getCategories.json',
        'https://api-content.flowly.com/scenes/landing?blockSize=25&orderBy=added&orderDir=desc&links=0&videoResolution=1080&imageResolution=1080' => 'getLanding.json'

    ];

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        if (!array_key_exists($url, self::MAP)) {
            throw new RuntimeException(sprintf('Mock response is not defined for url "%s"', $url));
        }

        return new MockResponse(file_get_contents(sprintf("%s/Resource/%s", __DIR__, self::MAP[$url])));
    }
}
