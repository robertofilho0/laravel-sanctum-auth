<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_somente_admin_gerencia_usuarios(): void
    {
        $this->actingAsProfessor();

        $this->getJson('/api/users')->assertForbidden();
        $this->postJson('/api/users', [])->assertForbidden();
    }

    public function test_admin_cria_professor(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/users', ['name' => 'Prof. Carlos', 'email' => 'CARLOS@escola.com', 'password' => 'Senha123'])
            ->assertCreated()
            ->assertJsonPath('data.email', 'carlos@escola.com')
            ->assertJsonPath('data.is_admin', false);
    }

    public function test_valida_senha_e_email_unico(): void
    {
        $this->actingAsAdmin();
        User::factory()->create(['email' => 'beth@escola.com']);

        $this->postJson('/api/users', ['name' => 'Beth', 'email' => 'beth@escola.com', 'password' => '123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_admin_remove_usuario_mas_nao_a_si_mesmo(): void
    {
        $admin = $this->actingAsAdmin();
        $professor = User::factory()->create();

        $this->deleteJson("/api/users/{$professor->id}")->assertNoContent();
        $this->deleteJson("/api/users/{$admin->id}")->assertUnprocessable();
    }
}
