<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Class Point
 * @package MartinezRueda
 */
class Point
{
    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * Point constructor.
     */
    public function __construct(float $x = 0, float $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function equalsTo(Point $p): bool
    {
        return ($this->x === $p->x && $this->y === $p->y);
    }

    public function distanceTo(Point $p): float
    {
        $dx = $this->x - $p->x;
        $dy = $this->y - $p->y;

        return sqrt($dx * $dx + $dy * $dy);
    }
}
