<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlunoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastra_aluno(): void
    {
        $this->actingAsProfessor();

        $this->postJson('/api/alunos', ['nome' => 'Ana Lima', 'matricula' => '2025001', 'serie' => '3'])
            ->assertCreated()
            ->assertJsonPath('data.nome', 'Ana Lima');

        $this->assertDatabaseHas('alunos', ['matricula' => '2025001']);
    }

    public function test_valida_matricula_unica_e_serie(): void
    {
        $this->actingAsProfessor();
        Aluno::factory()->create(['matricula' => '2025001']);

        $this->postJson('/api/alunos', ['nome' => 'Ana', 'matricula' => '2025001', 'serie' => '1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['matricula', 'serie']);
    }

    public function test_lista_com_filtros_e_paginacao(): void
    {
        $this->actingAsProfessor();
        Aluno::factory()->create(['nome' => 'Ana Lima', 'serie' => '3']);
        Aluno::factory()->create(['nome' => 'Bruno Costa', 'serie' => '2']);
        Aluno::factory()->create(['nome' => 'Carla Dias', 'serie' => '3']);

        $this->getJson('/api/alunos?serie=3')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/alunos?busca=bruno')->assertJsonPath('data.0.nome', 'Bruno Costa');
        $this->getJson('/api/alunos?per_page=2')->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 3);
    }

    public function test_atualiza_mantendo_a_propria_matricula(): void
    {
        $this->actingAsProfessor();
        $aluno = Aluno::factory()->create(['matricula' => '2025001']);

        $this->putJson("/api/alunos/{$aluno->id}", ['nome' => 'Novo Nome', 'matricula' => '2025001'])
            ->assertOk()
            ->assertJsonPath('data.nome', 'Novo Nome');
    }

    public function test_remove_aluno_e_suas_notas(): void
    {
        $this->actingAsProfessor();
        $nota = Nota::factory()->create();

        $this->deleteJson("/api/alunos/{$nota->aluno_id}")->assertNoContent();

        $this->assertDatabaseMissing('notas', ['id' => $nota->id]);
    }

    public function test_aluno_inexistente_retorna_404(): void
    {
        $this->actingAsProfessor();

        $this->getJson('/api/alunos/999')->assertNotFound()->assertJsonPath('message', 'Registro não encontrado.');
    }
}
