<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePurchaseRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $fromDept;
    private Department $toDept;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with department
        $this->fromDept = Department::factory()->create([
            'name' => 'COMPUTER',
            'is_office' => true,
        ]);

        $this->toDept = Department::factory()->create([
            'name' => 'PURCHASING',
            'is_office' => true,
        ]);

        $this->user = User::factory()->create([
            'department_id' => $this->fromDept->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_purchase_request_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Test purchase request',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => false,
            'items' => [
                [
                    'item_name' => 'Test Item 1',
                    'quantity' => 10,
                    'uom' => 'PCS',
                    'price' => 100.50,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
                [
                    'item_name' => 'Test Item 2',
                    'quantity' => 5,
                    'uom' => 'PCS',
                    'price' => 200.00,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        $response->assertRedirect(route('purchase-requests.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'user_id_create' => $this->user->id,
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'status' => 1, // Pending Department Head
            'type' => 'office',
        ]);

        $pr = PurchaseRequest::latest()->first();
        $this->assertNotNull($pr->doc_num);
        $this->assertNotNull($pr->pr_no);

        // Verify items were created
        $this->assertDatabaseHas('detail_purchase_requests', [
            'purchase_request_id' => $pr->id,
            'item_name' => 'Test Item 1',
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('detail_purchase_requests', [
            'purchase_request_id' => $pr->id,
            'item_name' => 'Test Item 2',
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function it_creates_draft_purchase_request_with_status_8()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Draft PR',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => true, // Draft mode
            'items' => [
                [
                    'item_name' => 'Draft Item',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 50,
                    'currency' => 'IDR',
                    'purpose' => 'Testing draft',
                ],
            ],
        ]);

        $this->assertDatabaseHas('purchase_requests', [
            'user_id_create' => $this->user->id,
            'status' => 8, // Draft status
        ]);
    }

    /** @test */
    public function it_generates_unique_document_numbers()
    {
        $this->actingAs($this->user);

        // Create first PR
        $this->post(route('purchase-requests.store'), [
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'First PR',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => false,
            'items' => [
                [
                    'item_name' => 'Item 1',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        // Create second PR
        $this->post(route('purchase-requests.store'), [
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Second PR',
            'supplier' => 'Test Supplier 2',
            'pic' => 'Test PIC 2',
            'is_draft' => false,
            'items' => [
                [
                    'item_name' => 'Item 2',
                    'quantity' => 2,
                    'uom' => 'PCS',
                    'price' => 200,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        $prs = PurchaseRequest::latest()->take(2)->get();

        // Verify doc_nums are different
        $this->assertNotEquals($prs[0]->doc_num, $prs[1]->doc_num);

        // Verify pr_nos are different
        $this->assertNotEquals($prs[0]->pr_no, $prs[1]->pr_no);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            // Missing required fields
        ]);

        $response->assertSessionHasErrors([
            'from_department',
            'to_department',
            'date_of_pr',
            'date_of_required',
        ]);
    }

    /** @test */
    public function it_requires_at_least_one_item()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            'from_department' => 'COMPUTER',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Test',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => false,
            'items' => [], // No items
        ]);

        $response->assertSessionHasErrors(['items']);
    }

    /** @test */
    public function it_sets_correct_status_for_plastic_injection_department()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            'from_department' => 'PLASTIC INJECTION',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Test',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => false,
            'items' => [
                [
                    'item_name' => 'Test Item',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        // Plastic Injection should go directly to GM (status 7)
        $this->assertDatabaseHas('purchase_requests', [
            'from_department' => 'PLASTIC INJECTION',
            'status' => 7,
        ]);
    }

    /** @test */
    public function it_sets_correct_status_for_personalia_department()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.store'), [
            'from_department' => 'PERSONALIA',
            'to_department' => 'PURCHASING',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'remark' => 'Test',
            'supplier' => 'Test Supplier',
            'pic' => 'Test PIC',
            'is_draft' => false,
            'items' => [
                [
                    'item_name' => 'Test Item',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        // Personalia should go directly to Purchaser (status 6)
        $this->assertDatabaseHas('purchase_requests', [
            'from_department' => 'PERSONALIA',
            'status' => 6,
        ]);
    }
}
