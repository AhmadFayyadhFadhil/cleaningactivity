<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\CleaningVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $staff;
    protected User $unauthorized;
    protected Area $area;
    protected CleaningSchedule $completedSchedule;
    protected CleaningSchedule $scheduledTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->create();
        $this->supervisor->givePermissionTo('verify-schedules');

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo('view-tasks');

        $this->unauthorized = User::factory()->create();

        $this->area = Area::create([
            'area_code'          => 'AREA-VER',
            'area_name'          => 'Cafeteria',
            'location'           => 'Building D, Lantai 1',
            'floor'              => '1',
            'building'           => 'Building D',
            'pic_user_id'        => $this->supervisor->id,
            'status'             => 'active',
            'schedule_frequency' => 'daily',
        ]);

        // Jadwal yang sudah selesai dikerjakan (menunggu verifikasi)
        $this->completedSchedule = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->toDateString(),
            'schedule_time'  => '07:00',
            'assigned_to_id' => $this->staff->id,
            'supervisor_id'  => $this->supervisor->id,
            'status'         => 'completed',
            'priority'       => 'high',
        ]);

        // Jadwal yang belum selesai (bukan kandidat verifikasi)
        $this->scheduledTask = CleaningSchedule::create([
            'area_id'        => $this->area->id,
            'schedule_date'  => now()->addDay()->toDateString(),
            'schedule_time'  => '08:00',
            'assigned_to_id' => $this->staff->id,
            'supervisor_id'  => $this->supervisor->id,
            'status'         => 'scheduled',
            'priority'       => 'medium',
        ]);
    }

    // ─── PENDING VERIFICATIONS ────────────────────────────────────────────────

    public function test_supervisor_can_view_pending_verifications(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->getJson('/api/verifications/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'area',
                        'assigned_to',
                        'status',
                        'schedule_date',
                    ],
                ],
            ]);

        // Hanya schedule yang completed dan belum diverifikasi
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($this->completedSchedule->id, $ids);
        $this->assertNotContains($this->scheduledTask->id, $ids);
    }

    public function test_pending_verifications_excludes_already_verified(): void
    {
        // Verifikasi completedSchedule dulu
        CleaningVerification::create([
            'schedule_id'         => $this->completedSchedule->id,
            'verified_by_id'      => $this->supervisor->id,
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson('/api/verifications/pending');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($this->completedSchedule->id, $ids);
    }

    public function test_unauthenticated_user_cannot_view_pending_verifications(): void
    {
        $this->getJson('/api/verifications/pending')
            ->assertStatus(401);
    }

    public function test_user_without_permission_cannot_view_pending_verifications(): void
    {
        $this->actingAs($this->unauthorized)
            ->getJson('/api/verifications/pending')
            ->assertStatus(403);
    }

    // ─── APPROVE ──────────────────────────────────────────────────────────────

    public function test_supervisor_can_approve_verification(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/approve", [
                'notes'    => 'Kebersihan sudah memenuhi standar',
                'findings' => null,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'verification_id',
                    'schedule',
                ],
            ]);

        $this->assertDatabaseHas('cleaning_verifications', [
            'schedule_id'         => $this->completedSchedule->id,
            'verified_by_id'      => $this->supervisor->id,
            'verification_status' => 'approved',
        ]);
    }

    public function test_approve_without_notes_is_valid(): void
    {
        // notes nullable pada approve
        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/approve", []);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_cannot_approve_already_verified_schedule(): void
    {
        // Verifikasi pertama
        CleaningVerification::create([
            'schedule_id'         => $this->completedSchedule->id,
            'verified_by_id'      => $this->supervisor->id,
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        // Coba approve lagi
        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/approve", [
                'notes' => 'Double approve',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_approve_returns_404_for_nonexistent_schedule(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson('/api/verifications/99999/approve', ['notes' => 'Test'])
            ->assertStatus(404);
    }

    public function test_user_without_permission_cannot_approve(): void
    {
        $this->actingAs($this->unauthorized)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/approve", [])
            ->assertStatus(403);
    }

    // ─── REJECT ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_reject_verification(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/reject", [
                'notes'    => 'Sudut-sudut ruangan masih kotor',
                'findings' => 'Ditemukan debu di balik pintu',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('cleaning_verifications', [
            'schedule_id'         => $this->completedSchedule->id,
            'verification_status' => 'rejected',
        ]);

        // Status jadwal kembali ke in-progress setelah ditolak
        $this->assertDatabaseHas('cleaning_schedules', [
            'id'     => $this->completedSchedule->id,
            'status' => 'in-progress',
        ]);
    }

    public function test_reject_requires_notes(): void
    {
        // notes wajib saat reject
        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_cannot_reject_already_verified_schedule(): void
    {
        CleaningVerification::create([
            'schedule_id'         => $this->completedSchedule->id,
            'verified_by_id'      => $this->supervisor->id,
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $response = $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/reject", [
                'notes' => 'Mencoba tolak yang sudah diapprove',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_reject_returns_404_for_nonexistent_schedule(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson('/api/verifications/99999/reject', ['notes' => 'Test'])
            ->assertStatus(404);
    }

    public function test_user_without_permission_cannot_reject(): void
    {
        $this->actingAs($this->unauthorized)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/reject", [
                'notes' => 'Unauthorized reject attempt',
            ])
            ->assertStatus(403);
    }

    // ─── INTEGRITY ────────────────────────────────────────────────────────────

    public function test_approve_creates_exactly_one_verification_record(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/approve", [
                'notes' => 'Approved',
            ]);

        $this->assertEquals(
            1,
            CleaningVerification::where('schedule_id', $this->completedSchedule->id)->count()
        );
    }

    public function test_reject_stores_verified_by_current_user(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson("/api/verifications/{$this->completedSchedule->id}/reject", [
                'notes' => 'Masih kurang bersih',
            ]);

        $this->assertDatabaseHas('cleaning_verifications', [
            'schedule_id'    => $this->completedSchedule->id,
            'verified_by_id' => $this->supervisor->id,
        ]);
    }
}