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
        '/scenes' => 'getScenes200.json',
    ];

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        ['path' => $path] = parse_url($url);

        if (!array_key_exists($path, self::MAP)) {
            throw new RuntimeException(sprintf('Mock response is not defined for path "%s"', $path));
        }

        return new MockResponse(file_get_contents(sprintf("%s/Resource/%s", __DIR__, self::MAP[$path])));
    }
}
