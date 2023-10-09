<?php 

namespace Src\Database\Seeders;

use GTG\MVC\DB\Seeder;
use Src\Models\User;

class UserSeeder extends Seeder 
{
    public function run(): void 
    {
        User::insertMany([
            [
                'utip_id' => User::UT_ADMIN,
                'name' => 'Admin',
                'password' => 'gpa@net12',
                'email' => 'admin@gpabr.com',
                'slug' => 'adm',
                'token' => md5('admin@gpabr.com')
            ],
            [
                'utip_id' => User::UT_ADM,
                'name' => 'ADM 1',
                'password' => 'gpa@net12',
                'email' => 'adm1@gpabr.com',
                'slug' => 'adm-1',
                'token' => md5('adm1@gpabr.com')
            ],
            [
                'utip_id' => User::UT_OPERATOR,
                'name' => 'Operador 1',
                'password' => 'gpa@net12',
                'email' => 'operador1@gpabr.com',
                'slug' => 'operador-1',
                'token' => md5('operador1@gpabr.com')
            ]
        ]);
    }
}