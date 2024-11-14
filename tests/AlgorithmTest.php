<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use MartinezRueda\Point;
use MartinezRueda\Polygon;
use MartinezRueda\Algorithm;

use MartinezRueda\SweepEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AlgorithmTest extends TestCase
{
    protected $implementation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->implementation = new Algorithm();
    }

    private function prepareData(?array $subject_data = null, ?array $clipping_data = null): array
    {
        if ($subject_data === null) {
            $subject_data = json_decode($this->getTestData('subjectData.json'));
        }

        if ($clipping_data === null) {
            $clipping_data = json_decode($this->getTestData('clippingData.json'));
        }

        $subject = new Polygon($subject_data);
        $clipping = new Polygon($clipping_data);

        return [$subject, $clipping];
    }

    private function runTestCase(string $method, array $data): void
    {
        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    public static function methodProvider(): array
    {
        return [
            'union' => ['getUnion'],
            'difference' => ['getDifference'],
            'intersection' => ['getIntersection'],
            'xor' => ['getXor']
        ];
    }

    #[DataProvider('methodProvider')]
    public function test_it_gets_all_polygons(string $method)
    {
        $polygon_data = [
            'subjectData.json',
            'subjectDataWithHole.json',
            'complexSubjectWithHoles.json',
            'simpleSubjectData.json',
            'hugePolygonSubjectData.json',
            'clippingData.json',
            'clippingDataWithHole.json',
            'complexClippingWithHoles.json',
            'simpleClippingData.json',
            'hugePolygonClippingData.json',
            'dataOutside.json',
            'edgeTouchingData.json',
            'polygonInside.json',
            'selfIntersectingData.json',
            'subjectDifferentTransition.json',
            'clippingDifferentTransition.json'
        ];

        $results = [];
        for ($i = 0; $i < count($polygon_data) - 1; $i++) {
            $data = $this->prepareData(json_decode($this->getTestData($polygon_data[$i])), json_decode($this->getTestData($polygon_data[$i + 1])));
            $result = $this->implementation->$method($data[0], $data[1]);
            $results[] = $result->toArray();
        }

        $this->assertMatchesSnapshot($results);
    }

    #[DataProvider('methodProvider')]
    public function test_polygons_dont_overlap(string $method)
    {
        $data = $this->prepareData(json_decode($this->getTestData('dataOutside.json')));

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_polygons_with_hole(string $method): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('subjectDataWithHole.json')), json_decode($this->getTestData('clippingDataWithHole.json')));

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_polygons_with_multiple_holes(string $method): void
    {
        $data = $this->prepareData(
            json_decode($this->getTestData('complexSubjectWithHoles.json')),
            json_decode($this->getTestData('complexClippingWithHoles.json'))
        );

        $result = $this->implementation->$method($data[0], $data[1]);
        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_union_self_intersecting(string $method): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('selfIntersectingData.json')));

        $result = $this->implementation->$method($data[0], $data[1]);
        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_invalid_polygon_data(string $method): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong array formation. Your point: 1.123');

        $data = $this->prepareData([[1.123]]);

        $this->implementation->$method($data[0], $data[1]);
    }

    #[DataProvider('methodProvider')]
    public function test_polygon_inside_other_polygon_hole(string $method): void
    {
        $data = $this->prepareData(
            json_decode($this->getTestData('polygonInside.json')),
            json_decode($this->getTestData('hugePolygonClippingData.json'))
        );

        $result = $this->implementation->$method($data[0], $data[1]);
        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_subject_clipping_switched(string $method): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('clippingData.json')), json_decode($this->getTestData('subjectData.json')));

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_same_polygon(string $method): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('clippingData.json')));

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_non_normal_edge_different_polygon_type1(string $method): void
    {
        $data = $this->prepareData(
            json_decode($this->getTestData('simpleSubjectData.json')),
            json_decode($this->getTestData('simpleClippingData.json'))
        );

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    #[DataProvider('methodProvider')]
    public function test_non_normal_edge_different_polygon_type(string $method): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('simpleSubjectData.json')), json_decode($this->getTestData('simpleClippingData.json')));

        $result = $this->implementation->$method($data[0], $data[1]);

        $this->assertMatchesSnapshot($result->toArray());
    }

    /** Union Tests */
    public function test_union_with_empty_subject(): void
    {
        $data = $this->prepareData([], json_decode($this->getTestData('clippingData.json')));

        $result = $this->implementation->getUnion($data[0], $data[1]);

        $this->assertEquals($data[1]->toArray(), $result->toArray());
    }

    public function test_union_with_empty_clipping(): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('subjectData.json')), []);

        $result = $this->implementation->getUnion($data[0], $data[1]);

        $this->assertEquals($data[0]->toArray(), $result->toArray());
    }

    /** Difference Tests */
    public function test_difference_with_empty_clipping(): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('subjectData.json')), []);

        $result = $this->implementation->getDifference($data[0], $data[1]);

        $this->assertEquals($data[0]->toArray(), $result->toArray());
    }

    public function test_difference_with_empty_subject(): void
    {
        $data = $this->prepareData([], json_decode($this->getTestData('clippingData.json')));

        $result = $this->implementation->getDifference($data[0], $data[1]);

        $this->assertSame(0, $result->ncontours());
    }

    /** XOR Tests */
    public function test_xor_with_empty_subject(): void
    {
        $data = $this->prepareData([], json_decode($this->getTestData('clippingData.json')));

        $result = $this->implementation->getXor($data[0], $data[1]);

        $this->assertEquals($data[1]->toArray(), $result->toArray());
    }

    public function test_xor_with_empty_clipping(): void
    {
        $data = $this->prepareData(json_decode($this->getTestData('subjectData.json')), []);

        $result = $this->implementation->getXor($data[0], $data[1]);

        $this->assertEquals($data[0]->toArray(), $result->toArray());
    }



    public function test_queue(): void
    {
        $data = $this->prepareData();

        $methods = ['getXor', 'getDifference', 'getIntersection'];

        $results = [];
        foreach ($methods as $method) {
            $result = $this->implementation->$method($data[0], $data[1]);
            $results[] = $result->toArray();
        }
        $this->assertMatchesSnapshot($results);
    }

    public function test_throws_error_on_overlap(): void
    {
        $data = $this->prepareData();

        $methods = ['getDifference', 'getXor'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Polygon has overlapping edges.');

        $results = [];
        foreach ($methods as $method) {
            $result = $this->implementation->$method($data[0], $data[1]);
            $results[] = $result->toArray();
        }

        $this->assertMatchesSnapshot($results);
    }

    public function test_non_normal_edge_type_with_position_less_than_two(): void
    {
        $data = [
            [
                [0, 0],
                [5, 0],
                [5, 5],
                [0, 5],
                [0, 0]
            ],
            [
                [3, 3],
                [8, 3],
                [8, 8],
                [3, 8],
                [3, 3]
            ]
        ];

        $subjectData = $data;
        $clippingData = $data;

        $subject = new Polygon($subjectData);
        $clipping = new Polygon($clippingData);

        $event1 = new SweepEvent(new Point(0, 0), true, Algorithm::POLYGON_TYPE_SUBJECT);
        $event2 = new SweepEvent(new Point(5, 5), true, Algorithm::POLYGON_TYPE_CLIPPING);

        $event1->edge_type = Algorithm::EDGE_TYPE_SAME_TRANSITION;
        $event2->edge_type = Algorithm::EDGE_TYPE_DIFFERENT_TRANSITION;

        $this->implementation->setEventHolder([$event1, $event2]);

        $result = $this->implementation->getUnion($subject, $clipping);

        $this->assertMatchesSnapshot($result->toArray());
    }

    public function test_difference_edge_type_different_transition(): void
    {
        $data = $this->prepareData(
            json_decode($this->getTestData('subjectDifferentTransition.json')),
            json_decode($this->getTestData('clippingDifferentTransition.json'))
        );

        $result = $this->implementation->getDifference($data[0], $data[1]);
        $this->assertMatchesSnapshot($result->toArray());
    }


}
