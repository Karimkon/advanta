<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // user_id or client_id
            $table->string('device_token')->unique();
            $table->enum('device_type', ['ios', 'android', 'web'])->default('android');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // morphs() already creates an index on tokenable_type and tokenable_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
