<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('title_i18n')->nullable();
            $table->text('description')->nullable();
            $table->integer('count')->nullable();
            $table->string('email')->nullable();
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('event_date')->nullable();
            $table->dateTime('event_at')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
            $table->unsignedBigInteger('primary_permission_id')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('show_extra')->default(false);
            $table->string('extra_field')->nullable();
            $table->json('notes_json')->nullable();
            $table->string('api_only_note')->nullable();
            $table->string('locked_field')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demos');
    }
};
