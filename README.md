# 📚 Notas API · Laravel + Sanctum

[![CI](https://github.com/robertofilho0/laravel-sanctum-auth/actions/workflows/ci.yml/badge.svg)](https://github.com/robertofilho0/laravel-sanctum-auth/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/php-8.3%20%7C%208.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/laravel-12-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

API REST em **Laravel 12** para gestão de alunos e notas do 2º e 3º ano, com autenticação por token via **Laravel Sanctum**. Professores lançam notas por bimestre, a média ponderada é calculada pela aplicação, e há boletim por aluno e relatório da turma.

## ✨ Funcionalidades

- **Autenticação com Sanctum**: login com token de acesso (com expiração), `me`, logout e logout de todos os dispositivos
- **Proteção contra força bruta**: rate limit de 5 tentativas por minuto por e-mail + IP
- **Perfis**: professor (gerencia alunos e notas) e **admin** (gerencia usuários) via Gate
- **Alunos**: CRUD com filtros por série, busca por nome/matrícula e paginação
- **Notas por bimestre**: uma por aluno em cada bimestre, com **média ponderada calculada no model** (parcial × 1 + bimestral × 2) / 3
- **Boletim** com os 4 bimestres, média geral e situação
- **Relatório da turma**: média, maior e menor nota, aprovados e abaixo da média, com filtros por série e bimestre
- Boas práticas do Laravel: **Form Requests**, **API Resources**, **Route Model Binding**, rotas aninhadas *shallow*, **Factories** e **Seeders**
- Respostas de erro sempre em JSON nas rotas `/api/*`
- **Testes** com PHPUnit (unitários e de feature), **Laravel Pint** e **CI** em PHP 8.3 e 8.4, com um job que roda as migrations num **MySQL 8**

## 🧱 Stack

PHP 8.3+ · Laravel 12 · Sanctum 4 · MySQL 8 ou SQLite · PHPUnit 11 · Pint · Docker

## 📁 Estrutura

```
app/
├── Http/
│   ├── Controllers/Api/   # Auth, Aluno, Nota, Relatorio e User
│   ├── Requests/          # validação (LoginRequest, AlunoRequest, NotaRequest, StoreUserRequest)
│   └── Resources/         # formatação das respostas
├── Models/                # User, Aluno e Nota (cálculo da média no evento saving)
└── Providers/AppServiceProvider.php   # Gate de admin e rate limiters
config/escola.php          # média de aprovação
database/
├── factories/ migrations/ seeders/
routes/api.php
tests/
├── Feature/               # Auth, Aluno, Nota e UserAdmin
└── Unit/                  # cálculo da média
```

## 🚀 Como rodar

### Localmente (SQLite, sem instalar banco)

```bash
git clone https://github.com/robertofilho0/laravel-sanctum-auth.git
cd laravel-sanctum-auth
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

### Com Docker (MySQL)

```bash
export APP_KEY=$(php artisan key:generate --show)   # ou gere em qualquer projeto Laravel
docker compose up --build
docker compose exec app php artisan db:seed
```

A API fica disponível em `http://localhost:8000/api`.

**Usuários do seed** (senha `Senha123`): `admin@escola.com` (admin) e `beth@escola.com` (professora).

## 📚 Endpoints

Todas as rotas, exceto `/login`, exigem `Authorization: Bearer <token>`.

| Método   | Rota                           | Descrição                                   |
| -------- | ------------------------------ | ------------------------------------------- |
| `POST`   | `/api/login`                   | Gera o token de acesso                      |
| `GET`    | `/api/me`                      | Usuário autenticado                         |
| `POST`   | `/api/logout`                  | Revoga o token atual                        |
| `POST`   | `/api/logout-all`              | Revoga todos os tokens do usuário           |
| `GET`    | `/api/users`                   | Lista usuários (admin)                      |
| `POST`   | `/api/users`                   | Cria professor/admin (admin)                |
| `DELETE` | `/api/users/{id}`              | Remove usuário (admin)                      |
| `GET`    | `/api/alunos`                  | Lista (`serie`, `busca`, `per_page`)        |
| `POST`   | `/api/alunos`                  | Cadastra aluno                              |
| `GET`    | `/api/alunos/{id}`             | Detalhes com notas                          |
| `PUT`    | `/api/alunos/{id}`             | Atualiza aluno                              |
| `DELETE` | `/api/alunos/{id}`             | Remove aluno e notas                        |
| `GET`    | `/api/alunos/{id}/boletim`     | Boletim do aluno                            |
| `GET`    | `/api/alunos/{id}/notas`       | Notas do aluno                              |
| `POST`   | `/api/alunos/{id}/notas`       | Lança nota do bimestre                      |
| `PUT`    | `/api/notas/{id}`              | Atualiza nota (recalcula a média)           |
| `DELETE` | `/api/notas/{id}`              | Remove nota                                 |
| `GET`    | `/api/relatorios/turma`        | Estatísticas (`serie`, `bimestre`)          |

### Exemplos

```bash
# Login
curl -X POST http://localhost:8000/api/login -H "Accept: application/json" \
  -d email=beth@escola.com -d password=Senha123

# Lançar nota
curl -X POST http://localhost:8000/api/alunos/1/notas \
  -H "Authorization: Bearer <TOKEN>" -H "Accept: application/json" \
  -d bimestre=1 -d nota_parcial=8 -d nota_bimestral=9
```

```json
{
  "data": {
    "id": 1,
    "aluno_id": 1,
    "bimestre": 1,
    "nota_parcial": 8,
    "nota_bimestral": 9,
    "media": 8.67,
    "situacao": "aprovado",
    "lancada_por": "Professora Beth"
  }
}
```

## 🧪 Testes e qualidade

```bash
composer test          # PHPUnit (SQLite em memória)
composer lint          # Laravel Pint em modo verificação
composer format        # aplica o Pint
```

## 📄 Licença

MIT.

---

Feito por [Roberto Filho](https://github.com/robertofilho0) · [LinkedIn](https://www.linkedin.com/in/roberto-filho-393b372b2/)
