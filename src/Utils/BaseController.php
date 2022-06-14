<?php

namespace App\Utils;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class BaseController extends AbstractController
{
    protected ?Serializer $serializer = null;

    public function serialize($entity, array $groups = []): array
    {
        if ($this->serializer === null) {
            // setup serializer
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizerDateTime = new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i']);

            $defaultContext = [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return null;
                    //return $object->getName();
                },
            ];
            $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
            $normalizerGetSet = new GetSetMethodNormalizer($classMetadataFactory, $metadataAwareNameConverter, null, null, null, $defaultContext);

            $this->serializer = new Serializer([$normalizerDateTime, $normalizerGetSet]);
        }

        return $this->serializer->normalize($entity, 'json', ['groups' => $groups]);
    }

}