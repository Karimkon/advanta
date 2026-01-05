<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Equipment name (e.g., "Dump Truck", "Excavator")
            $table->string('model'); // Model/Spec (e.g., "Caterpillar CAT 320D")
            $table->string('category'); // dump_truck, excavator, crane, etc.
            $table->text('description')->nullable(); // Detailed description and use case
            $table->decimal('value', 15, 2)->default(0); // Equipment purchase/current value
            $table->date('purchase_date')->nullable(); // Date of purchase
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'needs_repair'])->default('good');
            $table->string('location')->nullable(); // Current physical location
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete(); // Currently assigned project
            $table->json('images')->nullable(); // Array of image paths
            $table->string('serial_number')->nullable(); // Unique serial/identification number
            $table->foreignId('added_by')->constrained('users'); // User who added the equipment
            $table->enum('status', ['active', 'inactive', 'maintenance', 'disposed'])->default('active');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('category');
            $table->index('status');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
