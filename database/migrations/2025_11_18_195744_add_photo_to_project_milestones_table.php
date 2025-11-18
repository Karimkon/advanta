<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoToProjectMilestonesTable extends Migration
{
    public function up()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('progress_notes');
            $table->text('photo_caption')->nullable()->after('photo_path');
        });
    }

    public function down()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'photo_caption']);
        });
    }
}