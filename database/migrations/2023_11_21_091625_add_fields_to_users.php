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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_active')->default(0);
            $table->boolean("is_community_admin")->default(0);
            $table->string("account_balance")->default(0);
            $table->foreignId("community_id")->nullable()->constrained('communities')->onDelete('cascade');
            $table->string("pin")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('is_active');
            $table->dropColumn('pin');
        });
    }
};
