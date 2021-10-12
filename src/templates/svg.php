<?php
/**
 * @var float[][] $coordinates
 * @var int $radius
 * @var int $diameter
 * @var int $graphRadius
 * @var string[] $captions
 * @var \EugeneErg\Graphs\AdjacencyMatrix $matrix
 */
?>
<svg version="1.1"
     baseProfile="full"
     width="800"
     height="800"
     stroke="black"
     fill="white"
     viewBox="
        <?= - $graphRadius - $radius - 1 ?>
        <?= - $graphRadius - $radius - 1 ?>
        <?= ($graphRadius + $radius + 1) * 2 ?>
        <?= ($graphRadius + $radius + 1) * 2 ?>"
     xmlns="http://www.w3.org/2000/svg">
<?php foreach ($matrix->toArray() as $vertexA => $connections):
          if (!isset($coordinates[$vertexA])) {
              continue;
          }

          foreach ($connections as $vertexB => $value):
              if (!isset($coordinates[$vertexB])) {
                  continue;
              } ?>
        <line
            x1="<?= $coordinates[$vertexA]['x'] ?>"
            x2="<?= $coordinates[$vertexB]['x'] ?>"
            y1="<?= $coordinates[$vertexA]['y'] ?>"
            y2="<?= $coordinates[$vertexB]['y'] ?>"
            stroke="<?php switch ($value): case 1: ?>black
                <?php break;case 2: ?>blue
                <?php break;case 3: ?>red
                    <?php endswitch ?>"
        />
    <?php endforeach;
      endforeach;
      foreach ($coordinates as $number => $coordinate): ?>
    <!--circle
        cx="<?= $coordinate['x'] ?>"
        cy="<?= $coordinate['y'] ?>"
        r="<?= $radius ?>"
    />
    <text
        x="<?= $coordinate['x'] - 4 ?>"
        y="<?= $coordinate['y'] + 5 ?>">
        <?= $captions[$number] ?>
    </text-->
<?php endforeach; ?>
</svg>