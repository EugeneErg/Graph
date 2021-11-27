<?php
/**
 * @var float[][] $coordinates
 * @var int $radius
 * @var int $diameter
 * @var int $graphRadius
 * @var EugeneErg\Graph\ValueObjects\Graph $graph
 * @var string $caption
 */
?>
<h3><?= $caption ?></h3>
<svg version="1.1"
     width="800"
     height="800"
     stroke="black"
     stroke-width="1"
     vector-effect="non-scaling-stroke"
     fill="white"
     viewBox="
        <?= - $graphRadius - $radius - 1 ?>
        <?= - $graphRadius - $radius - 1 ?>
        <?= ($graphRadius + $radius + 1) * 2 ?>
        <?= ($graphRadius + $radius + 1) * 2 ?>"
     xmlns="http://www.w3.org/2000/svg">
    <!--marker id="MarkerArrow"
            viewBox="0 0 30 30"
            refX="35"
            refY="10"
            markerUnits="userSpaceOnUse"
            orient="auto"
            markerWidth="20"
            markerHeight="20">
        <polyline id="markerPoly1" points="0,0 20,10 0,20 2,10" fill="crimson"/>
    </marker-->
<?php foreach ($graph as $vertexA => $connections):
          if (!isset($coordinates[$vertexA])) {
              continue;
          }

          foreach ($connections as $vertexB => $value):
              if (!isset($coordinates[$vertexB])) {
                  continue;
              } ?>
        <line
            x1="<?= $coordinates[$vertexA]->getX() ?>"
            x2="<?= $coordinates[$vertexB]->getX() ?>"
            y1="<?= $coordinates[$vertexA]->getY() ?>"
            y2="<?= $coordinates[$vertexB]->getY() ?>"
            style="marker-end: url(#MarkerArrow)"
            vector-effect="non-scaling-stroke"
            stroke="<?php switch ($value): case 1: ?>rgba(0,0,0,.5)
                <?php break;case 2: ?>rgba(0,0,255,.5)
                <?php break;case 3: ?>rgba(255,0,0,.5)
                    <?php endswitch ?>"
        />
    <?php endforeach;
      endforeach;
      foreach ($coordinates as $vertex => $coordinate): ?>
    <circle
        cx="<?= $coordinate->getX() ?>"
        cy="<?= $coordinate->getY() ?>"
        vector-effect="non-scaling-stroke"
        r="<?= $radius ?>"
    />
    <svg width="<?= $radius * 2 ?>"
         height="<?= $radius * 2 ?>"
         x="<?= $coordinate->getX() - $radius ?>"
         y="<?= $coordinate->getY() - $radius ?>">
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="<?= $radius ?>" vector-effect="non-scaling-stroke">
            <?= $vertex ?>
        </text>
    </svg>
<?php endforeach; ?>
</svg>