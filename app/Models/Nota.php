<?php

namespace App\Models;

use Database\Factories\NotaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    /** @use HasFactory<NotaFactory> */
    use HasFactory;

    public const PESO_PARCIAL = 1;

    public const PESO_BIMESTRAL = 2;

    protected $fillable = ['aluno_id', 'bimestre', 'nota_parcial', 'nota_bimestral', 'observacao', 'lancada_por'];

    protected function casts(): array
    {
        return [
            'bimestre' => 'integer',
            'nota_parcial' => 'float',
            'nota_bimestral' => 'float',
            'media' => 'float',
        ];
    }

    /** A média é sempre recalculada ao salvar, nunca enviada pelo cliente. */
    protected static function booted(): void
    {
        static::saving(function (Nota $nota) {
            $nota->media = self::calcularMedia($nota->nota_parcial, $nota->nota_bimestral);
        });
    }

    public static function calcularMedia(float $parcial, float $bimestral): float
    {
        $total = $parcial * self::PESO_PARCIAL + $bimestral * self::PESO_BIMESTRAL;

        return round($total / (self::PESO_PARCIAL + self::PESO_BIMESTRAL), 2);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lancada_por');
    }
}
