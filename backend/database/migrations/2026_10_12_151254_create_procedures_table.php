<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::ensureVectorExtensionExists();

        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('titre');
            $table->string('service');
            $table->string('gravite');
            $table->json('alertes');
            $table->date('revision');
            $table->text('contenu');
            $table->vector('embedding', dimensions: 1024);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
