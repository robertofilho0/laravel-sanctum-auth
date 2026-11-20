<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_lanca_nota_calculando_a_media(): void
    {
        $professor = $this->actingAsProfessor();
        $aluno = Aluno::factory()->create();

        $this->postJson("/api/alunos/{$aluno->id}/notas", [
            'bimestre' => 1,
            'nota_parcial' => 8,
            'nota_bimestral' => 9,
            'media' => 0,
        ])
            ->assertCreated()
            ->assertJsonPath('data.media', 8.67)
            ->assertJsonPath('data.situacao', 'aprovado')
            ->assertJsonPath('data.lancada_por', $professor->name);
    }

    public function test_valida_faixa_das_notas_e_bimestre(): void
    {
        $this->actingAsProfessor();
        $aluno = Aluno::factory()->create();

        $this->postJson("/api/alunos/{$aluno->id}/notas", ['bimestre' => 5, 'nota_parcial' => 11, 'nota_bimestral' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['bimestre', 'nota_parcial', 'nota_bimestral']);
    }

    public function test_impede_duas_notas_no_mesmo_bimestre(): void
    {
        $this->actingAsProfessor();
        $nota = Nota::factory()->create(['bimestre' => 2]);

        $this->postJson("/api/alunos/{$nota->aluno_id}/notas", ['bimestre' => 2, 'nota_parcial' => 5, 'nota_bimestral' => 5])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bimestre');
    }

    public function test_atualiza_recalculando_a_media(): void
    {
        $this->actingAsProfessor();
        $nota = Nota::factory()->create(['nota_parcial' => 8, 'nota_bimestral' => 9]);

        $this->patchJson("/api/notas/{$nota->id}", ['nota_bimestral' => 6])
            ->assertOk()
            ->assertJsonPath('data.media', 6.67)
            ->assertJsonPath('data.situacao', 'aprovado');
    }

    public function test_remove_nota(): void
    {
        $this->actingAsProfessor();
        $nota = Nota::factory()->create();

        $this->deleteJson("/api/notas/{$nota->id}")->assertNoContent();
        $this->assertModelMissing($nota);
    }

    public function test_boletim_com_bimestres_e_situacao(): void
    {
        $this->actingAsProfessor();
        $aluno = Aluno::factory()->create();
        $aluno->notas()->create(['bimestre' => 1, 'nota_parcial' => 8, 'nota_bimestral' => 9]);
        $aluno->notas()->create(['bimestre' => 2, 'nota_parcial' => 4, 'nota_bimestral' => 5]);

        $this->getJson("/api/alunos/{$aluno->id}/boletim")
            ->assertOk()
            ->assertJsonCount(4, 'bimestres')
            ->assertJsonPath('bimestres.0.nota.media', 8.67)
            ->assertJsonPath('bimestres.2.nota', null)
            ->assertJsonPath('bimestres_lancados', 2)
            ->assertJsonPath('media_geral', 6.67)
            ->assertJsonPath('situacao', 'aprovado');
    }

    public function test_boletim_sem_notas(): void
    {
        $this->actingAsProfessor();
        $aluno = Aluno::factory()->create();

        $this->getJson("/api/alunos/{$aluno->id}/boletim")
            ->assertJsonPath('media_geral', null)
            ->assertJsonPath('situacao', 'sem_notas');
    }

    public function test_relatorio_da_turma(): void
    {
        $this->actingAsProfessor();
        $terceiro = Aluno::factory()->create(['serie' => '3']);
        $segundo = Aluno::factory()->create(['serie' => '2']);
        $terceiro->notas()->create(['bimestre' => 1, 'nota_parcial' => 10, 'nota_bimestral' => 10]);
        $segundo->notas()->create(['bimestre' => 1, 'nota_parcial' => 3, 'nota_bimestral' => 3]);

        $this->getJson('/api/relatorios/turma')
            ->assertOk()
            ->assertJsonPath('total_notas', 2)
            ->assertJsonPath('media_turma', 6.5)
            ->assertJsonPath('aprovados', 1)
            ->assertJsonPath('abaixo_da_media', 1);

        $this->getJson('/api/relatorios/turma?serie=3')->assertJsonPath('media_turma', 10);
    }
}
