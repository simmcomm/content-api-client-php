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

interface ContentApiClientInterface
{
    public function getScenes(GetScenesRequest $request): GetScenesResponse;

    public function getScene(GetSceneRequest $request): GetSceneResponse;

    public function getScenesSuggest(GetSceneSuggestRequest $request): GetScenesResponse;

    public function getCategories(): GetCategoriesResponse;

    public function getActors(): GetActorsResponse;

    public function submitRating(PostRatingRequest $request): PostRatingResponse;

    public function getScenesLanding(GetScenesLandingRequest $request): GetScenesLandingResponse;
}
