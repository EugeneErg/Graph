<?php declare(strict_types=1);
namespace EugeneErg\Tests;

use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Services\TopologyService;
use PHPUnit\Framework\TestCase;

class TopologyServiceTest extends TestCase
{
    /** @dataProvider createTopologyData */
    public function testCreateTopology(array $edges, array $expectedOuter, array $expectedArcs): void
    {
        $actual = TopologyService::instance()->createTopology(Helper::instance()->createEdgeCollection($edges));
        $this->assertEquals($expectedOuter, $actual->outer->vertexes->toArrayRecursive());

        //var_dump($actual->arcs);die;
        $this->assertEquals($expectedArcs, $actual->arcs->toArrayRecursive());
    }

    public function createTopologyData(): array
    {
        return [
            [
                [
                    [2,0,3],
                    [2,1,0,3],
                    [1,2,0],
                ],
                [2,0,3],
                [
                    [
                        'vertexes' => [
                            [0,1,2]
                        ],
                        'gravity' => [3],
                    ]
                ],
            ],
            /*[
                [
                    [],
                    [],
                    [],
                ],
                [],
                [
                    [],
                    [],
                    [],
                ],
            ],*/
        ];
    }
}
