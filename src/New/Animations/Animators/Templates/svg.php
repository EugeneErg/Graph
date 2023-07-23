<?php

declare(strict_types=1);

use EugeneErg\Collections\FloatCollection;
use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;
use EugeneErg\Graph\New\Animations\Tracks\AbstractTrack;
use EugeneErg\Graph\New\Collections\Point2DCollection;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;

$printAnimation = function (AbstractTrack $track, string $attributeName, ?callable $getValues = null) {
    if ($track->getDurationMilliSecond() > 0): ?>
        <animate
            attributeName="<?= $attributeName ?>"
            dur="<?= $track->getDurationMilliSecond() ?>ms"
            fill="freeze"
            values="<?= implode(';', $getValues === null ? $track->getValues()->toArray() : $getValues($track->getValues())->toArray()) ?>"
            keyTimes="<?= implode(';', FloatCollection::fromMap(
                fn (int $milliseconds): float => $milliseconds / $track->getDurationMilliSecond(),
                $track->getEnds())->toArray(),
            ) ?>"
        />
    <?php endif;
};

/**
 * @var int $left
 * @var int $top
 * @var int $width
 * @var int $height
 * @var DataTransferObjectCollection $objects
 */
?>
<svg version="1.1"
     width="<?= $width ?>"
     height="<?= $height ?>"
     stroke="black"
     stroke-width="1"
     vector-effect="non-scaling-stroke"
     fill="white"
     viewBox="<?= $left ?> <?= $top ?> <?= $width ?> <?= $height ?>"
     xmlns="http://www.w3.org/2000/svg">
    <?php foreach ($objects as $number => $object): ?>
        <?php if ($object instanceof Circle): ?>
            <circle
                vector-effect="non-scaling-stroke"
                cx="<?= $object->center->defaultValue->x ?>"
                cy="<?= $object->center->defaultValue->y ?>"
                r="<?= $object->radius->defaultValue ?>"
                fill="<?= $object->color->defaultValue ?>"
                stroke="#000"
            >
                <?= $printAnimation($object->radius, 'r') ?>
                <?= $printAnimation(
                    $object->center,
                    'cx',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->x, $points),
                ) ?>
                <?= $printAnimation(
                    $object->center,
                    'cy',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->y, $points),
                ) ?>
                <?= $printAnimation($object->color, 'fill') ?>
            </circle>
        <?php elseif ($object instanceof Line): ?>
            <line
                vector-effect="non-scaling-stroke"
                x1="<?= $object->from->defaultValue->x ?>"
                y1="<?= $object->from->defaultValue->y ?>"
                x2="<?= $object->to->defaultValue->x ?>"
                y2="<?= $object->to->defaultValue->y ?>"
                stroke="<?= $object->color->defaultValue ?>"
            >
                <?= $printAnimation($object->color, 'stroke') ?>
                <?= $printAnimation(
                    $object->from,
                    'x1',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->x, $points),
                ) ?>
                <?= $printAnimation(
                    $object->from,
                    'y1',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->y, $points),
                ) ?>
                <?= $printAnimation(
                    $object->to,
                    'x2',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->x, $points),
                ) ?>
                <?= $printAnimation(
                    $object->to,
                    'y2',
                    fn (Point2DCollection $points): IntegerCollection
                        => IntegerCollection::fromMap(fn (Point2D $point): float => $point->y, $points),
                ) ?>
            </line>
        <?php else: ?>
            <?= get_class($object) ?>
        <?php endif ?>
    <?php endforeach ?>
</svg>