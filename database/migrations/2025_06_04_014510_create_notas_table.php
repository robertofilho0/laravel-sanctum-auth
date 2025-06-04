<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->decimal('nota_parcial', 5, 2);   // peso 1
            $table->decimal('nota_bimestral', 5, 2); // peso 2
            $table->decimal('media', 5, 2)->nullable(); // calculada: (nota_parcial + 2 * nota_bimestral) / 3
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
