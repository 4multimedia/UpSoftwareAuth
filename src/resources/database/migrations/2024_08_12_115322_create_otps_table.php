<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTime('expired_at');
            $table->dateTime('used_at')->nullable();
            $table->string('kind', 32);
            $table->string('email', 32)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('code', 12);

            $table->index(['kind', 'email', 'phone', 'code'], 'auth_otp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
