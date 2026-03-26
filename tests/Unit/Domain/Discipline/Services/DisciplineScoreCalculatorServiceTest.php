<?php

use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Models\EvaluationData;

beforeEach(function () {
    $this->calculator = new DisciplineScoreCalculatorService;
});

test('it calculates total score with new system all a grades', function () {
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
    expect($total)->toBe(100);
});

test('it calculates total score with new system mixed grades', function () {
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
    expect($total)->toBe(79);
});

test('it calculates total score with new system with penalties', function () {
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
    expect($total)->toBe(69);
});

test('it calculates total score with old system all a grades', function () {
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
    expect($total)->toBe(100);
});

test('it calculates total score with old system mixed grades', function () {
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
    expect($total)->toBe(80);
});

test('it calculates total score with old system with penalties', function () {
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
    expect($total)->toBe(83);
});

test('it handles e grade as zero points new system', function () {
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

    expect($total)->toBe(0);
});

test('it handles e grade as zero points old system', function () {
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
    expect($total)->toBe(40);
});

test('it handles null penalties as zero', function () {
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
    expect($total)->toBe(100);
});

test('it handles partial scores new system', function () {
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
    expect($total)->toBe(37);
});

test('it handles partial scores old system', function () {
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
    expect($total)->toBe(65);
});

test('it can result in negative total with high penalties', function () {
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
    expect($total)->toBe(-120);
});

test('it returns correct new scored fields', function () {
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

    expect($fields)->toEqual($expected);
    expect($fields)->toHaveCount(9);
});

test('it returns correct old scored fields', function () {
    $fields = $this->calculator->getOldScoredFields();

    $expected = [
        'kerajinan_kerja',
        'kerapian_kerja',
        'prestasi',
        'loyalitas',
        'perilaku_kerja',
    ];

    expect($fields)->toEqual($expected);
    expect($fields)->toHaveCount(5);
});

test('it ignores unknown fields', function () {
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
    expect($total)->toBe(17);
});

test('it handles decimal penalties correctly', function () {
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
    expect($total)->toBe(10.5);
});
