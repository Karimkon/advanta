<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up()
    {
        // Update existing site_manager users to engineer
        User::where('role', 'site_manager')->update(['role' => 'engineer']);
        
        // Also update the default role in the schema
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer')->change(); // Change default to engineer
        });
    }

    public function down()
    {
        // Revert back if needed
        User::where('role', 'engineer')->update(['role' => 'site_manager']);
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('site_manager')->change();
        });
    }
};