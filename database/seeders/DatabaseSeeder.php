<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Dados de demonstração. Senha de todos os usuários: Senha123
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@escola.com',
            'password' => 'Senha123',
        ]);

        User::factory()->create([
            'name' => 'Professora Beth',
            'email' => 'beth@escola.com',
            'password' => 'Senha123',
        ]);

        Aluno::factory(12)->create()->each(function (Aluno $aluno) use ($admin) {
            foreach (range(1, fake()->numberBetween(1, 4)) as $bimestre) {
                $aluno->notas()->create([
                    'bimestre' => $bimestre,
                    'nota_parcial' => fake()->randomFloat(1, 3, 10),
                    'nota_bimestral' => fake()->randomFloat(1, 3, 10),
                    'lancada_por' => $admin->id,
                ]);
            }
        });
    }
}
