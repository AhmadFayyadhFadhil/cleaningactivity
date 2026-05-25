<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Area $area;
    protected CleaningSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['create-schedules', 'manage-schedules']);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo('view-schedules');

        $this->area = Area::create([
            'area_code'          => 'AREA001',
            'area_name'          => 'Lobby Utama',
            'location'           => 'Building A, Lantai 1',
            'floor'              => '1',
            'building'           => 'Building A',
            'pic_user_id'        => $this->admin->id,
            'status'             => 'active',
            'schedule_frequency' => 'daily',
        ]);

        $this->schedule = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDay()->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'supervisor_id'  => $this->admin->id,
            'status'         => 'scheduled',
            'priority'       => 'medium',
            'notes'          => 'Jadwal rutin harian',
        ]);
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function test_admin_can_list_all_schedules(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/schedules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'area',
                        'assigned_to',
                        'supervisor',
                        'schedule_date',
                        'status',
                        'priority',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_schedule_list_is_paginated(): void
    {
        CleaningSchedule::factory()->count(20)->create([
            'area_id'        => $this->area->id,
            'assigned_to_id' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/schedules');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_unauthenticated_user_cannot_list_schedules(): void
    {
        $this->getJson('/api/schedules')
            ->assertStatus(401);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_admin_can_create_schedule(): void
    {
        $payload = [
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDays(2)->toDateString(),
            'schedule_time'  => '09:00',
            'assigned_to_id' => $this->staff->id,
            'supervisor_id'  => $this->admin->id,
            'status'         => 'scheduled',
            'priority'       => 'high',
            'notes'          => 'Deep cleaning setiap Senin',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/schedules', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('cleaning_schedules', [
            'area_id'  => $this->area->id,
            'priority' => 'high',
            'status'   => 'scheduled',
        ]);
    }

    public function test_create_schedule_fails_with_past_date(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/schedules', [
                'area_id'        => $this->area->id,
                'schedule_date'  => now()->subDay()->toDateString(),
                'schedule_time'  => '08:00',
                'assigned_to_id' => $this->staff->id,
                'status'         => 'scheduled',
                'priority'       => 'low',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_date']);
    }

    public function test_create_schedule_fails_with_invalid_area(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/schedules', [
                'area_id'        => 99999,
                'schedule_date'  => now()->addDay()->toDateString(),
                'schedule_time'  => '08:00',
                'assigned_to_id' => $this->staff->id,
                'status'         => 'scheduled',
                'priority'       => 'medium',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['area_id']);
    }

    public function test_create_schedule_fails_with_invalid_priority(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/schedules', [
                'area_id'        => $this->area->id,
                'schedule_date'  => now()->addDay()->toDateString(),
                'schedule_time'  => '08:00',
                'assigned_to_id' => $this->staff->id,
                'status'         => 'scheduled',
                'priority'       => 'extreme', // invalid
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_user_without_permission_cannot_create_schedule(): void
    {
        $unauthorized = User::factory()->create();

        $response = $this->actingAs($unauthorized)
            ->postJson('/api/schedules', [
                'area_id'        => $this->area->id,
                'schedule_date'  => now()->addDay()->toDateString(),
                'schedule_time'  => '08:00',
                'assigned_to_id' => $this->staff->id,
                'status'         => 'scheduled',
                'priority'       => 'low',
            ]);

        $response->assertStatus(403);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_admin_can_show_schedule_detail(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/schedules/{$this->schedule->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->schedule->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'area',
                    'assigned_to',
                    'supervisor',
                    'status',
                    'priority',
                    'schedule_date',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_schedule(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/schedules/99999')
            ->assertStatus(404);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_admin_can_update_schedule(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/schedules/{$this->schedule->id}", [
                'status'   => 'in-progress',
                'priority' => 'urgent',
                'notes'    => 'Perubahan prioritas',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'in-progress')
            ->assertJsonPath('data.priority', 'urgent');

        $this->assertDatabaseHas('cleaning_schedules', [
            'id'       => $this->schedule->id,
            'status'   => 'in-progress',
            'priority' => 'urgent',
        ]);
    }

    public function test_update_fails_with_invalid_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/schedules/{$this->schedule->id}", [
                'status' => 'done', // invalid value
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_admin_can_delete_schedule(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/schedules/{$this->schedule->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('cleaning_schedules', [
            'id' => $this->schedule->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_schedule(): void
    {
        $this->actingAs($this->admin)
            ->deleteJson('/api/schedules/99999')
            ->assertStatus(404);
    }
}