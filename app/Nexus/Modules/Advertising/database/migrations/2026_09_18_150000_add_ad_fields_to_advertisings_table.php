<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('slot')->nullable()->after('title');
            $table->string('type')->default('html')->after('slot');
            $table->text('content_html')->nullable()->after('type');
            $table->string('image')->nullable()->after('content_html');
            $table->string('target_url')->nullable()->after('image');
            $table->boolean('is_active')->default(true)->after('target_url');
            $table->unsignedInteger('max_impressions')->nullable()->after('is_active');
            $table->unsignedInteger('max_impressions_per_visitor')->nullable()->after('max_impressions');
            $table->unsignedInteger('impressions_count')->default(0)->after('max_impressions_per_visitor');
            $table->unsignedInteger('clicks_count')->default(0)->after('impressions_count');
            $table->timestamp('starts_at')->nullable()->after('clicks_count');
            $table->timestamp('ends_at')->nullable()->after('starts_at');

            $table->index(['slot', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->dropIndex(['slot', 'is_active']);
            $table->dropColumn([
                'title', 'slot', 'type', 'content_html', 'image', 'target_url', 'is_active',
                'max_impressions', 'max_impressions_per_visitor', 'impressions_count', 'clicks_count',
                'starts_at', 'ends_at',
            ]);
        });
    }
};
