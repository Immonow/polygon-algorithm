<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Event - X coordinate at which something interesting happens:
 * left, right endpoint or edge intersection
 *
 * Class SweepEvent
 * @package MartinezRueda
 */
class SweepEvent
{
    /**
     * Point associated with the event
     *
     * @var Point|null
     */
    public $p;

    /**
     * Is the point the left endpoint of the edge (p, other->p)
     *
     * @var bool|null
     */
    public $is_left;

    /**
     * Indicates if the edge belongs to subject or clipping polygon
     *
     * @var int|null
     */
    public $polygon_type;

    /**
     * Inside-outside transition into the polygon
     *
     * @var bool|null
     */
    public $in_out;

    /**
     * Is the edge (p, other->p) inside the other polygon
     *
     * @var bool|null
     */
    public $inside;

    /**
     * For sorting, increases monotonically
     *
     * @var int
     */
    public $id = 0;

    /**
     * @deprecated
     */
    public $pos; // in s
    /**
     * SweepEvent constructor.
     * @param int $edge_type
     */
    public function __construct(
        Point $p,
        bool $is_left,
        int $associated_polygon,
        /**
         * Event associated to the other endpoint of the edge
         */
        public $other = null,
        /**
         * Used for overlapped edges
         */
        public $edge_type = Algorithm::EDGE_TYPE_NORMAL
    ) {
        $this->p = $p;
        $this->is_left = $is_left;
        $this->polygon_type = $associated_polygon;

        static $id = 0;

        $this->id = ++$id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function segment(): Segment
    {
        return new Segment($this->p, $this->other->p);
    }

    public function below(Point $point): bool
    {
        return $this->is_left
            ? Helper::signedArea($this->p, $this->other->p, $point) > 0
            : Helper::signedArea($this->other->p, $this->p, $point) > 0;
    }

    public function above(Point $point): bool
    {
        return !$this->below($point);
    }

    public function equalsTo(SweepEvent $event): bool
    {
        return $this->getId() === $event->getId();
    }

    public function lessThan(SweepEvent $event): bool
    {
        return $this->getId() < $event->getId();
    }
}
