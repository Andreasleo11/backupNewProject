<?php

use App\Models\User;
use App\Policies\DisciplineAccessPolicy;

beforeEach(function () {
    $this->policy = new DisciplineAccessPolicy;
});

test('department head can view any discipline', function () {
    $user = new User;
    $user->is_head = 1;

    expect($this->policy->viewAnyDiscipline($user))->toBeTrue();
});

test('non department head cannot view any discipline', function () {
    $user = new User(['is_head' => 0]);

    expect($this->policy->viewAnyDiscipline($user))->toBeFalse();
});

test('special user id 120 can view any discipline', function () {
    $user = new User;
    $user->id = 120;
    $user->is_head = 0;

    expect($this->policy->viewAnyDiscipline($user))->toBeTrue();
});

test('special email users can view all discipline', function () {
    $user1 = new User([
        'email' => 'ani_apriani@daijo.co.id',
        'is_head' => 0,
    ]);

    $user2 = new User([
        'email' => 'bernadett@daijo.co.id',
        'is_head' => 0,
    ]);

    expect($this->policy->viewAllDiscipline($user1))->toBeTrue();
    expect($this->policy->viewAllDiscipline($user2))->toBeTrue();
});

test('regular users cannot view all discipline', function () {
    $user = new User([
        'email' => 'regular@daijo.co.id',
        'is_head' => 0,
    ]);

    expect($this->policy->viewAllDiscipline($user))->toBeFalse();
});

test('special email users can view any discipline', function () {
    $user = new User([
        'email' => 'ani_apriani@daijo.co.id',
        'is_head' => 0,
    ]);

    // Special email users should also pass viewAnyDiscipline check
    expect($this->policy->viewAnyDiscipline($user))->toBeTrue();
});

test('department head can view yayasan discipline', function () {
    $user = new User;
    $user->is_head = 1;

    expect($this->policy->viewYayasanDiscipline($user))->toBeTrue();
});

test('special users can view yayasan discipline', function () {
    $user = new User([
        'email' => 'bernadett@daijo.co.id',
        'is_head' => 0,
    ]);

    expect($this->policy->viewYayasanDiscipline($user))->toBeTrue();
});

test('regular users cannot view yayasan discipline', function () {
    $user = new User([
        'email' => 'regular@daijo.co.id',
        'is_head' => 0,
    ]);

    expect($this->policy->viewYayasanDiscipline($user))->toBeFalse();
});
