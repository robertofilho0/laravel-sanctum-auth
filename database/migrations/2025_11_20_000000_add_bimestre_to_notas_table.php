<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->unsignedTinyInteger('bimestre')->default(1)->after('aluno_id');
            $table->text('observacao')->nullable()->after('media');
            $table->foreignId('lancada_por')->nullable()->after('observacao')->constrained('users')->nullOnDelete();
            $table->unique(['aluno_id', 'bimestre']);
        });
    }

    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            // No MySQL o índice único também atende a FK de aluno_id: cria um índice próprio antes de removê-lo
            $table->index('aluno_id');
            $table->dropUnique(['aluno_id', 'bimestre']);
            $table->dropConstrainedForeignId('lancada_por');
            $table->dropColumn(['bimestre', 'observacao']);
        });
    }
};
