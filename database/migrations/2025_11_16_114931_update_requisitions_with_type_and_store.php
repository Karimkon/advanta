<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->string('type')->default('purchase'); // store, purchase
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            
            // Update status options
            $table->string('status')->default('pending')->change();
        });

        // Create store_releases table
        Schema::create('store_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('store_release_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_release_items');
        Schema::dropIfExists('store_releases');
        
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['type', 'store_id']);
        });
    }
};