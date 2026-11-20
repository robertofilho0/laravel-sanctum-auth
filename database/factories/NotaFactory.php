<?php

namespace Database\Factories;

use App\Models\Aluno;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nota>
 */
class NotaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'aluno_id' => Aluno::factory(),
            'bimestre' => 1,
            'nota_parcial' => fake()->randomFloat(1, 0, 10),
            'nota_bimestral' => fake()->randomFloat(1, 0, 10),
        ];
    }
}
