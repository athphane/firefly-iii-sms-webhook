<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('card')->nullable();
            $table->dateTime('transaction_at')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('approval_code')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('message');

            $table->foreignIdFor(\App\Models\Vendor::class)->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('firefly_transaction_id')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
