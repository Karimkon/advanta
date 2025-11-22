// New Migration: create_subcontractors_and_labor_tables
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Subcontractors table
        Schema::create('subcontractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('specialization'); // e.g., Pavers, Electrical, Plumbing
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Project subcontractors (pivot table with contract details)
        Schema::create('project_subcontractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->string('work_description');
            $table->decimal('contract_amount', 12, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'terminated'])->default('active');
            $table->text('terms')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'subcontractor_id']);
        });

        // Subcontractor payments ledger
Schema::create('subcontractor_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_subcontractor_id')->constrained()->onDelete('cascade');
    $table->string('payment_reference');
    $table->date('payment_date');
    $table->decimal('amount', 12, 2);
    $table->enum('payment_type', ['advance', 'progress', 'final', 'retention']);
    $table->string('description');
    $table->foreignId('paid_by')->constrained('users')->onDelete('cascade');
    $table->string('payment_method')->default('bank_transfer');
    $table->string('reference_number')->nullable();
    $table->text('notes')->nullable();
    $table->json('attachments')->nullable();
    $table->timestamps();
    
    // Custom short index name to avoid MySQL 64-character limit
    $table->index(['payment_date', 'project_subcontractor_id'], 'sub_pay_date_sub_id_idx');
});

        // Labor workers table
        Schema::create('labor_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('id_number')->nullable();
            $table->string('role'); // e.g., Mason, Carpenter, Helper
            $table->decimal('daily_rate', 8, 2)->default(0);
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->enum('payment_frequency', ['daily', 'weekly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

       // Labor payments - same issue here
Schema::create('labor_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('labor_worker_id')->constrained()->onDelete('cascade');
    $table->string('payment_reference');
    $table->date('payment_date');
    $table->date('period_start');
    $table->date('period_end');
    $table->decimal('amount', 10, 2);
    $table->integer('days_worked')->default(1);
    $table->string('description');
    $table->foreignId('paid_by')->constrained('users')->onDelete('cascade');
    $table->string('payment_method')->default('cash');
    $table->text('notes')->nullable();
    $table->timestamps();
    
    // Custom short index name
    $table->index(['payment_date', 'labor_worker_id'], 'labor_pay_date_worker_idx');
});
    }

    public function down()
    {
        Schema::dropIfExists('labor_payments');
        Schema::DropIfExists('labor_workers');
        Schema::dropIfExists('subcontractor_payments');
        Schema::dropIfExists('project_subcontractors');
        Schema::dropIfExists('subcontractors');
    }
};