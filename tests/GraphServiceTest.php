<?php declare(strict_types=1);
namespace EugeneErg\tests;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\GraphService;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graphs\Edge;
use PHPUnit\Framework\TestCase;

class GraphServiceTest extends TestCase
{
    /** @dataProvider splitGraphOnDisconnectedData */
    public function testSplitGraphOnDisconnected(array $graph, array $expectedConnections, array $expectedVertexes): void
    {
        $graphs = GraphService::instance()->splitGraphOnDisconnected($this->arrayToClearGraph($graph));
        $resultConnections = array_map(function (ClearGraph $graph): array {
            return $graph->connections;
        }, $graphs->toArray());
        $resultVertexes = array_map(function (ClearGraph $graph): array {
            return $graph->vertexes;
        }, $graphs->toArray());
        //var_dump('$expected', $expected, '$result', $result);
        $this->assertEquals($expectedConnections, $resultConnections);
        $this->assertEquals($expectedVertexes, $resultVertexes);
        //$this->assertEquals([], array_diff($result, $expectedConnections));
    }

    public function q(): void
    {

    }

    /** @dataProvider getEdgesData */
    public function testGetEdges(array $graph, array $expected, bool $hasError): void
    {
        if ($hasError) {
            $this->expectException(\Exception::class);
        }

        $edges = GraphService::instance()->getEdges(new ClearGraph($graph));
        $result = array_map(function (Edge $edge): array {
            return $edge->toArray();
        }, $edges->toArray());
        $this->assertEquals(array_diff($expected, $result), array_diff($result, $expected));
    }

    public function splitGraphOnDisconnectedData(): array
    {
        return [
            'single' => [
                [
                    [0,1,1],
                    [1,0,1],
                    [1,1,0],
                ],
                [
                    [
                        0 => [1 => 1, 2 => 1],
                        1 => [0 => 1, 2 => 1],
                        2 => [0 => 1, 1 => 1],
                    ],
                ],
                [
                    [0,1,2],
                ],
            ],
            [
                [
                    [0,1,0,0],
                    [1,0,0,0],
                    [0,0,0,1],
                    [0,0,1,0],
                ],
                [
                    [
                        0 => [1 => 1],
                        1 => [0 => 1],
                    ],
                    [
                        2 => [3 => 1],
                        3 => [2 => 1],
                    ],
                ],
                [
                    [0,1],
                    [2,3],
                ],
            ],
            [
                [
                    [0,1,0],
                    [1,0,0],
                    [0,0,0],
                ],
                [
                    [
                        0 => [1 => 1],
                        1 => [0 => 1],
                    ],
                    [
                    ],
                ],
                [
                    [0,1],
                    [2],
                ],
            ],
        ];
    }

    public function getEdgesData(): array
    {
        return [
            '' => [
                [
                    [0,1,1],
                    [1,0,1],
                    [1,1,0],
                ],
                [
                    [1,2,3],
                ],
                false,
            ],
        ];
    }

    private function arrayToClearGraph(array $graph): ClearGraph
    {
        $connections = IntegerMatrix::map(function (array $integers): IntegerCollection {
            return new IntegerCollection((new Collection($integers))->filter(function(int $value): bool {
                return $value !== 0;
            })->toArray());
        }, new Collection($graph));

        return new ClearGraph($connections);
    }
}
