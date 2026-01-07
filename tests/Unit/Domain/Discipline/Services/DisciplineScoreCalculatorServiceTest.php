<?php

namespace Tests\Unit\Domain\Discipline\Services;

use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Models\EvaluationData;
use Tests\TestCase;

class DisciplineScoreCalculatorServiceTest extends TestCase
{
    private DisciplineScoreCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DisciplineScoreCalculatorService;
    }

    /** @test */
    public function it_calculates_total_score_with_new_system_all_a_grades()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kemampuan_kerja' => 'A',
            'kecerdasan_kerja' => 'A',
            'qualitas_kerja' => 'A',
            'disiplin_kerja' => 'A',
            'kepatuhan_kerja' => 'A',
            'lembur' => 'A',
            'efektifitas_kerja' => 'A',
            'relawan' => 'A',
            'integritas' => 'A',
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 17 + 16 + 11 + 8 + 10 + 10 + 10 + 10 + 8 = 100
        $this->assertEquals(100, $total);
    }

    /** @test */
    public function it_calculates_total_score_with_new_system_mixed_grades()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kemampuan_kerja' => 'B',    // 14
            'kecerdasan_kerja' => 'A',   // 16
            'qualitas_kerja' => 'C',     // 7
            'disiplin_kerja' => 'B',     // 6
            'kepatuhan_kerja' => 'A',    // 10
            'lembur' => 'D',             // 4
            'efektifitas_kerja' => 'B',  // 8
            'relawan' => 'C',            // 6
            'integritas' => 'A',         // 8
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 14 + 16 + 7 + 6 + 10 + 4 + 8 + 6 + 8 = 79
        $this->assertEquals(79, $total);
    }

    /** @test */
    public function it_calculates_total_score_with_new_system_with_penalties()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 2,    // -20
            'Izin' => 3,     // -6
            'Sakit' => 4,    // -4
            'Telat' => 2,    // -1
        ]);

        $scores = [
            'kemampuan_kerja' => 'A',
            'kecerdasan_kerja' => 'A',
            'qualitas_kerja' => 'A',
            'disiplin_kerja' => 'A',
            'kepatuhan_kerja' => 'A',
            'lembur' => 'A',
            'efektifitas_kerja' => 'A',
            'relawan' => 'A',
            'integritas' => 'A',
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 100 - (2*10 + 3*2 + 4*1 + 2*0.5) = 100 - 31 = 69
        $this->assertEquals(69, $total);
    }

    /** @test */
    public function it_calculates_total_score_with_old_system_all_a_grades()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kerajinan_kerja' => 'A',  // 10
            'kerapian_kerja' => 'A',   // 10
            'prestasi' => 'A',         // 20
            'loyalitas' => 'A',        // 10
            'perilaku_kerja' => 'A',   // 10
        ];

        $total = $this->calculator->calculateTotalOld($scores, $evaluation);

        // Expected: 40 (base) + 10 + 10 + 20 + 10 + 10 = 100
        $this->assertEquals(100, $total);
    }

    /** @test */
    public function it_calculates_total_score_with_old_system_mixed_grades()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kerajinan_kerja' => 'B',  // 7.5
            'kerapian_kerja' => 'C',   // 5
            'prestasi' => 'B',         // 15
            'loyalitas' => 'D',        // 2.5
            'perilaku_kerja' => 'A',   // 10
        ];

        $total = $this->calculator->calculateTotalOld($scores, $evaluation);

        // Expected: 40 + 7.5 + 5 + 15 + 2.5 + 10 = 80
        $this->assertEquals(80, $total);
    }

    /** @test */
    public function it_calculates_total_score_with_old_system_with_penalties()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 1,    // -10
            'Izin' => 2,     // -4
            'Sakit' => 1,    // -1
            'Telat' => 4,    // -2
        ]);

        $scores = [
            'kerajinan_kerja' => 'A',
            'kerapian_kerja' => 'A',
            'prestasi' => 'A',
            'loyalitas' => 'A',
            'perilaku_kerja' => 'A',
        ];

        $total = $this->calculator->calculateTotalOld($scores, $evaluation);

        // Expected: 100 - (1*10 + 2*2 + 1*1 + 4*0.5) = 100 - 17 = 83
        $this->assertEquals(83, $total);
    }

    /** @test */
    public function it_handles_e_grade_as_zero_points_new_system()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kemampuan_kerja' => 'E',
            'kecerdasan_kerja' => 'E',
            'qualitas_kerja' => 'E',
            'disiplin_kerja' => 'E',
            'kepatuhan_kerja' => 'E',
            'lembur' => 'E',
            'efektifitas_kerja' => 'E',
            'relawan' => 'E',
            'integritas' => 'E',
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        $this->assertEquals(0, $total);
    }

    /** @test */
    public function it_handles_e_grade_as_zero_points_old_system()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kerajinan_kerja' => 'E',
            'kerapian_kerja' => 'E',
            'prestasi' => 'E',
            'loyalitas' => 'E',
            'perilaku_kerja' => 'E',
        ];

        $total = $this->calculator->calculateTotalOld($scores, $evaluation);

        // Expected: 40 (base) + 0 = 40
        $this->assertEquals(40, $total);
    }

    /** @test */
    public function it_handles_null_penalties_as_zero()
    {
        $evaluation = new EvaluationData([
            'Alpha' => null,
            'Izin' => null,
            'Sakit' => null,
            'Telat' => null,
        ]);

        $scores = [
            'kemampuan_kerja' => 'A',
            'kecerdasan_kerja' => 'A',
            'qualitas_kerja' => 'A',
            'disiplin_kerja' => 'A',
            'kepatuhan_kerja' => 'A',
            'lembur' => 'A',
            'efektifitas_kerja' => 'A',
            'relawan' => 'A',
            'integritas' => 'A',
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Should calculate as if penalties are 0
        $this->assertEquals(100, $total);
    }

    /** @test */
    public function it_handles_partial_scores_new_system()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        // Only provide some scores
        $scores = [
            'kemampuan_kerja' => 'A',  // 17
            'kecerdasan_kerja' => 'B', // 13
            'qualitas_kerja' => 'C',   // 7
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 17 + 13 + 7 = 37
        $this->assertEquals(37, $total);
    }

    /** @test */
    public function it_handles_partial_scores_old_system()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        // Only provide some scores
        $scores = [
            'kerajinan_kerja' => 'A',  // 10
            'prestasi' => 'B',         // 15
        ];

        $total = $this->calculator->calculateTotalOld($scores, $evaluation);

        // Expected: 40 + 10 + 15 = 65
        $this->assertEquals(65, $total);
    }

    /** @test */
    public function it_can_result_in_negative_total_with_high_penalties()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 10,   // -100
            'Izin' => 5,     // -10
            'Sakit' => 5,    // -5
            'Telat' => 10,   // -5
        ]);

        $scores = [
            'kemampuan_kerja' => 'E',
            'kecerdasan_kerja' => 'E',
            'qualitas_kerja' => 'E',
            'disiplin_kerja' => 'E',
            'kepatuhan_kerja' => 'E',
            'lembur' => 'E',
            'efektifitas_kerja' => 'E',
            'relawan' => 'E',
            'integritas' => 'E',
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 0 - 120 = -120
        $this->assertEquals(-120, $total);
    }

    /** @test */
    public function it_returns_correct_new_scored_fields()
    {
        $fields = $this->calculator->getScoredFields();

        $expected = [
            'kemampuan_kerja',
            'kecerdasan_kerja',
            'qualitas_kerja',
            'disiplin_kerja',
            'kepatuhan_kerja',
            'lembur',
            'efektifitas_kerja',
            'relawan',
            'integritas',
        ];

        $this->assertEquals($expected, $fields);
        $this->assertCount(9, $fields);
    }

    /** @test */
    public function it_returns_correct_old_scored_fields()
    {
        $fields = $this->calculator->getOldScoredFields();

        $expected = [
            'kerajinan_kerja',
            'kerapian_kerja',
            'prestasi',
            'loyalitas',
            'perilaku_kerja',
        ];

        $this->assertEquals($expected, $fields);
        $this->assertCount(5, $fields);
    }

    /** @test */
    public function it_ignores_unknown_fields()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 0,
        ]);

        $scores = [
            'kemampuan_kerja' => 'A',  // 17
            'unknown_field' => 'A',    // Should be ignored
            'another_fake' => 'A',     // Should be ignored
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: Only 17 from kemampuan_kerja
        $this->assertEquals(17, $total);
    }

    /** @test */
    public function it_handles_decimal_penalties_correctly()
    {
        $evaluation = new EvaluationData([
            'Alpha' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Telat' => 7,    // 7 * 0.5 = 3.5
        ]);

        $scores = [
            'kemampuan_kerja' => 'B',  // 14
        ];

        $total = $this->calculator->calculateTotal($scores, $evaluation);

        // Expected: 14 - 3.5 = 10.5
        $this->assertEquals(10.5, $total);
    }
}
