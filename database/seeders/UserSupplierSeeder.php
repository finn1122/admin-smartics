<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSupplierSeeder extends Seeder
{
    public function run()
    {
        // Insertar un usuario
        $user = User::create([
            'name' => 'mauro',
            'email' => 'mauroivaning@gmail.com',
            'password' => Hash::make('123456'), // Recuerda cambiar la contraseña
        ]);

        // Insertar un proveedor
        $supplier = Supplier::create([
            'name' => 'CVA',
            'address' => '',
        ]);

        $this->command->info('Usuario y proveedor creados exitosamente.');
    }
}
