<?php

declare(strict_types=1);

namespace MartinezRueda;

/**
 * Class Connector
 * @package MartinezRueda
 */
class Connector
{
    public $open_polygons = [];

    public $closed_polygons = [];

    /**
     * @var bool
     */
    protected $closed = false;

    public function isClosed() : bool
    {
        return $this->closed;
    }

    public function add(Segment $segment): void
    {
        $size = count($this->open_polygons);

        for ($j = 0; $j < $size; $j++) {
            $chain = $this->open_polygons[$j];

            if (!$chain->linkSegment($segment)) {
                continue;
            }

            if ($chain->closed) {
                if (count($chain->segments) == 2) {
                    $chain->closed = false;

                    return;
                }

                $this->closed_polygons[] = $this->open_polygons[$j];

                Helper::removeElementWithShift($this->open_polygons, $j);

                return;
            }

            // if chain not closed
            $k = count($this->open_polygons);

            for ($i = $j + 1; $i < $k; $i++) {
                $v = $this->open_polygons[$i];

                if ($chain->linkChain($v)) {
                    Helper::removeElementWithShift($this->open_polygons, $i);

                    return;
                }
            }

            return;
        }

        $new_chain = new PointChain();
        $new_chain->init($segment);

        $this->open_polygons[] = $new_chain;
    }

    public function toPolygon() : Polygon
    {
        $contours = [];

        foreach ($this->closed_polygons as $closed_polygon) {
            $contour_points = [];

            foreach ($closed_polygon->segments as $point) {
                $contour_points[] = [$point->x, $point->y];
            }

            // close contour
            $first = reset($contour_points);
            $last = end($contour_points);

            if ($last != $first) {
                $contour_points[] = $first;
            }

            $contours[] = $contour_points;
        }

        return new Polygon($contours);
    }
}