<?php

declare(strict_types = 1);

error_reporting(E_ALL);

include 'vendor/autoload.php';

/*use EugeneErg\Graph\Processes\NewCreateSvgAnimationProcess;
use EugeneErg\Tests\Helper;

$graph = Helper::instance()->createClearGraph([
    [0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,],
    [0,0,0,0,0,0,0,1,0,1,1,0,0,0,1,1,0,],
    [0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,1,0,],
    [0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,],
    [0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,],
    [0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,],
    [0,0,0,0,0,0,0,0,1,0,0,1,0,0,0,0,0,],
    [0,1,0,0,0,1,0,0,0,0,0,0,0,0,0,1,0,],
    [0,0,0,0,0,0,1,0,0,0,0,0,1,0,0,0,0,],
    [0,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,],
    [0,1,0,1,0,0,0,0,0,1,0,0,0,0,0,1,1,],
    [0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,],
    [1,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,],
    [0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,],
    [0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,],
    [0,1,1,1,0,0,0,1,0,0,1,0,0,0,0,0,0,],
    [0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,],
]);

$svg = (new NewCreateSvgAnimationProcess($graph,null,20))->getSvgAnimation();*/


$graph = \EugeneErg\Graph\New\ValueObjects\Graph::createFromStrings(new \EugeneErg\Collections\StringCollection([
    '------------1----',
    '-------1-11---11-',
    '---1-----------1-',
    '--1-------1----1-',
    '-------------1---',
    '-------1---------',
    '--------1--1-----',
    '-1---1---------1-',
    '------1-----1----',
    '-1--------1------',
    '-1-1-----1-----11',
    '------1----------',
    '1-------1--------',
    '----1------------',
    '-1---------------',
    '-111---1--1------',
    '----------1------',
]));
$process = new \EugeneErg\Graph\New\Processes\GraphSvgAnimationProcess(
    new \EugeneErg\Graph\New\Services\EventService(),
    new \EugeneErg\Graph\New\Animations\Animators\SvgAnimator(),
);
$process->generate($graph);