<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BonusTransaction isn't a registered Nexus module (see its model docblock),
 * so this migration lives in Bonus instead — same "lives in whichever
 * module is actually registered" reasoning already applied to
 * OrderShipment's migrations in Phase 7.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamp('available_at');
            $table->timestamp('expires_at')->nullable();
            $table->nullableMorphs('source');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'available_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_transactions');
    }
};
