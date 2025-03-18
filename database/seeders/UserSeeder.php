<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Добавьте эту строку

class UserSeeder extends Seeder
{
    const ADMIN_PASSWORD = 'admin@admin';
    const ADMIN_EMAIL = 'admin@admin';

    const USER_PASSWORD = 'test@test.ru';
    const USER_EMAIL = 'test@test.ru';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка таблицы перед вставкой
      //   DB::table('users')->truncate();
 
        // Проверка существования пользователя
        $userAdmin = User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Admin', // Добавьте значение для 'name'
                'password' => Hash::make(self::ADMIN_PASSWORD)
            ]
        );
 
        $userAdmin->assignRole(Role::ROLE_ADMIN);
    }
}