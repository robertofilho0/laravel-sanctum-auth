<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = ['aluno_id', 'nota_parcial', 'nota_bimestral', 'media'];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    // Cálculo automático
    public static function calcularMedia($nota1, $nota2)
    {
        return ($nota1 * 1 + $nota2 * 2) / 3;
    }
}
