<?php

namespace Tests\Unit\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestSignatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestSignatureService $service;
    private PurchaseRequest $pr;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new PurchaseRequestSignatureService();
        $this->user = User::factory()->create();
        $this->pr = PurchaseRequest::factory()->create();
    }

    public function test_adds_signature_to_purchase_request(): void
    {
        $signature = $this->service->addSignature(
            $this->pr,
            'DEPT_HEAD',
            $this->user->id,
            'signatures/test.png'
        );

        $this->assertDatabaseHas('purchase_request_signatures', [
            'purchase_request_id' => $this->pr->id,
            'step_code' => 'DEPT_HEAD',
            'signed_by_user_id' => $this->user->id,
            'image_path' => 'signatures/test.png',
        ]);

        $this->assertNotNull($signature->signed_at);
    }

    public function test_checks_signature_existence(): void
    {
        $this->assertFalse($this->service->hasSignature($this->pr, 'DEPT_HEAD'));

        $this->service->addSignature($this->pr, 'DEPT_HEAD', $this->user->id, 'test.png');

        $this->assertTrue($this->service->hasSignature($this->pr, 'DEPT_HEAD'));
    }

    public function test_retrieves_signature_by_step_code(): void
    {
        $this->service->addSignature($this->pr, 'VERIFICATOR', $this->user->id, 'test.png');

        $signature = $this->service->getSignature($this->pr, 'VERIFICATOR');

        $this->assertNotNull($signature);
        $this->assertEquals('VERIFICATOR', $signature->step_code);
        $this->assertEquals($this->user->id, $signature->signed_by_user_id);
    }

    public function test_returns_null_when_signature_not_found(): void
    {
        $signature = $this->service->getSignature($this->pr, 'NONEXISTENT');

        $this->assertNull($signature);
    }

    public function test_creates_signature_with_timestamp(): void
    {
        $before = now();
        
        $signature = $this->service->addSignature(
            $this->pr,
            'DIRECTOR',
            $this->user->id,
            'test.png'
        );

        $after = now();

        $this->assertNotNull($signature->signed_at);
        $this->assertTrue($signature->signed_at->between($before, $after));
    }

    public function test_gets_all_signatures_ordered_by_time(): void
    {
        $this->service->addSignature($this->pr, 'MAKER', $this->user->id, 'test1.png');
        sleep(1);
        $this->service->addSignature($this->pr, 'DEPT_HEAD', $this->user->id, 'test2.png');
        sleep(1);
        $this->service->addSignature($this->pr, 'VERIFICATOR', $this->user->id, 'test3.png');

        $signatures = $this->service->getAllSignatures($this->pr);

        $this->assertCount(3, $signatures);
        $this->assertEquals('MAKER', $signatures[0]->step_code);
        $this->assertEquals('DEPT_HEAD', $signatures[1]->step_code);
        $this->assertEquals('VERIFICATOR', $signatures[2]->step_code);
    }

    public function test_removes_signature(): void
    {
        $this->service->addSignature($this->pr, 'DEPT_HEAD', $this->user->id, 'test.png');
        
        $this->assertTrue($this->service->hasSignature($this->pr, 'DEPT_HEAD'));

        $removed = $this->service->removeSignature($this->pr, 'DEPT_HEAD');

        $this->assertTrue($removed);
        $this->assertFalse($this->service->hasSignature($this->pr, 'DEPT_HEAD'));
    }

    public function test_remove_returns_false_for_nonexistent_signature(): void
    {
        $removed = $this->service->removeSignature($this->pr, 'NONEXISTENT');

        $this->assertFalse($removed);
    }
}
