<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// (Records supplier deliveries and attaches delivery note)
class CreateDeliveriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpo_id')->nullable()->constrained('lpos')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('delivered_at')->nullable();
            $table->string('status')->default('pending'); // pending, partial, complete
            $table->text('delivery_note')->nullable(); // file name or text
            $table->timestamps();

            $table->index(['lpo_id','supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
}
