<?php 

namespace Src\Database\Seeders;

use GTG\MVC\DB\Seeder;
use Src\Models\UserType;

class UserTypeSeeder extends Seeder 
{
    public function run(): void 
    {
        UserType::insertMany([
            ['name_sing' => 'Administrador', 'name_plur' => 'Administradores'],
            ['name_sing' => 'ADM', 'name_plur' => 'ADMs'],
            ['name_sing' => 'Conferente', 'name_plur' => 'Conferentes']
        ]);
    }
}