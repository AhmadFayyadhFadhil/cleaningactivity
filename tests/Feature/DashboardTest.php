<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\CleaningVerification;
use App\Models\FollowUpTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('view-dashboard');

        $this->staff = User::factory()->create();

        $this->area = Area::create([
            'area_code'          => 'DASH001',
            'area_name'          => 'Lobby',
            'location'           => 'Building A',
            'floor'              => '1',
            'building'           => 'Building A',
            'pic_user_id'        => $this->admin->id,
            'status'             => 'active',
            'schedule_frequency' => 'daily',
        ]);
    }

    // ─── SUMMARY ──────────────────────────────────────────────────────────────

    public function test_admin_can_access_dashboard_summary(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'summary' => [
                        'total_areas',
                        'total_schedules',
                        'completed_schedules',
                        'completion_rate',
                        'pending_verifications',
                        'total_follow_ups',
                        'open_follow_ups',
                    ],
                    'recent_schedules',
                ],
            ]);
    }

    public function test_summary_reflects_actual_data(): void
    {
        // Buat 2 schedule: 1 completed, 1 scheduled
        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'completed',
            'priority'       => 'high',
        ]);

        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDay()->toDateString(),
            'schedule_time'  => '09:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'scheduled',
            'priority'       => 'medium',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $response->assertStatus(200);
        $summary = $response->json('data.summary');

        $this->assertEquals(1, $summary['total_areas']);
        $this->assertEquals(2, $summary['total_schedules']);
        $this->assertEquals(1, $summary['completed_schedules']);
        $this->assertEquals('50%', $summary['completion_rate']);
    }

    public function test_summary_shows_zero_completion_rate_when_no_schedules(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $response->assertStatus(200);
        $this->assertEquals('0%', $response->json('data.summary.completion_rate'));
    }

    public function test_summary_counts_pending_verifications_correctly(): void
    {
        // Schedule completed tapi belum diverifikasi
        $completedSchedule = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '07:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'completed',
            'priority'       => 'high',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $this->assertEquals(1, $response->json('data.summary.pending_verifications'));

        // Setelah diverifikasi, tidak masuk pending lagi
        CleaningVerification::create([
            'schedule_id'         => $completedSchedule->id,
            'verified_by_id'      => $this->admin->id,
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $response2 = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $this->assertEquals(0, $response2->json('data.summary.pending_verifications'));
    }

    public function test_summary_recent_schedules_limited_to_five(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            CleaningSchedule::create([
                'area_id'        => $this->area->id,
                'schedule_date'  => now()->addDays($i)->toDateString(),
                'schedule_time'  => '08:00',
                'assigned_to_id' => $this->staff->id,
                'status'         => 'scheduled',
                'priority'       => 'low',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $recentSchedules = $response->json('data.recent_schedules');
        $this->assertCount(5, $recentSchedules);
    }

    public function test_summary_recent_schedules_structure(): void
    {
        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'scheduled',
            'priority'       => 'medium',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/summary');

        $response->assertJsonStructure([
            'data' => [
                'recent_schedules' => [
                    '*' => [
                        'id',
                        'area',
                        'assigned_to',
                        'status',
                        'date',
                    ],
                ],
            ],
        ]);
    }

    public function test_unauthenticated_user_cannot_access_summary(): void
    {
        $this->getJson('/api/dashboard/summary')
            ->assertStatus(401);
    }

    public function test_user_without_permission_cannot_access_summary(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/api/dashboard/summary')
            ->assertStatus(403);
    }

    // ─── AREA STATUS ──────────────────────────────────────────────────────────

    public function test_admin_can_access_area_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/area-status');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'area_code',
                        'area_name',
                        'building',
                        'floor',
                        'pic',
                        'status',
                        'schedule_frequency',
                        'total_schedules',
                        'completed_schedules',
                        'in_progress_schedules',
                        'completion_rate',
                    ],
                ],
            ]);
    }

    public function test_area_status_calculates_completion_rate_correctly(): void
    {
        // 3 schedules: 2 completed, 1 in-progress
        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '07:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'completed',
            'priority'       => 'high',
        ]);

        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->subDay()->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'completed',
            'priority'       => 'medium',
        ]);

        CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDay()->toDateString(),
            'schedule_time'  => '09:00',
            'assigned_to_id' => $this->staff->id,
            'status'         => 'in-progress',
            'priority'       => 'low',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/area-status');

        $areaData = collect($response->json('data'))
            ->firstWhere('id', $this->area->id);

        $this->assertNotNull($areaData);
        $this->assertEquals(3, $areaData['total_schedules']);
        $this->assertEquals(2, $areaData['completed_schedules']);
        $this->assertEquals(1, $areaData['in_progress_schedules']);
        $this->assertEquals('66.67%', $areaData['completion_rate']);
    }

    public function test_area_status_shows_zero_rate_for_area_with_no_schedules(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/area-status');

        $areaData = collect($response->json('data'))
            ->firstWhere('id', $this->area->id);

        $this->assertEquals('0%', $areaData['completion_rate']);
        $this->assertEquals(0, $areaData['total_schedules']);
    }

    public function test_area_status_includes_all_areas(): void
    {
        $area2 = Area::create([
            'area_code'          => 'DASH002',
            'area_name'          => 'Ruang Rapat',
            'location'           => 'Building B',
            'floor'              => '2',
            'building'           => 'Building B',
            'pic_user_id'        => $this->admin->id,
            'status'             => 'active',
            'schedule_frequency' => 'weekly',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/dashboard/area-status');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($this->area->id, $ids);
        $this->assertContains($area2->id, $ids);
    }

    public function test_unauthenticated_user_cannot_access_area_status(): void
    {
        $this->getJson('/api/dashboard/area-status')
            ->assertStatus(401);
    }

    public function test_user_without_permission_cannot_access_area_status(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/api/dashboard/area-status')
            ->assertStatus(403);
    }
}