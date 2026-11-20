<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'aluno_id' => $this->aluno_id,
            'bimestre' => $this->bimestre,
            'nota_parcial' => $this->nota_parcial,
            'nota_bimestral' => $this->nota_bimestral,
            'media' => $this->media,
            'situacao' => $this->media >= config('escola.media_aprovacao') ? 'aprovado' : 'abaixo_da_media',
            'observacao' => $this->observacao,
            'lancada_por' => $this->whenLoaded('professor', fn () => $this->professor?->name),
            'updated_at' => $this->updated_at,
        ];
    }
}
