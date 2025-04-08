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
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('previous_estimation', 10, 2)->after('sprint_length')->nullable()->comment('Previous estimation in work days');
            $table->decimal('actual_effort', 10, 2)->after('previous_estimation')->nullable()->comment('Actual effort spent in work days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['previous_estimation', 'actual_effort']);
        });
    }
};
