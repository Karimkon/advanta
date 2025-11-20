<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatFieldsToTables extends Migration
{
    public function up()
    {
        // Add VAT fields to payments table
        if (!Schema::hasColumn('payments', 'tax_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('amount');
            });
        }

        if (!Schema::hasColumn('payments', 'vat_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('vat_amount', 10, 2)->default(0)->after('tax_amount');
            });
        }

        // Add VAT fields to lpos table
        if (!Schema::hasColumn('lpos', 'vat_amount')) {
            Schema::table('lpos', function (Blueprint $table) {
                $table->decimal('vat_amount', 10, 2)->default(0)->after('tax');
            });
        }

        // Add VAT fields to lpo_items table
        if (!Schema::hasColumn('lpo_items', 'has_vat')) {
            Schema::table('lpo_items', function (Blueprint $table) {
                $table->boolean('has_vat')->default(false)->after('total_price');
            });
        }

        if (!Schema::hasColumn('lpo_items', 'vat_rate')) {
            Schema::table('lpo_items', function (Blueprint $table) {
                $table->decimal('vat_rate', 5, 2)->default(18.00)->after('has_vat');
            });
        }
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'vat_amount']);
        });

        Schema::table('lpos', function (Blueprint $table) {
            $table->dropColumn('vat_amount');
        });

        Schema::table('lpo_items', function (Blueprint $table) {
            $table->dropColumn(['has_vat', 'vat_rate']);
        });
    }
}