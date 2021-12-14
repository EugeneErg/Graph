<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class Query
{
    private $results = [];

    public function __construct(?string $message = null)
    {

    }

    public function instanceOf(Argument $object, Argument $class): self
    {
        $new = clone $this;
        $new->results[] = ['instanceOf', [$object, $class]];

        return $new;
    }

    public function not()
    {

    }

    public function or()
    {

    }

    public function and()
    {

    }

    public function check()
    {

    }
}
