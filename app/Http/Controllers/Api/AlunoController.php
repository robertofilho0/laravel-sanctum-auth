<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlunoRequest;
use App\Http\Resources\AlunoResource;
use App\Http\Resources\NotaResource;
use App\Models\Aluno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AlunoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->validate([
            'serie' => ['nullable', Rule::in(Aluno::SERIES)],
            'busca' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $alunos = Aluno::query()
            ->when($filtros['serie'] ?? null, fn ($q, $serie) => $q->where('serie', $serie))
            ->busca($filtros['busca'] ?? null)
            ->orderBy('nome')
            ->paginate($filtros['per_page'] ?? 20)
            ->withQueryString();

        return AlunoResource::collection($alunos);
    }

    public function store(AlunoRequest $request): JsonResponse
    {
        $aluno = Aluno::create($request->validated());

        return (new AlunoResource($aluno))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Aluno $aluno): AlunoResource
    {
        return new AlunoResource($aluno->load('notas'));
    }

    public function update(AlunoRequest $request, Aluno $aluno): AlunoResource
    {
        $aluno->update($request->validated());

        return new AlunoResource($aluno);
    }

    public function destroy(Aluno $aluno): Response
    {
        $aluno->delete();

        return response()->noContent();
    }

    /** Boletim do aluno: notas por bimestre, média geral e situação. */
    public function boletim(Aluno $aluno): JsonResponse
    {
        $aluno->load('notas.professor');
        $mediaGeral = $aluno->mediaGeral();
        $mediaAprovacao = config('escola.media_aprovacao');

        $bimestres = collect(range(1, 4))->map(function (int $bimestre) use ($aluno) {
            $nota = $aluno->notas->firstWhere('bimestre', $bimestre);

            return [
                'bimestre' => $bimestre,
                'nota' => $nota ? new NotaResource($nota) : null,
            ];
        });

        return response()->json([
            'aluno' => new AlunoResource($aluno->withoutRelations()),
            'bimestres' => $bimestres,
            'bimestres_lancados' => $aluno->notas->count(),
            'media_geral' => $mediaGeral,
            'media_aprovacao' => $mediaAprovacao,
            'situacao' => match (true) {
                $mediaGeral === null => 'sem_notas',
                $mediaGeral >= $mediaAprovacao => 'aprovado',
                default => 'abaixo_da_media',
            },
        ]);
    }
}
