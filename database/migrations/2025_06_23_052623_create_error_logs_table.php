<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration is intended to be run on the 'pgsql_logging' connection.
     */
    public function up(): void
    {
        // Use the 'pgsql_logging' connection for this schema operation
        Schema::connection('pgsql_logging')->create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->text('error_message');
            $table->string('endpoint');
            $table->integer('status_code')->nullable();
            // Use timestamp as a specific column name, not Laravel's default created_at
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use the 'pgsql_logging' connection for dropping the table
        Schema::connection('pgsql_logging')->dropIfExists('error_logs');
    }
};