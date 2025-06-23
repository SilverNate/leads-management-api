<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{

    public function up(): void
    {
        Schema::connection('pgsql_logging')->create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->text('error_message');
            $table->string('endpoint');
            $table->integer('status_code')->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }


    public function down(): void
    {
        Schema::connection('pgsql_logging')->dropIfExists('error_logs');
    }
};
