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
            $table->string('vendor');
            $table->string('currency');
            $table->float('amount');
            $table->string('reference_no')->nullable();
            $table->string('approval_code')->nullable();
            $table->dateTime('transaction_at');
            $table->boolean('created_on_firefly')->default(false);
            $table->foreignIdFor(\App\Models\Vendor::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
