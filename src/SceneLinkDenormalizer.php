<?php

namespace Flowly\Content;

use Flowly\Content\Response\Scene;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class SceneLinkDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $data;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_string($data) && $type === Scene::class;
    }
}
