<?php declare(strict_types=1);

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\CssPropertyAnimationCube;
use EugeneErg\Graph\Collections\CssPropertyAnimationMatrix;
use EugeneErg\Graph\Dto\CSS\CssPropertyAnimationDto;
use EugeneErg\Graph\Dto\Point2D;
/**
 * @var CssPropertyAnimationMatrix $animations
 * @var Point2D[][] $keyFrames
 * @var int $vertexRadius
 * @var int $graphRadius
 * @var CssPropertyAnimationCube $connections
 */
?>
<svg version="1.1"
     width="<?= $graphRadius * 2 + $vertexRadius ?>"
     height="<?= $graphRadius * 2 + $vertexRadius ?>"
     stroke="black"
     stroke-width="1"
     vector-effect="non-scaling-stroke"
     fill="white"
     viewBox="
        -<?= $graphRadius + $vertexRadius ?>
        -<?= $graphRadius + $vertexRadius ?>
        <?= ($graphRadius + $vertexRadius) * 2 ?>
        <?= ($graphRadius + $vertexRadius) * 2 ?>"
     xmlns="http://www.w3.org/2000/svg">
    <style>
        <?php foreach ($keyFrames as $keyFrame => $steps): ?>
            @keyFrames <?= $keyFrame ?> {
                <?php foreach ($steps as $percent => $point): ?>
                <?= $percent ?>% {
                    <?php if ($point instanceof Point2D): ?>
                    x: <?= $point->getX() ?>;
                    y: <?= $point->getY() ?>;
                    <?php else: ?>
                    fill: <?= $point ?>;
                    <?php endif ?>
                }
                <?php endforeach ?>
            }
        <?php endforeach ?>
        <?php foreach ($animations as $vertex => $animation): ?>
            .vertex<?= $vertex ?> {
                animation: <?= $animation->implode(',') ?>;
            }
        <?php endforeach ?>
    </style>
    <?php foreach ($animations as $vertex => $animation): ?>
        <symbol id="vertex<?= $vertex ?>"
                width="<?= $vertexRadius * 2 ?>"
                height="<?= $vertexRadius * 2 ?>"
                viewBox="0 0 <?= $vertexRadius * 2 ?> <?= $vertexRadius * 2 ?>">
            <circle
                vector-effect="non-scaling-stroke"
                cx="<?= $vertexRadius ?>"
                cy="<?= $vertexRadius ?>"
                r="<?= $vertexRadius ?>"/>
            <svg width="<?= $vertexRadius * 2 ?>"
                 height="<?= $vertexRadius * 2 ?>"
                 viewBox="-<?= strlen((string) $vertex) * 6 ?> -12 <?= strlen((string) $vertex) * 12 ?> 24">
                <text dominant-baseline="central"
                      text-anchor="middle"
                      font-size="20"
                      font-family="monospace"
                      vector-effect="non-scaling-stroke">
                    <?= $vertex ?>
                </text>
            </svg>
        </symbol>
    <?php endforeach ?>
    <?php foreach ($connections as $vertex1 => $connection): ?>
        <line class="<?= $vertex1 ?>">
        <?php foreach ($connection as $type => $animationsByTypes): ?>
        <?php /** @var CssPropertyAnimationDto $animation */
            foreach ($animationsByTypes as $animation): ?>
                <?php
                    $lines = Collection::fromArray($keyFrames[$animation->getKeyFrames()])
                        ->filter(fn ($value) => $value instanceof Point2D);

                    if ($lines->isEmpty()) {
                        continue;
                    }
                ?>
                <?php if ($lines->count() === 1): ?>
                    <animate attributeName="<?= ['from' => 'x1', 'to' => 'x2'][$type] ?>"
                             to="<?= $lines->map(fn ($keyFrame) => $keyFrame->getX())->implode(';') ?>"
                             dur="<?= $animation->getDuration() ?>s" begin="<?= $animation->getDelay() ?>s" fill="freeze"
                    ></animate>
                    <animate attributeName="<?= ['from' => 'y1', 'to' => 'y2'][$type] ?>"
                             to="<?= $lines->map(fn ($keyFrame) => $keyFrame->getY())->implode(';') ?>"
                             dur="<?= $animation->getDuration() ?>s" begin="<?= $animation->getDelay() ?>s" fill="freeze"
                    ></animate>
                <?php else: ?>
                    <animate attributeName="<?= ['from' => 'x1', 'to' => 'x2'][$type] ?>"
                             values="<?= $lines->map(fn ($keyFrame) => $keyFrame->getX())->implode(';') ?>"
                             keyTimes="<?= $lines->keys()->map(fn ($value) => round($value / 100, 2))->implode(';') ?>"
                             dur="<?= $animation->getDuration() ?>s" begin="<?= $animation->getDelay() ?>s" fill="freeze"
                    ></animate>
                    <animate attributeName="<?= ['from' => 'y1', 'to' => 'y2'][$type] ?>"
                             values="<?= $lines->map(fn ($keyFrame) => $keyFrame->getY())->implode(';') ?>"
                             keyTimes="<?= $lines->keys()->map(fn ($value) => round($value / 100, 2))->implode(';') ?>"
                             dur="<?= $animation->getDuration() ?>s" begin="<?= $animation->getDelay() ?>s" fill="freeze"
                    ></animate>
                <?PHP endif ?>
            <?php endforeach ?>
        <?php endforeach ?>
        </line>
    <?php endforeach ?>
    <?php foreach ($animations as $vertex => $animation): ?>
        <use class="vertex<?= $vertex ?>"
             href="#vertex<?= $vertex ?>"
             style="transform: translate(-<?= $vertexRadius ?>px, -<?= $vertexRadius ?>px)">
        </use>
    <?php endforeach ?>
</svg>