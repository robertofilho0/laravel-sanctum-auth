<?php

namespace App\Http\Requests;

use App\Models\Aluno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlunoRequest extends FormRequest
{
    public function rules(): array
    {
        $aluno = $this->route('aluno');
        $obrigatorio = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'nome' => [$obrigatorio, 'string', 'min:3', 'max:100'],
            'matricula' => [
                $obrigatorio,
                'string',
                'regex:/^\d{4,20}$/',
                Rule::unique('alunos', 'matricula')->ignore($aluno?->id),
            ],
            'serie' => [$obrigatorio, Rule::in(Aluno::SERIES)],
        ];
    }

    public function messages(): array
    {
        return [
            'matricula.regex' => 'A matrícula deve conter de 4 a 20 dígitos.',
            'matricula.unique' => 'Esta matrícula já está cadastrada.',
            'serie.in' => 'A série deve ser 2 ou 3.',
        ];
    }
}
