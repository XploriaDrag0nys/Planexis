<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FirstSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('roles')->insert([
            [
                'name'       => 'Admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Chef de projet',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Contributeur',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.fr',
            'password' => bcrypt('admin'),
            'trigramme' => 'ADM',
            'email_verified_at' => $now,
            'is_admin' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
