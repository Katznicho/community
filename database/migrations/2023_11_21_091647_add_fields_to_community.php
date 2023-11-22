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
        Schema::table('communities', function (Blueprint $table) {
            //
            $table->boolean('is_active')->default(1);
            $table->string("account_name")->nullable();
            $table->string("account_number")->nullable();
            $table->string("account_balance")->default(0);
            $table->foreignId("leader_id")->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            //

        });
    }
};
