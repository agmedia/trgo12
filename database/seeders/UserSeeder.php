<?php

namespace Database\Seeders;

use App\Models\Back\User\UserDetail;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- Clean user-related data every time this seeder runs ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('model_has_roles')->truncate();
        // If you later attach direct user->permissions:
        // DB::table('model_has_permissions')->truncate();
        
        UserDetail::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // --- Ensure roles exist (PermissionSeeder should have run) ---
        // If not sure, you can uncomment next line:
        // $this->call(PermissionSeeder::class);
        
        $faker = Faker::create();
        
        // --- Your 2 MASTER users (matching your data; role switched to 'master') ---
        $masters = [
            [
                'name'   => 'Filip Jankoski',
                'email'  => 'filip@agmedia.hr',
                'pass'   => 'majamaja001',
                'detail' => [
                    'fname'   => 'Filip',
                    'lname'   => 'Jankoski',
                    'address' => 'Kovačića 23',
                    'zip'     => '44320',
                    'city'    => 'Kutina',
                    'state'   => null,
                    'phone'   => null,
                    'avatar'  => 'media/avatars/default_avatar.png',
                    'bio'     => 'Lorem ipsum...',
                    'social'  => '790117367', // kept as in your SQL
                    'role'    => 'master',
                    'status'  => 1,
                ],
            ],
            [
                'name'   => 'Tomislav Jureša',
                'email'  => 'tomislav@agmedia.hr',
                'pass'   => 'bakanal',
                'detail' => [
                    'fname'   => 'Tomislav',
                    'lname'   => 'Jureša',
                    'address' => 'Malešnica bb',
                    'zip'     => '10000',
                    'city'    => 'Zagreb',
                    'state'   => null,
                    'phone'   => null,
                    'avatar'  => 'media/avatars/default_avatar.png',
                    'bio'     => 'Lorem ipsum...',
                    'social'  => '',
                    'role'    => 'master',
                    'status'  => 1,
                ],
            ],
        ];
        
        foreach ($masters as $m) {
            $user = User::create([
                'name'     => $m['name'],
                'email'    => $m['email'],
                'password' => Hash::make($m['pass']),
            ]);
            $user->syncRoles(['master']);
            
            UserDetail::create(array_merge($m['detail'], [
                'user_id' => $user->id,
            ]));
        }
        
        // --- Helper to create random users of a given role ---
        $makeUsers = function (string $role, int $count) use ($faker) {
            for ($i = 0; $i < $count; $i++) {
                $fname = $faker->firstName();
                $lname = $faker->lastName();
                $email = Str::lower(Str::slug($fname.'.'.$lname)).'+'.Str::random(6).'@example.com';
                
                $user = User::create([
                    'name'     => trim("$fname $lname"),
                    'email'    => $email,
                    'password' => Hash::make('password'), // dev only
                ]);
                $user->syncRoles([$role]);
                
                UserDetail::create([
                    'user_id' => $user->id,
                    'fname'   => $fname,
                    'lname'   => $lname,
                    'address' => $faker->streetAddress(),
                    'zip'     => $faker->postcode(),
                    'city'    => $faker->city(),
                    'state'   => $faker->state(),
                    'phone'   => $faker->phoneNumber(),
                    'avatar'  => 'media/avatars/default_avatar.png',
                    'bio'     => $faker->sentence(10),
                    'social'  => null,
                    'role'    => $role,
                    'status'  => $role === 'customer' ? 1 : (int) $faker->boolean(85),
                ]);
            }
        };
        
        // --- Admins (2), Managers (3), Editors (5) ---
        $makeUsers('admin',   2);
        $makeUsers('manager', 3);
        $makeUsers('editor',  5);
        
        // --- Customers (N via config/env; default 100) ---
        $customerCount = (int) (config('seeder.customers', env('SEED_CUSTOMERS', 100)));
        $makeUsers('customer', $customerCount);
    }
}
