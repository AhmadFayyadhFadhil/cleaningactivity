<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $area;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with permission
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-areas');

        // Create test area
        $this->area = Area::create([
            'area_code' => 'TEST001',
            'area_name' => 'Test Area',
            'location' => 'Building A, Floor 1',
            'floor' => 1,
            'building' => 'Building A',
            'pic_user_id' => $this->admin->id,
            'status' => 'Active',
            'schedule_frequency' => 'Daily',
        ]);
    }

    public function test_can_list_areas(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/areas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'area_code',
                        'area_name',
                        'location',
                        'floor',
                        'building',
                        'pic',
                        'status',
                        'schedule_frequency',
                    ],
                ],
            ]);
    }

    public function test_can_create_area(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/areas', [
                'area_code' => 'NEW001',
                'area_name' => 'New Area',
                'location' => 'Building B, Floor 2',
                'floor' => 2,
                'building' => 'Building B',
                'pic_user_id' => $this->admin->id,
                'status' => 'Active',
                'schedule_frequency' => 'Weekly',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.area_code', 'NEW001');

        $this->assertDatabaseHas('areas', [
            'area_code' => 'NEW001',
            'area_name' => 'New Area',
        ]);
    }

    public function test_can_show_area(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/areas/{$this->area->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.area_code', 'TEST001');
    }

    public function test_can_update_area(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/areas/{$this->area->id}", [
                'area_name' => 'Updated Area Name',
                'status' => 'Inactive',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.area_name', 'Updated Area Name');

        $this->assertDatabaseHas('areas', [
            'id' => $this->area->id,
            'area_name' => 'Updated Area Name',
            'status' => 'Inactive',
        ]);
    }

    public function test_can_delete_area(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/areas/{$this->area->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('areas', [
            'id' => $this->area->id,
        ]);
    }

    public function test_cannot_create_duplicate_area_code(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/areas', [
                'area_code' => 'TEST001',
                'area_name' => 'Duplicate Code Area',
                'location' => 'Building C, Floor 1',
                'floor' => 1,
                'building' => 'Building C',
                'pic_user_id' => $this->admin->id,
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ]);

        $response->assertStatus(422);
    }

    public function test_unauthorized_user_cannot_manage_areas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/areas');

        $response->assertStatus(403);
    }
}
