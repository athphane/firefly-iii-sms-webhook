<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_access_token_regexes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('personal_access_token_id')->nullable()->constrained()->nullOnDelete();
            $table->text('regex');
            $table->boolean('allowed')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_token_regexes');
    }
};
