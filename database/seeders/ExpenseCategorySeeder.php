<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultCategories = [
            ['name' => 'Travel', 'description' => 'Travel related expenses'],
            ['name' => 'Food', 'description' => 'Food and meal expenses'],
            ['name' => 'Accommodation', 'description' => 'Hotel and accommodation expenses'],
            ['name' => 'Office Supplies', 'description' => 'Office supplies and stationery'],
            ['name' => 'Client Visit', 'description' => 'Expenses related to client visits'],
            ['name' => 'Internet / Mobile', 'description' => 'Internet and mobile phone expenses'],
            ['name' => 'Company-Specific', 'description' => 'Company specific expenses'],
            ['name' => 'Other', 'description' => 'Other miscellaneous expenses'],
        ];

        // Get all company users to create categories for each
        $companies = User::where('type', 'company')->get();

        foreach ($companies as $company) {
            foreach ($defaultCategories as $category) {
                ExpenseCategory::updateOrCreate(
                    [
                        'name' => $category['name'],
                        'created_by' => $company->id,
                    ],
                    [
                        'description' => $category['description'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
