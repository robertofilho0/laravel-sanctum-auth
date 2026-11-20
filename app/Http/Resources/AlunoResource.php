<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlunoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'serie' => $this->serie,
            'notas' => NotaResource::collection($this->whenLoaded('notas')),
            'created_at' => $this->created_at,
        ];
    }
}
