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
                $track->getTimes())->toArray(),
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
<svg width="<?= $width ?>"
     height="<?= $height ?>"
     stroke="black"
     stroke-width="1"
     fill="white"
     viewBox="<?= $left ?> <?= $top ?> <?= $width ?> <?= $height ?>"
     xmlns="http://www.w3.org/2000/svg">
    <?php foreach ($objects as $number => $object): ?>
        <?php if ($object instanceof Circle): ?>
            <pattern id="id-<?= $number ?>" width="100%" height="100%" viewBox="0, 0, 10, 10">
                <rect stroke="none" x="0" y="0" width="10" height="10" fill="<?= $object->color->getLastValue() ?>">
                    <?= $printAnimation($object->color, 'fill') ?>
                </rect>
                <text
                    stroke="none"
                    fill="#000"
                    dominant-baseline="central"
                    text-anchor="middle"
                    x="5"
                    y="5"
                    font-size="4"
                    font-family="monospace"
                    vector-effect="non-scaling-stroke"
                >
                    <?= $object->text ?>
                </text>
            </pattern>
            <circle
                vector-effect="non-scaling-stroke"
                cx="<?= $object->center->getLastValue()->x ?>"
                cy="<?= $object->center->getLastValue()->y ?>"
                r="<?= $object->radius->getLastValue() ?>"
                fill="url(#id-<?= $number ?>)"
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
            </circle>
        <?php elseif ($object instanceof Line): ?>
            <line
                vector-effect="non-scaling-stroke"
                x1="<?= $object->from->getLastValue()->x ?>"
                y1="<?= $object->from->getLastValue()->y ?>"
                x2="<?= $object->to->getLastValue()->x ?>"
                y2="<?= $object->to->getLastValue()->y ?>"
                stroke="<?= $object->color->getLastValue() ?>"
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