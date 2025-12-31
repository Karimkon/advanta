<?php
/**
 * Fix Script for LPO-20251230-9819 (ID: 41)
 *
 * This script will:
 * 1. Update the requisition status from 'rejected' to 'procurement'
 * 2. Optionally update LPO item prices
 *
 * Run this via: php artisan tinker < fix_lpo_41.php
 * OR access via browser (then delete this file!)
 */

// If running via web browser
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
}

use App\Models\Lpo;
use App\Models\LpoItem;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\DB;

echo "<pre>";
echo "=== LPO Fix Script ===\n\n";

// Find the LPO
$lpo = Lpo::where('lpo_number', 'LPO-20251230-9819')->orWhere('id', 41)->first();

if (!$lpo) {
    echo "ERROR: LPO not found!\n";
    echo "</pre>";
    exit;
}

echo "Found LPO:\n";
echo "- ID: {$lpo->id}\n";
echo "- Number: {$lpo->lpo_number}\n";
echo "- Status: {$lpo->status}\n";
echo "- Total: UGX " . number_format($lpo->total, 2) . "\n";
echo "- Requisition ID: {$lpo->requisition_id}\n\n";

// Find the requisition
$requisition = $lpo->requisition;

if ($requisition) {
    echo "Found Requisition:\n";
    echo "- ID: {$requisition->id}\n";
    echo "- Ref: {$requisition->ref}\n";
    echo "- Status: {$requisition->status}\n";
    echo "- Total: UGX " . number_format($requisition->estimated_total, 2) . "\n\n";
}

// Show LPO items
echo "LPO Items:\n";
foreach ($lpo->items as $item) {
    echo "- {$item->description}: Qty {$item->quantity} x UGX " . number_format($item->unit_price, 2) . " = UGX " . number_format($item->total_price, 2) . "\n";
}
echo "\n";

// Check if we should apply fixes
$applyFix = isset($_GET['apply']) && $_GET['apply'] === 'yes';

if (!$applyFix) {
    echo "=== PREVIEW MODE ===\n";
    echo "Add ?apply=yes to the URL to apply fixes.\n\n";

    echo "Proposed Changes:\n";
    echo "1. Change requisition status from '{$requisition->status}' to 'procurement'\n";
    echo "2. You may need to update item prices manually through the web interface\n\n";

    echo "URL to apply: " . $_SERVER['REQUEST_URI'] . "?apply=yes\n";
} else {
    echo "=== APPLYING FIXES ===\n\n";

    DB::beginTransaction();
    try {
        // Fix 1: Update requisition status to 'procurement'
        if ($requisition && $requisition->status === 'rejected') {
            $oldStatus = $requisition->status;
            $requisition->update(['status' => 'procurement']);
            echo "✓ Requisition status changed from '{$oldStatus}' to 'procurement'\n";
        } else {
            echo "- Requisition status is '{$requisition->status}' (not 'rejected'), skipping status change\n";
        }

        // Fix 2: Ensure LPO is in draft status
        if ($lpo->status !== 'draft') {
            $oldStatus = $lpo->status;
            $lpo->update(['status' => 'draft']);
            echo "✓ LPO status changed from '{$oldStatus}' to 'draft'\n";
        } else {
            echo "- LPO status is already 'draft'\n";
        }

        DB::commit();

        echo "\n=== FIX APPLIED SUCCESSFULLY ===\n";
        echo "\nNext Steps:\n";
        echo "1. Go to Procurement → Requisitions → Find REQ-{$requisition->ref}\n";
        echo "2. Edit the requisition to set item prices\n";
        echo "3. OR go to LPO Management and edit the LPO directly\n";
        echo "4. Then CEO can approve the LPO\n";
        echo "\n⚠️ IMPORTANT: Delete this file after use!\n";

    } catch (\Exception $e) {
        DB::rollBack();
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "</pre>";
