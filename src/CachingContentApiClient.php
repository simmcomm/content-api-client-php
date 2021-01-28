<?php

namespace Flowly\Content;

use Flowly\Content\Request\GetScenesLandingRequest;
use Flowly\Content\Request\GetScenesRequest;
use Flowly\Content\Request\GetSceneSuggestRequest;
use Flowly\Content\Request\PostRatingRequest;
use Flowly\Content\Response\GetActorsResponse;
use Flowly\Content\Response\GetCategoriesResponse;
use Flowly\Content\Response\GetSceneResponse;
use Flowly\Content\Response\GetScenesLandingResponse;
use Flowly\Content\Response\GetScenesResponse;
use Flowly\Content\Response\PostRatingResponse;
use Symfony\Contracts\Cache\CacheInterface;

class CachingContentApiClient implements ContentApiClientInterface
{
    private ContentApiClientInterface $client;

    private CacheInterface $cache;

    public function __construct(ContentApiClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function getScenes(GetScenesRequest $request): GetScenesResponse
    {
        return $this->cache->get(self::cacheKey($request->toArray()), [$this->client, 'getScenes']);
    }

    public function getScene(string $id): GetSceneResponse
    {
        return $this->cache->get(self::cacheKey(['id' => $id]), [$this->client, 'getScene']);
    }

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse
    {
        return $this->cache->get(self::cacheKey($request->toArray()), [$this->client, 'getScenesSuggest']);
    }

    public function getCategories(): GetCategoriesResponse
    {
        return $this->cache->get(self::cacheKey(['function' => __FUNCTION__]), [$this->client, 'getCategories']);
    }

    public function getActors(): GetActorsResponse
    {
        return $this->cache->get(self::cacheKey(['function' => __FUNCTION__]), [$this->client, 'getCategories']);
    }

    public function submitRating(PostRatingRequest $request): PostRatingResponse
    {
        return $this->client->submitRating($request);
    }

    public function getScenesLanding(GetScenesLandingRequest $request): GetScenesLandingResponse
    {
        return $this->cache->get(self::cacheKey($request->toArray()), [$this->client, 'getScenesLanding']);
    }

    private static function cacheKey(array $args): string
    {
        ksort($args);

        return hash('md5', http_build_query($args));
    }
}
