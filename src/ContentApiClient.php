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
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use const E_USER_DEPRECATED;

class ContentApiClient implements ContentApiClientInterface
{
    use LoggerAwareTrait;

    private const ENDPOINT = 'https://api-content.flowly.com';
    protected ?string $authAlias = null;
    private HttpClientInterface $http;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private string $access;
    private string $secret;
    private bool $dev;
    private string $portalIdentification;
    private ResponseAuthAliasPostProcessor $authAliasPostProcessor;
    private bool $authAliasPostProcessingEnabled = true;

    public function __construct(string $access, string $secret, ?string $portalIdentification = null, ?HttpClientInterface $http = null, bool $dev = false)
    {
        $this->http = $http ?? HttpClient::create();
        $this->access = $access;
        $this->secret = $secret;
        $this->dev = $dev;
        $this->setLogger(new NullLogger());

        $this->serializer = self::createSerializer();
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        if ($portalIdentification === null) {
            @trigger_error('Argument $portalIdentification will be required in next version, value must be set.', E_USER_DEPRECATED);
        }

        $this->portalIdentification ??= $portalIdentification
            ?? $_SERVER['HTTP_HOST']
            ?? $_SERVER['SERVER_NAME']
            ?? sprintf(
                '%s@%s[%s]',
                $_SERVER['USER'],
                gethostname(),
                $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_NAME']
            );

        $this->authAliasPostProcessor = new ResponseAuthAliasPostProcessor();
    }

    public function setAuthAliasPostProcessingEnabled(bool $authAliasPostProcessingEnabled): ContentApiClient
    {
        $this->authAliasPostProcessingEnabled = $authAliasPostProcessingEnabled;

        return $this;
    }

    public function getScenes(GetScenesRequest $request): GetScenesResponse
    {
        if ($exception = $this->validateRequest($request)) {
            throw $exception;
        }

        $uri = $this->getUri('/scenes');

        return $this->getSceneCommon($request, $uri, GetScenesResponse::class);
    }

    public function getScene(GetSceneRequest $request): GetSceneResponse
    {
        if ($exception = $this->validateRequest($request)) {
            throw $exception;
        }

        $uri = $this->getUri("/scenes/{$request->getId()}");

        return $this->getSceneCommon($request, $uri, GetSceneResponse::class);
    }

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse
    {
        if ($exception = $this->validateRequest($request)) {
            throw $exception;
        }

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
        if ($exception = $this->validateRequest($request)) {
            throw $exception;
        }

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
        if ($exception = $this->validateRequest($request)) {
            throw $exception;
        }

        $uri = $this->getUri('/scenes/landing');

        return $this->getSceneCommon($request, $uri, GetScenesLandingResponse::class);
    }

    public function setAuthAlias(string $authAlias): ContentApiClientInterface
    {
        $this->authAlias = $authAlias;

        return $this;
    }

    public function getAuthAlias(): ?string
    {
        return $this->authAlias;
    }

    private static function createSerializer(): Serializer
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfoExtractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor],
            [$reflectionExtractor],
        );

        $objectNormalizer = new ObjectNormalizer(null, null, $propertyAccessor, $propertyInfoExtractor);

        return new Serializer([new SceneLinkDenormalizer(), $objectNormalizer, new ArrayDenormalizer()], [new JsonEncoder()]);
    }

    private function validateRequest(object $request): ?UnexpectedValueException
    {
        /** @var ConstraintViolationInterface $violation */
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->validator->validate($request) as $violation) {
            return new UnexpectedValueException($violation->getMessage());
        }

        return null;
    }

    private function getUri(string $path): string
    {
        $endpoint = self::ENDPOINT;
        if ($this->dev) {
            $endpoint = str_replace('api', 'api-staging', self::ENDPOINT);
        }

        return "$endpoint$path";
    }

    /**
     * @param GetScenesRequest|GetSceneRequest|GetSceneSuggestRequest|GetScenesLandingRequest $request
     * @param string                                                                          $uri
     * @param string                                                                          $responseType
     *
     * @return GetScenesResponse|GetSceneResponse|GetScenesLandingResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @noinspection PhpDocSignatureInspection
     */
    private function getSceneCommon(object $request, string $uri, string $responseType): object
    {
        $query = $request->toArray();

        $benchmark = microtime(true);
        $content = $this->http->request('GET', $uri, $this->getClientOptions(['query' => $query]))
                              ->getContent(false);
        $this->logger->info(
            sprintf('ContentApiClient: GET %s', $uri),
            ['benchmark' => sprintf('%.3f', microtime(true) - $benchmark), 'query' => $query]
        );

        $response = $this->serializer->deserialize($content, $responseType, 'json');

        if ($this->authAliasPostProcessingEnabled) {
            $this->authAliasPostProcessor->process($response, $this->authAlias);
        }

        return $response;
    }

    private function getClientOptions(array $additionalOptions = []): array
    {
        if (empty($this->authAlias)) {
            throw new UnexpectedValueException('authAlias must be provided before sending any request (was $client->setAuthAlias() called?).');
        }

        return array_merge(
            [
                'auth_basic' => $this->access . $this->secret,
                'headers' => [
                    'User-Agent' => sprintf('ContentApiClient(%s)', gethostname()),
                    'Accept' => 'application/json',
                    'X-Portal-Id' => $this->portalIdentification,
                ],
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
