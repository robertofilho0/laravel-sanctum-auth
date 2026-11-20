<?php

namespace Database\Factories;

use App\Models\Aluno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aluno>
 */
class AlunoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake('pt_BR')->name(),
            'matricula' => (string) fake()->unique()->numberBetween(2025000, 2025999),
            'serie' => fake()->randomElement(Aluno::SERIES),
        ];
    }
}
