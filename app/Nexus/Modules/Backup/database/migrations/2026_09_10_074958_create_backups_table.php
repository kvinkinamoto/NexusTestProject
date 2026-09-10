<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('backup_database')->default(true);
            $table->boolean('backup_code')->default(false);
            $table->boolean('backup_storage')->default(false);
            $table->string('backup_cron_expression')->nullable();
            $table->unsignedInteger('max_backups')->default(10);
            $table->string('last_status')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
