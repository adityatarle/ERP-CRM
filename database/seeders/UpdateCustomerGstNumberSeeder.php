<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class UpdateCustomerGstNumberSeeder extends Seeder
{
    /**
     * Generate a random GST number following the 15-character format.
     *
     * @return string
     */
    private function generateGstNumber(): string
    {
        // List of valid Indian state codes (partial list for brevity)
        $stateCodes = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '27'];

        // Characters for PAN number
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        // 1. State code (2 digits)
        $stateCode = $stateCodes[array_rand($stateCodes)];

        // 2. PAN number (10 characters: 5 letters, 4 digits, 1 letter)
        $pan = '';
        // 5 random letters
        for ($i = 0; $i < 5; $i++) {
            $pan .= $letters[rand(0, 25)];
        }
        // 4 random digits
        for ($i = 0; $i < 4; $i++) {
            $pan .= $digits[rand(0, 9)];
        }
        // 1 random letter
        $pan .= $letters[rand(0, 25)];

        // 3. Entity code (1 digit, usually '1' for simplicity)
        $entityCode = '1';

        // 4. Checksum (1 character, using 'Z' for simplicity)
        $checksum = 'Z';

        // 5. Additional checksum digit (1 random alphanumeric)
        $alphanumeric = $letters . $digits;
        $additionalChecksum = $alphanumeric[rand(0, strlen($alphanumeric) - 1)];

        // Combine all parts
        return $stateCode . $pan . $entityCode . $checksum . $additionalChecksum;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all customers with a random GST number
        Customer::whereNull('gst_number')->get()->each(function ($customer) {
            $customer->update(['gst_number' => $this->generateGstNumber()]);
        });
    }
}