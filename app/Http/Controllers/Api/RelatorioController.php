<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RelatorioController extends Controller
{
    /** Estatísticas da turma, com filtros opcionais de série e bimestre. */
    public function turma(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'serie' => ['nullable', Rule::in(Aluno::SERIES)],
            'bimestre' => ['nullable', 'integer', 'between:1,4'],
        ]);

        $notas = Nota::query()
            ->when($filtros['bimestre'] ?? null, fn ($q, $b) => $q->where('bimestre', $b))
            ->when($filtros['serie'] ?? null, fn ($q, $s) => $q->whereRelation('aluno', 'serie', $s))
            ->pluck('media');

        $mediaAprovacao = config('escola.media_aprovacao');

        return response()->json([
            'filtros' => ['serie' => $filtros['serie'] ?? null, 'bimestre' => $filtros['bimestre'] ?? null],
            'total_notas' => $notas->count(),
            'media_turma' => $notas->isEmpty() ? null : round($notas->avg(), 2),
            'maior_media' => $notas->max(),
            'menor_media' => $notas->min(),
            'aprovados' => $notas->filter(fn ($m) => $m >= $mediaAprovacao)->count(),
            'abaixo_da_media' => $notas->filter(fn ($m) => $m < $mediaAprovacao)->count(),
        ]);
    }
}
