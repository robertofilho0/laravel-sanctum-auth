<?php

namespace App\Models;

use Database\Factories\AlunoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluno extends Model
{
    /** @use HasFactory<AlunoFactory> */
    use HasFactory;

    public const SERIES = ['2', '3'];

    protected $fillable = ['nome', 'matricula', 'serie'];

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class)->orderBy('bimestre');
    }

    /** Filtra por nome ou matrícula. */
    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        return $query->when($termo, fn (Builder $q) => $q->where(
            fn (Builder $w) => $w->where('nome', 'like', "%{$termo}%")->orWhere('matricula', 'like', "{$termo}%")
        ));
    }

    /** Média das médias bimestrais lançadas (null se não houver notas). */
    public function mediaGeral(): ?float
    {
        $media = $this->notas->avg('media');

        return $media === null ? null : round((float) $media, 2);
    }
}
