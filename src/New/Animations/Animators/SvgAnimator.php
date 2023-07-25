<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Animators;

use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;
use EugeneErg\Graph\New\Animations\Segments\Point2DSegment;
use EugeneErg\Graph\New\Animations\Segments\RadiusSegment;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Animations\Tracks\RadiusTrack;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;

class SvgAnimator implements AnimatorInterface
{
    public function generateContent(DataTransferObjectCollection $objects): string
    {
        ob_start();
        $minPoint = null;
        $maxPoint = null;
        $maxRadius = 0;

        foreach ($objects as $object) {
            if ($object instanceof Circle) {
                [$minPoint, $maxPoint] = $this->getMinMaxPoints($object->center, $minPoint, $maxPoint);
                $maxRadius = $this->getMaxRadius($object->radius);
            } elseif ($object instanceof Line) {
                [$minPoint, $maxPoint] = $this->getMinMaxPoints($object->from, $minPoint, $maxPoint);
                [$minPoint, $maxPoint] = $this->getMinMaxPoints($object->to, $minPoint, $maxPoint);
            }
        }

        $minPoint = new Point2D($minPoint->x - $maxRadius, $minPoint->y - $maxRadius);
        $maxPoint = new Point2D($maxPoint->x + $maxRadius, $maxPoint->y + $maxRadius);
        $this->echoTemplate(__DIR__ . '/Templates/svg.php', [
            'objects' => $objects,
            'width' => $maxPoint->x - $minPoint->x,
            'height' => $maxPoint->y - $minPoint->y,
            'top' => $minPoint->y,
            'left' => $minPoint->x,
        ]);

        return ob_get_clean();
    }

    private function echoTemplate(string $template, array $variables = []): void
    {
        extract($variables);

        require $template;
    }

    private function getMinMaxPoints(Point2DTrack $track, ?Point2D $minPoint = null, ?Point2D $maxPoint = null): array
    {
        $minPoint = $this->getMinPoint($track->defaultValue, $minPoint);
        $maxPoint = $this->getMaxPoint($track->defaultValue, $maxPoint);

        /** @var Point2DSegment $segment */
        foreach ($track->getSegments() as $segment) {
            $minPoint = $this->getMinPoint($segment->getValue(), $minPoint);
            $maxPoint = $this->getMaxPoint($segment->getValue(), $maxPoint);
        }

        return [$minPoint, $maxPoint];
    }

    private function getMaxPoint(Point2D $pointA, ?Point2D $pointB = null): Point2D
    {
        return new Point2D(max($pointA->x, $pointB?->x), max($pointA->y, $pointB?->y));
    }

    private function getMinPoint(Point2D $pointA, ?Point2D $pointB = null): Point2D
    {
        return new Point2D(min($pointA->x, $pointB?->x), min($pointA->y, $pointB?->y));
    }

    private function getMaxRadius(RadiusTrack $radius): int
    {
        return $radius->getSegments()->reduce(
            fn (int $result, RadiusSegment $segment): int => max($segment->getValue(), $result),
            $radius->defaultValue,
        );
    }
}
