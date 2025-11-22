<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_polymorphic_columns_to_expenses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('notes');
            $table->string('reference_type')->nullable()->after('reference_id');
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['reference_id', 'reference_type']);
            $table->dropColumn(['reference_id', 'reference_type']);
        });
    }
};