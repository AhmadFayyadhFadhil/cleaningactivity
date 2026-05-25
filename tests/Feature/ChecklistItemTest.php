<?php

namespace Tests\Feature;

use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistItemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected ChecklistItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-checklists');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo('view-checklists');

        $this->item = ChecklistItem::create([
            'item_code'   => 'CHK001',
            'item_name'   => 'Bersihkan Lantai',
            'category'    => 'Lantai',
            'description' => 'Pembersihan lantai dengan mop basah',
            'instruction' => '1. Siapkan mop\n2. Tuangkan cairan pembersih\n3. Pel lantai merata',
            'status'      => 'Active',
        ]);
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_list_checklist_items(): void
    {
$response = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/checklist-items');


        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'item_code',
                        'item_name',
                        'category',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_checklist_list_is_paginated(): void
    {
        ChecklistItem::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/checklist-items');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_unauthenticated_user_cannot_list_checklist_items(): void
    {
        $this->getJson('/api/checklist-items')
            ->assertStatus(401);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_admin_can_create_checklist_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/checklist-items', [
                'item_code'   => 'CHK002',
                'item_name'   => 'Bersihkan Kaca',
                'category'    => 'Kaca',
                'description' => 'Pembersihan kaca dengan glass cleaner',
                'instruction' => 'Semprot dan lap hingga bersih',
                'status'      => 'Active',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.item_code', 'CHK002')
            ->assertJsonPath('data.item_name', 'Bersihkan Kaca');

        $this->assertDatabaseHas('checklist_items', [
            'item_code' => 'CHK002',
            'category'  => 'Kaca',
        ]);
    }

    public function test_create_fails_when_item_code_is_missing(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/checklist-items', [
                'item_name' => 'Item Tanpa Kode',
                'category'  => 'Umum',
                'status'    => 'Active',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_code']);
    }

    public function test_create_fails_with_duplicate_item_code(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/checklist-items', [
                'item_code' => 'CHK001', // sudah ada
                'item_name' => 'Item Duplikat',
                'category'  => 'Umum',
                'status'    => 'Active',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_code']);
    }

    public function test_create_fails_with_invalid_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/checklist-items', [
                'item_code' => 'CHK003',
                'item_name' => 'Item Baru',
                'category'  => 'Umum',
                'status'    => 'pending', // invalid
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_without_permission_cannot_create_checklist_item(): void
    {
        $unauthorized = User::factory()->create();

        $this->actingAs($unauthorized)
            ->postJson('/api/checklist-items', [
                'item_code' => 'CHK999',
                'item_name' => 'Item Unauthorized',
                'category'  => 'Umum',
                'status'    => 'Active',
            ])
            ->assertStatus(403);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_show_checklist_item(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson("/api/checklist-items/{$this->item->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.item_code', 'CHK001')
            ->assertJsonPath('data.item_name', 'Bersihkan Lantai')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'item_code',
                    'item_name',
                    'category',
                    'description',
                    'instruction',
                    'status',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_item(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/api/checklist-items/99999')
            ->assertStatus(404);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_admin_can_update_checklist_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/checklist-items/{$this->item->id}", [
                'item_name'   => 'Bersihkan Lantai (Updated)',
                'description' => 'Pembersihan lantai diperbarui',
                'status'      => 'Inactive',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.item_name', 'Bersihkan Lantai (Updated)')
            ->assertJsonPath('data.status', 'Inactive');

        $this->assertDatabaseHas('checklist_items', [
            'id'        => $this->item->id,
            'item_name' => 'Bersihkan Lantai (Updated)',
            'status'    => 'Inactive',
        ]);
    }

    public function test_update_fails_with_invalid_status(): void
    {
        $this->actingAs($this->admin)
            ->putJson("/api/checklist-items/{$this->item->id}", [
                'status' => 'disabled', // invalid
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_without_permission_cannot_update_checklist_item(): void
    {
        $this->actingAs($this->staff)
            ->putJson("/api/checklist-items/{$this->item->id}", [
                'item_name' => 'Tidak Boleh Update',
            ])
            ->assertStatus(403);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_admin_can_delete_checklist_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/checklist-items/{$this->item->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('checklist_items', [
            'id' => $this->item->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_item(): void
    {
        $this->actingAs($this->admin)
            ->deleteJson('/api/checklist-items/99999')
            ->assertStatus(404);
    }

    public function test_user_without_permission_cannot_delete_checklist_item(): void
    {
        $this->actingAs($this->staff)
            ->deleteJson("/api/checklist-items/{$this->item->id}")
            ->assertStatus(403);
    }

    // ─── SUBMISSION COUNT ─────────────────────────────────────────────────────

    public function test_checklist_item_includes_submission_count(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson("/api/checklist-items/{$this->item->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_submissions', 0);
    }
}