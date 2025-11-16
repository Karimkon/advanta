<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requisition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lpo_id')->nullable()->constrained('lpos')->nullOnDelete();
            $table->string('type')->default('material'); // material, labour, tax, misc
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('incurred_on')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('unpaid'); // unpaid, paid, pending_approval
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id','type','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
}
