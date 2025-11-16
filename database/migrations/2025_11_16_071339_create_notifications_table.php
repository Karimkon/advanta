<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->nullable();
            $table->text('data')->nullable(); // json
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->index(['user_id','read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
    }
}
