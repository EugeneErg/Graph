<?php declare(strict_types = 1);
namespace EugeneErg\Graphs;

class Arc
{
    private $vertexes;

    public function __construct(array $vertexes)
    {
        $this->vertexes = $vertexes;
    }
}