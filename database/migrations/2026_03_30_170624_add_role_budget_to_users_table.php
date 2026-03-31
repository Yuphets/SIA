<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin'])->default('user');
            }
            if (!Schema::hasColumn('users', 'budget_limit')) {
                $table->decimal('budget_limit', 12, 2)->default(10000);
            }
            if (!Schema::hasColumn('users', 'last_budget_change')) {
                $table->timestamp('last_budget_change')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'budget_limit', 'last_budget_change']);
        });
    }
};
