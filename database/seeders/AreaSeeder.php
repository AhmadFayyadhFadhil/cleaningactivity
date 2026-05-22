<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users - use their IDs
        $users = User::pluck('id')->toArray();
        
        // If no users, just skip
        if (empty($users)) {
            return;
        }

        $areas = [
            [
                'area_code' => 'AREA001',
                'area_name' => 'Ruang Kerja Lantai 1',
                'location' => 'Building A, Floor 1',
                'floor' => 1,
                'building' => 'Building A',
                'pic_user_id' => $users[0] ?? null,
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA002',
                'area_name' => 'Ruang Kerja Lantai 2',
                'location' => 'Building A, Floor 2',
                'floor' => 2,
                'building' => 'Building A',
                'pic_user_id' => $users[1] ?? $users[0],
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA003',
                'area_name' => 'Ruang Kerja Lantai 3',
                'location' => 'Building A, Floor 3',
                'floor' => 3,
                'building' => 'Building A',
                'pic_user_id' => $users[2] ?? $users[0],
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA004',
                'area_name' => 'Kantin',
                'location' => 'Building A, Ground Floor',
                'floor' => 0,
                'building' => 'Building A',
                'pic_user_id' => $users[0] ?? null,
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA005',
                'area_name' => 'Toilet Lantai 1',
                'location' => 'Building A, Floor 1',
                'floor' => 1,
                'building' => 'Building A',
                'pic_user_id' => $users[1] ?? $users[0],
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA006',
                'area_name' => 'Lobby Utama',
                'location' => 'Building A, Ground Floor',
                'floor' => 0,
                'building' => 'Building A',
                'pic_user_id' => $users[2] ?? $users[0],
                'status' => 'Active',
                'schedule_frequency' => 'Daily',
            ],
            [
                'area_code' => 'AREA007',
                'area_name' => 'Ruang Rapat',
                'location' => 'Building B, Floor 1',
                'floor' => 1,
                'building' => 'Building B',
                'pic_user_id' => $users[0] ?? null,
                'status' => 'Active',
                'schedule_frequency' => 'Weekly',
            ],
            [
                'area_code' => 'AREA008',
                'area_name' => 'Area Parkir',
                'location' => 'Building A, Basement',
                'floor' => -1,
                'building' => 'Building A',
                'pic_user_id' => $users[1] ?? $users[0],
                'status' => 'Active',
                'schedule_frequency' => 'Weekly',
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}
