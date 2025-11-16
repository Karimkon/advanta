<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitionApprovalsTable extends Migration
{
    public function up(): void
    {
        Schema::create('requisition_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role'); // operations, procurement, finance, ceo
            $table->string('action'); // approved, rejected, modified
            $table->text('comment')->nullable();
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['requisition_id','role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_approvals');
    }
}
