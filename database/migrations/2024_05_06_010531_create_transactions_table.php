<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('vendor')->nullable();
            $table->string('currency')->nullable();
            $table->float('amount')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('approval_code')->nullable();
            $table->dateTime('transaction_at')->nullable();
            $table->unsignedBigInteger('firefly_transaction_id')->nullable();
            $table->foreignIdFor(\App\Models\Vendor::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
