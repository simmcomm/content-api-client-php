<?php

namespace Flowly\Content;

use Flowly\Content\Response\GetSceneResponse;
use Flowly\Content\Response\GetScenesLandingResponse;
use Flowly\Content\Response\GetScenesResponse;
use Flowly\Content\Response\Scene;

/**
 * @author   Ivan Pepelko <ivan.pepelko@gmail.com>
 * @internal Flowly\Content
 */
class ResponseAuthAliasPostProcessor
{
    /**
     * @template T
     *
     * @param T      $response
     * @param string $authAlias
     *
     * @return T
     */
    public function process(object $response, string $authAlias): object
    {
        if ($response instanceof GetScenesResponse) {
            $response->scenes = array_map(fn ($scene) => $this->modifyScene($scene, $authAlias), $response->scenes);
        }

        if ($response instanceof GetSceneResponse) {
            $response->scene = $this->modifyScene($response->scene, $authAlias);
        }

        if ($response instanceof GetScenesLandingResponse) {
            foreach ($response->blocks as $block) {
                $block->scenes = array_map(fn ($scene) => $this->modifyScene($scene, $authAlias), $block->scenes);
            }
        }

        return $response;
    }

    private function modifyScene($scene, string $authAlias)
    {
        if ($scene instanceof Scene) {
            $scene->videos->full = array_map(
                static function($video) use ($authAlias) {
                    $video['uri'] = str_replace('{{authalias}}', $authAlias, $video['uri']);

                    return $video;
                },
                $scene->videos->full
            );
        }

        return $scene;
    }
}
