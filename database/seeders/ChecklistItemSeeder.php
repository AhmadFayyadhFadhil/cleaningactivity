<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChecklistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Floor category
            [
                'item_code' => 'FLOOR001',
                'item_name' => 'Sapu lantai',
                'category' => 'Floor',
                'description' => 'Sapu seluruh lantai area',
                'instruction' => 'Gunakan sapu basah untuk hasil optimal, mulai dari sudut terjauh',
                'status' => 'Active',
            ],
            [
                'item_code' => 'FLOOR002',
                'item_name' => 'Pel lantai',
                'category' => 'Floor',
                'description' => 'Pel lantai dengan cairan pembersih',
                'instruction' => 'Gunakan cairan pembersih dilarutkan dalam air, pel sampai terasa bersih',
                'status' => 'Active',
            ],
            [
                'item_code' => 'FLOOR003',
                'item_name' => 'Cek keretakan lantai',
                'category' => 'Floor',
                'description' => 'Periksa apakah ada keretakan atau kerusakan pada lantai',
                'instruction' => 'Inspeksi visual menyeluruh, catat temuan di form laporan',
                'status' => 'Active',
            ],
            // Window category
            [
                'item_code' => 'WINDOW001',
                'item_name' => 'Bersihkan kaca jendela',
                'category' => 'Window',
                'description' => 'Bersihkan kaca jendela dari debu dan noda',
                'instruction' => 'Gunakan cairan pembersih kaca dan lap tidak berbulu',
                'status' => 'Active',
            ],
            [
                'item_code' => 'WINDOW002',
                'item_name' => 'Bersihkan frame jendela',
                'category' => 'Window',
                'description' => 'Bersihkan frame jendela dari debu',
                'instruction' => 'Gunakan kain lembab, pastikan tidak ada debu yang tersisa',
                'status' => 'Active',
            ],
            // Furniture category
            [
                'item_code' => 'FURN001',
                'item_name' => 'Lap meja kerja',
                'category' => 'Furniture',
                'description' => 'Lap semua meja kerja untuk menghilangkan debu',
                'instruction' => 'Gunakan kain lap lembab, pastikan permukaan kering setelahnya',
                'status' => 'Active',
            ],
            [
                'item_code' => 'FURN002',
                'item_name' => 'Lap kursi',
                'category' => 'Furniture',
                'description' => 'Lap semua kursi di area',
                'instruction' => 'Gunakan kain lembab, perhatian khusus pada bagian sering disentuh',
                'status' => 'Active',
            ],
            [
                'item_code' => 'FURN003',
                'item_name' => 'Cek kondisi furniture',
                'category' => 'Furniture',
                'description' => 'Periksa kondisi furniture untuk kerusakan atau kecacatan',
                'instruction' => 'Inspeksi visual, catat kerusakan yang ditemukan',
                'status' => 'Active',
            ],
            // Restroom category
            [
                'item_code' => 'REST001',
                'item_name' => 'Cuci wastafel',
                'category' => 'Restroom',
                'description' => 'Cuci dan bersihkan wastafel',
                'instruction' => 'Gunakan cairan pembersih dan sikat, pastikan mengkilap',
                'status' => 'Active',
            ],
            [
                'item_code' => 'REST002',
                'item_name' => 'Bersihkan toilet',
                'category' => 'Restroom',
                'description' => 'Bersihkan dan disinfeksi toilet',
                'instruction' => 'Gunakan cairan desinfektan khusus toilet, sikat menyeluruh',
                'status' => 'Active',
            ],
            [
                'item_code' => 'REST003',
                'item_name' => 'Pel lantai kamar mandi',
                'category' => 'Restroom',
                'description' => 'Pel lantai kamar mandi dengan antiseptik',
                'instruction' => 'Gunakan cairan antiseptik, pastikan lantai kering dan tidak licin',
                'status' => 'Active',
            ],
            [
                'item_code' => 'REST004',
                'item_name' => 'Refill perlengkapan (tisu, sabun)',
                'category' => 'Restroom',
                'description' => 'Pastikan perlengkapan kamar mandi cukup',
                'instruction' => 'Refill tisu, sabun, dan hand sanitizer sesuai kebutuhan',
                'status' => 'Active',
            ],
            // Wall & Ceiling category
            [
                'item_code' => 'WALL001',
                'item_name' => 'Cek dinding noda/kotor',
                'category' => 'Wall & Ceiling',
                'description' => 'Periksa dinding untuk noda atau area kotor',
                'instruction' => 'Inspeksi visual, bersihkan noda dengan kain lembab',
                'status' => 'Active',
            ],
            [
                'item_code' => 'WALL002',
                'item_name' => 'Cek langit-langit debu',
                'category' => 'Wall & Ceiling',
                'description' => 'Periksa langit-langit dari debu atau sarang laba-laba',
                'instruction' => 'Inspeksi visual, bersihkan dengan duster pada stick panjang',
                'status' => 'Active',
            ],
            // Trash & Recycling category
            [
                'item_code' => 'TRASH001',
                'item_name' => 'Kosongkan tempat sampah',
                'category' => 'Trash & Recycling',
                'description' => 'Kosongkan semua tempat sampah di area',
                'instruction' => 'Ganti dengan kantong sampah baru, limbah dibuang ke tempat pembuangan akhir',
                'status' => 'Active',
            ],
            [
                'item_code' => 'TRASH002',
                'item_name' => 'Lap/bersihkan tempat sampah',
                'category' => 'Trash & Recycling',
                'description' => 'Bersihkan bagian luar tempat sampah',
                'instruction' => 'Gunakan kain lembab, pastikan tidak ada sisa sampah yang menempel',
                'status' => 'Active',
            ],
        ];

        foreach ($items as $item) {
            ChecklistItem::create($item);
        }
    }
}
