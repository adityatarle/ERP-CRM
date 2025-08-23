<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliveryNote;
use App\Models\Product;

class ValidateDeliveryNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery-notes:validate {--fix : Fix any issues found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate delivery notes for consistency and fix any issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Validating delivery notes...');
        
        $issues = [];
        $fixed = 0;

        // Check for delivery notes with missing financial details
        $deliveryNotes = DeliveryNote::with(['items.product'])->get();
        
        foreach ($deliveryNotes as $deliveryNote) {
            $deliveryNoteIssues = $this->validateDeliveryNote($deliveryNote);
            
            if (!empty($deliveryNoteIssues)) {
                $issues[] = [
                    'delivery_note' => $deliveryNote->delivery_note_number,
                    'issues' => $deliveryNoteIssues
                ];
                
                if ($this->option('fix')) {
                    $this->fixDeliveryNote($deliveryNote, $deliveryNoteIssues);
                    $fixed++;
                }
            }
        }

        // Check for stock inconsistencies
        $stockIssues = $this->checkStockConsistency();
        if (!empty($stockIssues)) {
            $issues[] = [
                'type' => 'stock_inconsistency',
                'issues' => $stockIssues
            ];
        }

        if (empty($issues)) {
            $this->info('✅ All delivery notes are valid!');
            return 0;
        }

        $this->warn('⚠️  Found ' . count($issues) . ' issue(s):');
        
        foreach ($issues as $issue) {
            if (isset($issue['delivery_note'])) {
                $this->error("Delivery Note: {$issue['delivery_note']}");
            } else {
                $this->error("Type: {$issue['type']}");
            }
            
            foreach ($issue['issues'] as $problem) {
                $this->line("  - {$problem}");
            }
        }

        if ($this->option('fix') && $fixed > 0) {
            $this->info("✅ Fixed {$fixed} issue(s)");
        }

        return 1;
    }

    /**
     * Validate a single delivery note
     */
    private function validateDeliveryNote(DeliveryNote $deliveryNote): array
    {
        $issues = [];

        // Check if already invoiced
        if ($deliveryNote->is_invoiced) {
            $issues[] = 'Already converted to invoice';
            return $issues;
        }

        // Check financial details
        if (empty($deliveryNote->gst_type)) {
            $issues[] = 'Missing GST type';
        } elseif ($deliveryNote->gst_type === 'CGST') {
            if (empty($deliveryNote->cgst) || empty($deliveryNote->sgst)) {
                $issues[] = 'Missing CGST or SGST values for CGST type';
            }
        } elseif ($deliveryNote->gst_type === 'IGST') {
            if (empty($deliveryNote->igst)) {
                $issues[] = 'Missing IGST value for IGST type';
            }
        }

        // Check items
        if ($deliveryNote->items->isEmpty()) {
            $issues[] = 'No items found';
        }

        // Check stock availability
        foreach ($deliveryNote->items as $item) {
            if ($item->product && $item->product->stock < $item->quantity) {
                $issues[] = "Insufficient stock for product {$item->product->name} (Available: {$item->product->stock}, Required: {$item->quantity})";
            }
        }

        return $issues;
    }

    /**
     * Check stock consistency across delivery notes
     */
    private function checkStockConsistency(): array
    {
        $issues = [];
        
        // This would check if stock calculations are consistent
        // For now, just return empty array
        return $issues;
    }

    /**
     * Fix issues in a delivery note
     */
    private function fixDeliveryNote(DeliveryNote $deliveryNote, array $issues): void
    {
        $this->line("Fixing issues in delivery note {$deliveryNote->delivery_note_number}...");
        
        foreach ($issues as $issue) {
            if (str_contains($issue, 'Missing GST type')) {
                $deliveryNote->update(['gst_type' => 'CGST']);
                $this->line("  ✅ Set default GST type to CGST");
            }
            
            if (str_contains($issue, 'Missing CGST or SGST values')) {
                $deliveryNote->update(['cgst' => 9, 'sgst' => 9]);
                $this->line("  ✅ Set default CGST and SGST to 9%");
            }
            
            if (str_contains($issue, 'Missing IGST value')) {
                $deliveryNote->update(['igst' => 18]);
                $this->line("  ✅ Set default IGST to 18%");
            }
        }
    }
}