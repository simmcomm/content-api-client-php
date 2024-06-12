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
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CachingContentApiClient implements ContentApiClientInterface
{
    protected ?string                 $authAlias = null;
    private ContentApiClientInterface $client;
    private CacheInterface            $cache;
    private ResponseAuthAliasPostProcessor $authAliasPostProcessor;

    public function __construct(ContentApiClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
        if ($client instanceof ContentApiClient) {
            $client->setAuthAliasPostProcessingEnabled(false);
            $this->authAlias = $client->getAuthAlias();
        }
        $this->authAliasPostProcessor = new ResponseAuthAliasPostProcessor();
    }

    private static function cacheKey(array $args): string
    {
        ksort($args);

        return hash('md5', http_build_query($args));
    }

    private static function wrapCacheCallback(callable $callback): callable
    {
        return static function (CacheItemInterface $item, bool &$save) use ($callback) {
            $result = $callback();
            if (is_object($result) && property_exists($result, 'error')) {
                $save = $result->error === null;
            }

            return $result;
        };
    }

    public function getScenes(GetScenesRequest $request): GetScenesResponse
    {
        return $this->authAliasPostProcessor->process(
            $this->cache->get(
                self::cacheKey($request->toArray()),
                self::wrapCacheCallback(fn() => $this->client->getScenes($request)),
            ),
            $this->authAlias,
        );
    }

    public function getScene(GetSceneRequest $request): GetSceneResponse
    {
        return $this->authAliasPostProcessor->process(
            $this->cache->get(
                self::cacheKey(array_merge(['id' => $request->getId(), $request->toArray()])),
                self::wrapCacheCallback(fn() => $this->client->getScene($request)),
            ),
            $this->authAlias,
        );
    }

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse
    {
        return $this->authAliasPostProcessor->process(
            $this->cache->get(
                self::cacheKey(array_merge(['id' => $request->getId(), $request->toArray()])),
                self::wrapCacheCallback(fn() => $this->client->getScenesSuggest($request)),
            ),
            $this->authAlias,
        );
    }

    public function getCategories(): GetCategoriesResponse
    {
        return $this->cache->get(
            self::cacheKey(['function' => __FUNCTION__]),
            self::wrapCacheCallback(fn() => $this->client->getCategories()),
        );
    }

    public function getActors(): GetActorsResponse
    {
        return $this->cache->get(
            self::cacheKey(['function' => __FUNCTION__]),
            self::wrapCacheCallback(fn() => $this->client->getActors()),
        );
    }

    public function submitRating(PostRatingRequest $request): PostRatingResponse
    {
        return $this->client->submitRating($request);
    }

    public function getScenesLanding(GetScenesLandingRequest $request): GetScenesLandingResponse
    {
        return $this->authAliasPostProcessor->process(
            $this->cache->get(
                self::cacheKey($request->toArray()),
                self::wrapCacheCallback(fn() => $this->client->getScenesLanding($request)),
            ),
            $this->authAlias,
        );
    }

    public function setAuthAlias(string $authAlias): ContentApiClientInterface
    {
        $this->authAlias = $authAlias;
        $this->client->setAuthAlias($authAlias);

        return $this;
    }
}
