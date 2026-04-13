<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Roles')->upsert([
            [
                'Id' => 1,
                'RoleName' => 'Admin',
                'Description' => 'Quản trị viên hệ thống - có toàn quyền',
                'IsDeleted' => 0,
                'CreatedBy' => null,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => '2026-03-12 11:32:58.0067113',
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ],
            [
                'Id' => 2,
                'RoleName' => 'Member',
                'Description' => 'Thành viên - quyền xem cơ bản',
                'IsDeleted' => 0,
                'CreatedBy' => null,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => '2026-03-12 11:32:58.1964270',
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ],
            [
                'Id' => 3,
                'RoleName' => 'Kinh doanh',
                'Description' => 'Kinh doanh hệ thống',
                'IsDeleted' => 0,
                'CreatedBy' => 6,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => '2026-03-14 06:34:35.5833980',
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ],
        ], ['Id'], [
            'RoleName',
            'Description',
            'IsDeleted',
            'CreatedBy',
            'UpdatedBy',
            'DeleteBy',
            'CreatedAt',
            'UpdatedAt',
            'DeleteAt',
        ]);
    }
}
