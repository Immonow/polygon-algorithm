<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Class Segment
 * @package MartinezRueda
 */
class Segment
{
    public $p1;

    public $p2;

    public function __construct(Point $p1, Point $p2)
    {
        $this->setBegin($p1);
        $this->setEnd($p2);
    }

    public function setBegin(Point $p): void
    {
        $this->p1 = $p;
    }

    public function setEnd(Point $p): void
    {
        $this->p2 = $p;
    }

    public function begin(): Point
    {
        return $this->p1;
    }

    public function end(): Point
    {
        return $this->p2;
    }

    public function changeOrientation(): void
    {
        $tmp = $this->p1;
        $this->p1 = $this->p2;
        $this->p2 = $tmp;
    }
}
