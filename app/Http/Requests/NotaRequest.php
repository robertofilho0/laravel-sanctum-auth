<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotaRequest extends FormRequest
{
    public function rules(): array
    {
        $obrigatorio = $this->isMethod('POST') ? 'required' : 'sometimes';
        $alunoId = $this->route('aluno')?->id ?? $this->route('nota')?->aluno_id;

        return [
            'bimestre' => [
                $obrigatorio,
                'integer',
                'between:1,4',
                Rule::unique('notas')
                    ->where('aluno_id', $alunoId)
                    ->ignore($this->route('nota')?->id),
            ],
            'nota_parcial' => [$obrigatorio, 'numeric', 'between:0,10'],
            'nota_bimestral' => [$obrigatorio, 'numeric', 'between:0,10'],
            'observacao' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bimestre.unique' => 'Este aluno já possui nota lançada neste bimestre.',
        ];
    }
}
