<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotaRequest;
use App\Http\Resources\NotaResource;
use App\Models\Aluno;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotaController extends Controller
{
    public function index(Aluno $aluno): AnonymousResourceCollection
    {
        return NotaResource::collection($aluno->notas()->with('professor')->get());
    }

    public function store(NotaRequest $request, Aluno $aluno): JsonResponse
    {
        $nota = $aluno->notas()->create([
            ...$request->validated(),
            'lancada_por' => $request->user()->id,
        ]);

        return (new NotaResource($nota->load('professor')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(NotaRequest $request, Nota $nota): NotaResource
    {
        $nota->update([...$request->validated(), 'lancada_por' => $request->user()->id]);

        return new NotaResource($nota->load('professor'));
    }

    public function destroy(Nota $nota): Response
    {
        $nota->delete();

        return response()->noContent();
    }
}
