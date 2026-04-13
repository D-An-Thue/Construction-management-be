<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@gmail.com';
        $now = now();

        $personId = DB::table('Persons')
            ->where('Email', $email)
            ->value('Id');

        if ($personId) {
            DB::table('Persons')
                ->where('Id', $personId)
                ->update([
                    'Name' => 'Admin',
                    'Sex' => 0,
                    'Email' => $email,
                    'AvatarUrl' => '',
                    'DateOfBirth' => null,
                    'PhoneNumber' => '',
                    'Address' => '',
                    'Password' => Hash::make('A@a1234567'),
                    'BankID' => '',
                    'BankAccountNumber' => '',
                    'BankName' => '',
                    'IsDeleted' => 0,
                    'UpdatedBy' => 0,
                    'DeleteBy' => null,
                    'UpdatedAt' => $now,
                    'DeleteAt' => null,
                ]);
        } else {
            $personId = DB::table('Persons')->insertGetId([
                'Name' => 'Admin',
                'Sex' => 0,
                'Email' => $email,
                'AvatarUrl' => '',
                'DateOfBirth' => null,
                'PhoneNumber' => '',
                'Address' => '',
                'Password' => Hash::make('A@a1234567'),
                'BankID' => '',
                'BankAccountNumber' => '',
                'BankName' => '',
                'IsDeleted' => 0,
                'CreatedBy' => 0,
                'UpdatedBy' => 0,
                'DeleteBy' => null,
                'CreatedAt' => $now,
                'UpdatedAt' => $now,
                'DeleteAt' => null,
            ]);
        }

        $permissionRows = DB::table('Permissions')
            ->pluck('Id')
            ->map(fn ($permissionId) => [
                'RoleId' => 1,
                'PermissionId' => $permissionId,
            ])
            ->all();

        if ($permissionRows !== []) {
            DB::table('RolePermissions')->insertOrIgnore($permissionRows);
        }

        DB::table('UserRoles')->insertOrIgnore([
            'PersonId' => $personId,
            'RoleId' => 1,
        ]);
    }
}
