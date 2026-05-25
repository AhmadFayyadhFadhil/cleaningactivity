<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleaningTaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected User $otherStaff;
    protected User $admin;
    protected Area $area;
    protected CleaningSchedule $myTask;
    protected CleaningSchedule $otherTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-schedules');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo('view-tasks');

        $this->otherStaff = User::factory()->create();
        $this->otherStaff->givePermissionTo('view-tasks');

        $this->area = Area::create([
            'area_code'          => 'AREA-TASK',
            'area_name'          => 'Ruang Server',
            'location'           => 'Building C, Lantai 3',
            'floor'              => '3',
            'building'           => 'Building C',
            'pic_user_id'        => $this->admin->id,
            'status'             => 'active',
            'schedule_frequency' => 'daily',
        ]);

        $this->myTask = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '07:00',
            'assigned_to_id' => $this->staff->id,
            'supervisor_id'  => $this->admin->id,
            'status'         => 'scheduled',
            'priority'       => 'high',
        ]);

        $this->otherTask = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '09:00',
            'assigned_to_id' => $this->otherStaff->id,
            'supervisor_id'  => $this->admin->id,
            'status'         => 'scheduled',
            'priority'       => 'low',
        ]);
    }

    // ─── MY TASKS ─────────────────────────────────────────────────────────────

    public function test_staff_can_view_own_tasks(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/api/my-tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'area',
                        'assigned_to',
                        'schedule_date',
                        'status',
                        'priority',
                    ],
                ],
            ]);

        // Hanya task milik staff ini yang muncul
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($this->myTask->id, $ids->toArray());
        $this->assertNotContains($this->otherTask->id, $ids->toArray());
    }

    public function test_my_tasks_returns_empty_when_no_tasks_assigned(): void
    {
        $newStaff = User::factory()->create();
        $newStaff->givePermissionTo('view-tasks');

        $response = $this->actingAs($newStaff)
            ->getJson('/api/my-tasks');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    public function test_unauthenticated_user_cannot_access_my_tasks(): void
    {
        $this->getJson('/api/my-tasks')
            ->assertStatus(401);
    }

    // ─── SHOW TASK ────────────────────────────────────────────────────────────

    public function test_staff_can_view_own_task_detail(): void
    {
        $response = $this->actingAs($this->staff)
            ->getJson("/api/my-tasks/{$this->myTask->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->myTask->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'area',
                    'assigned_to',
                    'supervisor',
                    'status',
                    'priority',
                ],
            ]);
    }

    public function test_staff_cannot_view_other_staff_task(): void
    {
        // staff mencoba akses task milik otherStaff
        $this->actingAs($this->staff)
            ->getJson("/api/my-tasks/{$this->otherTask->id}")
            ->assertStatus(404);
    }

    public function test_show_task_returns_404_for_nonexistent_id(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/api/my-tasks/99999')
            ->assertStatus(404);
    }

    // ─── COMPLETE TASK ────────────────────────────────────────────────────────

    public function test_staff_can_complete_own_task(): void
    {
        $response = $this->actingAs($this->staff)
            ->postJson("/api/my-tasks/{$this->myTask->id}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('cleaning_schedules', [
            'id'     => $this->myTask->id,
            'status' => 'completed',
        ]);
    }

    public function test_completing_already_completed_task_returns_error(): void
    {
        // Complete task pertama kali
        $this->myTask->update(['status' => 'completed']);

        $response = $this->actingAs($this->staff)
            ->postJson("/api/my-tasks/{$this->myTask->id}/complete");

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_staff_cannot_complete_other_staff_task(): void
    {
        $this->actingAs($this->staff)
            ->postJson("/api/my-tasks/{$this->otherTask->id}/complete")
            ->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_complete_task(): void
    {
        $this->postJson("/api/my-tasks/{$this->myTask->id}/complete")
            ->assertStatus(401);
    }

    // ─── MY TASKS ORDERED BY DATE ─────────────────────────────────────────────

    public function test_my_tasks_are_ordered_by_schedule_date_descending(): void
    {
        // Buat beberapa task dengan tanggal berbeda
        $earlierTask = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->subDays(2)->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'completed',
            'priority'       => 'low',
        ]);

        $laterTask = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDays(3)->toDateString(),
            'schedule_time'  => '10:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'scheduled',
            'priority'       => 'high',
        ]);

        $response = $this->actingAs($this->staff)
            ->getJson('/api/my-tasks');

        $response->assertStatus(200);

        $dates = collect($response->json('data'))->pluck('schedule_date')->toArray();
        $sorted = collect($dates)->sortDesc()->values()->toArray();

        $this->assertEquals($sorted, array_values($dates));
    }
}