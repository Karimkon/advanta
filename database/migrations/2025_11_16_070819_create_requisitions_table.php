<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->nullable()->index();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('urgency')->default('normal'); // low, normal, high, urgent
            $table->string('status')->default('pending'); // pending, operations_approved, procurement, lpo_issued, delivered, closed, rejected
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('attachments')->nullable(); // store filenames or json
            $table->timestamps();

            $table->index(['project_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
}
