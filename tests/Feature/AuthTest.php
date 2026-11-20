<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_retorna_token_e_usuario(): void
    {
        User::factory()->create(['email' => 'beth@escola.com', 'password' => 'Senha123']);

        $response = $this->postJson('/api/login', ['email' => 'BETH@escola.com', 'password' => 'Senha123']);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_at', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonMissingPath('user.password');
    }

    public function test_login_com_credenciais_invalidas_retorna_401(): void
    {
        User::factory()->create(['email' => 'beth@escola.com', 'password' => 'Senha123']);

        $this->postJson('/api/login', ['email' => 'beth@escola.com', 'password' => 'errada'])->assertUnauthorized();
        $this->postJson('/api/login', ['email' => 'ninguem@escola.com', 'password' => 'Senha123'])->assertUnauthorized();
    }

    public function test_login_valida_campos(): void
    {
        $this->postJson('/api/login', ['email' => 'nao-e-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_bloqueia_apos_varias_tentativas(): void
    {
        User::factory()->create(['email' => 'beth@escola.com']);

        foreach (range(1, 5) as $_) {
            $this->postJson('/api/login', ['email' => 'beth@escola.com', 'password' => 'errada']);
        }

        $this->postJson('/api/login', ['email' => 'beth@escola.com', 'password' => 'errada'])->assertTooManyRequests();
    }

    public function test_token_da_acesso_ao_me_e_logout_revoga(): void
    {
        $user = User::factory()->create(['password' => 'Senha123']);
        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Senha123'])->json('access_token');

        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('data.email', $user->email);
        $this->withToken($token)->postJson('/api/logout')->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_all_revoga_todos_os_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('celular');
        $token = $user->createToken('notebook')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout-all')->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_rotas_protegidas_exigem_autenticacao(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/alunos')->assertUnauthorized();
    }

    public function test_rota_inexistente_retorna_json(): void
    {
        $this->getJson('/api/nada')->assertNotFound()->assertJsonPath('message', 'Rota não encontrada.');
    }
}
