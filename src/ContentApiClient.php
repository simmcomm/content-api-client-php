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
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ContentApiClient implements ContentApiClientInterface
{
    use LoggerAwareTrait;

    private const ENDPOINT = 'https://api.content-dn.com';

    private HttpClientInterface $http;

    private SerializerInterface $serializer;

    private ValidatorInterface $validator;

    private string $access;

    private string $secret;

    private bool $dev;

    public function __construct(
        HttpClientInterface $http,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        string $access,
        string $secret,
        bool $dev = false
    ) {
        $this->http = $http;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->access = $access;
        $this->secret = $secret;
        $this->dev = $dev;
        $this->setLogger(new NullLogger());
    }

    public static function create(string $access, string $secret): ContentApiClientInterface
    {
        $client = HttpClient::create();
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $validator = Validation::createValidator();

        return new ContentApiClient($client, $serializer, $validator, $access, $secret);
    }

    public function getScenes(GetScenesRequest $request): GetScenesResponse
    {
        $this->validator->validate($request);
        $uri = $this->getUri('/scenes');

        return $this->getSceneCommon($request, $uri, GetScenesResponse::class);
    }

    public function getScene(GetSceneRequest $request): GetSceneResponse
    {
        $this->validator->validate($request);
        $uri = $this->getUri("/scenes/{$request->getId()}");

        return $this->getSceneCommon($request, $uri, GetSceneResponse::class);
    }

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse
    {
        $this->validator->validate($request);
        $uri = $this->getUri("/scenes/{$request->getId()}/suggest");

        return $this->getSceneCommon($request, $uri, GetScenesResponse::class);
    }

    public function getCategories(): GetCategoriesResponse
    {
        return $this->getMetadata('/categories', GetCategoriesResponse::class);
    }

    public function getActors(): GetActorsResponse
    {
        return $this->getMetadata('/actors', GetActorsResponse::class);
    }

    public function submitRating(PostRatingRequest $request): PostRatingResponse
    {
        $this->validator->validate($request);

        $uri = $this->getUri("/rating/{$request->getType()}/{$request->getUserId()}/{$request->getContentId()}");

        $benchmark = microtime(true);
        $content = $this->http->request('POST', $uri, $this->getClientOptions(['body' => (string) $request->getRating()]))
                              ->getContent(false);

        $this->logger->info(
            sprintf('ContentApiClient: POST %s', $uri),
            ['benchmark' => sprintf('%.3f', microtime(true) - $benchmark)]
        );

        return $this->serializer->deserialize($content, PostRatingResponse::class, 'json');
    }

    public function getScenesLanding(GetScenesLandingRequest $request): GetScenesLandingResponse
    {
        $this->validator->validate($request);

        $uri = $this->getUri('/scenes/landing');

        return $this->getSceneCommon($request, $uri, GetScenesLandingResponse::class);
    }

    private function getUri(string $path): string
    {
        $endpoint = self::ENDPOINT;
        if ($this->dev) {
            $endpoint = str_replace('api', 'api-staging', self::ENDPOINT);
        }

        return "$endpoint$path";
    }

    private function getSceneCommon(object $request, string $uri, string $responseType)
    {
        if (
            !$request instanceof GetScenesRequest
            || !$request instanceof GetSceneRequest
            || !$request instanceof GetSceneSuggestRequest
            || !$request instanceof GetScenesLandingRequest
        ) {
            throw new InvalidArgumentException(sprintf('Unexpected type %s', get_class($request)));
        }
        $query = $request->toArray();

        $benchmark = microtime(true);
        $content = $this->http->request('GET', $uri, $this->getClientOptions(['query' => $query]))
                              ->getContent(false);
        $this->logger->info(
            sprintf('ContentApiClient: GET %s', $uri),
            ['benchmark' => sprintf('%.3f', microtime(true) - $benchmark), 'query' => $query]
        );

        return $this->serializer->deserialize($content, $responseType, 'json');
    }

    private function getClientOptions(array $additionalOptions = []): array
    {
        return array_merge(
            [
                'auth_basic' => $this->access . $this->secret,
                'headers'    => ['User-Agent' => sprintf('ContentApiClient(%s)', gethostname()), 'Accept' => 'application/json'],
            ],
            $additionalOptions
        );
    }

    private function getMetadata(string $path, string $type)
    {
        $uri = $this->getUri($path);

        $benchmark = microtime(true);
        $content = $this->http->request('GET', $uri, $this->getClientOptions())
                              ->getContent(false);

        $this->logger->info(
            sprintf('ContentApiClient: GET %s', $uri),
            ['benchmark' => sprintf('%.3f', microtime(true) - $benchmark)]
        );

        return $this->serializer->deserialize($content, $type, 'json');
    }
}
