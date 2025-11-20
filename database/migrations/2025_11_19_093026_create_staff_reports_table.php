<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // daily, weekly
            $table->string('title');
            $table->text('description');
            $table->string('staff_name');
            $table->string('staff_email');
            $table->string('access_code'); // For authentication
            $table->json('attachments')->nullable(); // Store file paths
            $table->date('report_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_reports');
    }
};