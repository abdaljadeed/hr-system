<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
|--------------------------------------------------------------------------
| Demo Login Credentials (password: "password")
|--------------------------------------------------------------------------
| Admin        admin@hr.test
| HR Manager   hr@hr.test
| Team Lead    lead@hr.test
| Employee     employee@hr.test
|
| Run with:  php artisan migrate:fresh --seed
|        or: php artisan db:seed --class=DemoSeeder
*/
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
