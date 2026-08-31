<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nexus_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('nexus_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('target')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('nexus_module_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('nexus_modules')->onDelete('cascade');
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('nexus_module_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('nexus_modules')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('model')->nullable();
            $table->string('microservice')->nullable();
            $table->string('endpoint')->nullable();
            $table->json('fields')->nullable();
            $table->boolean('async_save')->default(false);
            $table->boolean('is_lazy_load')->default(false);
            $table->boolean('is_display_in_table')->default(false);
            $table->timestamps();
        });

        Schema::create('nexus_user_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module');
            $table->json('visible_columns')->nullable();
            $table->json('filters')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'module']);
        });

    }

    public function down()
    {
        Schema::dropIfExists('nexus_module_relations');
        Schema::dropIfExists('nexus_module_settings');
        Schema::dropIfExists('nexus_plugins');
        Schema::dropIfExists('nexus_modules');
        Schema::dropIfExists('nexus_user_table_preferences');
    }
};
