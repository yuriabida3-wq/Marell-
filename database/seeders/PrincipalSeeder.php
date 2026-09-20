<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrincipalSeeder extends Seeder {
    public function run(): void {
        $u = User::firstOrCreate(
            ['email' => 'admin@marell.ac.ke'],
            ['name'=>'Principal Marell','password'=>Hash::make('123456'),'phone'=>'254700000001','role_label'=>'principal','active'=>true]
        );
        $u->assignRole('principal');
    }
}
