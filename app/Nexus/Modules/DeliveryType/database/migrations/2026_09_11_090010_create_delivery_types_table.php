<?php

use App\Nexus\Modules\Delivery\Models\Delivery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_types', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Delivery::class)
                ->nullable()
                ->constrained('deliveries')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->json('title');
            $table->string('key');
            $table->boolean('publish')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_types');
    }
};
