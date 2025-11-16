<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->foreignId('lpo_id')->nullable()->constrained('lpos')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method')->nullable(); // bank_transfer, cash, mobile_money
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->decimal('amount', 15, 2);
            $table->date('paid_on')->nullable();
            $table->text('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}
