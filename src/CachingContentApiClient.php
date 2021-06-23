<?php

namespace Flowly\Content;

use Flowly\Content\Request\GetSceneRequest;
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
        return $this->cache->get(self::cacheKey($request->toArray()), fn () => $this->client->getScenes($request));
    }

    public function getScene(GetSceneRequest $request): GetSceneResponse
    {
        return $this->cache->get(self::cacheKey([$request->getId(), ...$request->toArray()]), fn () => $this->client->getScene($request));
    }

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse
    {
        return $this->cache->get(self::cacheKey([$request->getId(), ...$request->toArray()]), fn () => $this->client->getScenesSuggest($request));
    }

    public function getCategories(): GetCategoriesResponse
    {
        return $this->cache->get(self::cacheKey(['function' => __FUNCTION__]), fn () => $this->client->getCategories());
    }

    public function getActors(): GetActorsResponse
    {
        return $this->cache->get(self::cacheKey(['function' => __FUNCTION__]), fn () => $this->client->getActors());
    }

    public function submitRating(PostRatingRequest $request): PostRatingResponse
    {
        return $this->client->submitRating($request);
    }

    public function getScenesLanding(GetScenesLandingRequest $request): GetScenesLandingResponse
    {
        return $this->cache->get(self::cacheKey($request->toArray()), fn () => $this->client->getScenesLanding($request));
    }

    public function setAuthAlias(string $authAlias): ContentApiClientInterface
    {
        $this->client->setAuthAlias($authAlias);

        return $this;
    }

    private static function cacheKey(array $args): string
    {
        ksort($args);

        return hash('md5', http_build_query($args));
    }
}
