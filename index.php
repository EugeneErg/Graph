<?php

declare(strict_types = 1);

use EugeneErg\Graph\New\Animations\Animators\SvgAnimator;
use EugeneErg\Graph\New\Processes\GraphSvgAnimationProcess;
use EugeneErg\Graph\New\Services\ArticulationVertexesFinderService;
use EugeneErg\Graph\New\Services\CanvasService;
use EugeneErg\Graph\New\Services\EventService;
use EugeneErg\Graph\New\Services\GraphService;
use EugeneErg\Graph\New\Services\TreeService;

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
$eventService = new EventService();
$canvasService = new CanvasService();
$process = new GraphSvgAnimationProcess(
    $eventService,
    new TreeService(
        new GraphService($canvasService, $eventService),
        new ArticulationVertexesFinderService(),
        $eventService,
        $canvasService,
    ),
);

file_put_contents('test.svg', (new SvgAnimator())->generateContent($process->generate($graph)));
