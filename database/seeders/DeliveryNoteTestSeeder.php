<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use App\Models\Customer;

class DeliveryNoteTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test customer if not exists
        $customer = Customer::firstOrCreate(
            ['name' => 'Test Customer'],
            [
                'email' => 'test@customer.com',
                'phone' => '1234567890',
                'address' => 'Test Address',
                'gst_number' => 'TEST123456789',
                'default_credit_days' => 30,
            ]
        );

        // Create test products if not exist
        $product1 = Product::firstOrCreate(
            ['name' => 'Test Product 1'],
            [
                'price' => 100.00,
                'stock' => 50,
                'category' => 'Test Category',
                'subcategory' => 'Test Subcategory',
                'hsn' => '12345678',
                'item_code' => 'TP001',
            ]
        );

        $product2 = Product::firstOrCreate(
            ['name' => 'Test Product 2'],
            [
                'price' => 200.00,
                'stock' => 30,
                'category' => 'Test Category',
                'subcategory' => 'Test Subcategory',
                'hsn' => '87654321',
                'item_code' => 'TP002',
            ]
        );

        // Create test delivery notes
        $deliveryNote1 = DeliveryNote::firstOrCreate(
            ['delivery_note_number' => 'DN-TEST001'],
            [
                'customer_id' => $customer->id,
                'ref_no' => 'REF001',
                'purchase_number' => 'PO001',
                'purchase_date' => now()->subDays(5),
                'delivery_date' => now()->subDays(3),
                'gst_type' => 'CGST',
                'cgst' => 9.00,
                'sgst' => 9.00,
                'igst' => null,
                'description' => 'Test delivery note 1',
                'notes' => 'Test notes for delivery note 1',
                'contact_person' => 'John Doe',
                'is_invoiced' => false,
            ]
        );

        $deliveryNote2 = DeliveryNote::firstOrCreate(
            ['delivery_note_number' => 'DN-TEST002'],
            [
                'customer_id' => $customer->id,
                'ref_no' => 'REF002',
                'purchase_number' => 'PO002',
                'purchase_date' => now()->subDays(4),
                'delivery_date' => now()->subDays(2),
                'gst_type' => 'IGST',
                'cgst' => null,
                'sgst' => null,
                'igst' => 18.00,
                'description' => 'Test delivery note 2',
                'notes' => 'Test notes for delivery note 2',
                'contact_person' => 'Jane Smith',
                'is_invoiced' => false,
            ]
        );

        // Create delivery note items
        if (!$deliveryNote1->items()->exists()) {
            DeliveryNoteItem::create([
                'delivery_note_id' => $deliveryNote1->id,
                'product_id' => $product1->id,
                'quantity' => 5,
                'price' => 100.00,
                'discount' => 10.00,
                'itemcode' => 'TP001',
                'secondary_itemcode' => 'SEC001',
            ]);
        }

        if (!$deliveryNote2->items()->exists()) {
            DeliveryNoteItem::create([
                'delivery_note_id' => $deliveryNote2->id,
                'product_id' => $product2->id,
                'quantity' => 3,
                'price' => 200.00,
                'discount' => 5.00,
                'itemcode' => 'TP002',
                'secondary_itemcode' => 'SEC002',
            ]);
        }

        $this->command->info('Delivery note test data seeded successfully!');
        $this->command->info("Created delivery notes: {$deliveryNote1->delivery_note_number}, {$deliveryNote2->delivery_note_number}");
    }
}