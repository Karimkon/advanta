<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressFieldsToProjectMilestonesTable extends Migration
{
    public function up()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->decimal('actual_cost', 15, 2)->nullable()->after('cost_estimate');
            $table->integer('completion_percentage')->default(0)->after('actual_cost');
            $table->text('progress_notes')->nullable()->after('completion_percentage');
        });
    }

    public function down()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn(['actual_cost', 'completion_percentage', 'progress_notes']);
        });
    }
}