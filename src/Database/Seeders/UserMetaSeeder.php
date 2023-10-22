<?php 

namespace Src\Database\Seeders;

use GTG\MVC\DB\Seeder;
use Src\Models\UserMeta;

class UserMetaSeeder extends Seeder 
{
    public function run(): void 
    {
        UserMeta::insertMany([
            [
                'usu_id' => 3, 
                'meta' => UserMeta::KEY_REGISTRATION_NUMBER,
                'value' => 1234
            ]
        ]);
    }
}