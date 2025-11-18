<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lpo_received_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpo_id')->constrained()->onDelete('cascade');
            $table->foreignId('lpo_item_id')->constrained('lpo_items')->onDelete('cascade');
            $table->decimal('quantity_ordered', 10, 2);
            $table->decimal('quantity_received', 10, 2);
            $table->string('condition')->default('good'); // good, damaged, partial, etc.
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpo_received_items');
    }
};