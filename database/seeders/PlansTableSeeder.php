<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if Free Plan exists, if not create it
        if (!Plan::where('name', 'Free Plan')->exists()) {
            Plan::create(
                [
                    'name' => 'Free Plan',
                    'price' => 0,
                    'duration' => 'Lifetime',
                    'max_users' => 1,
                    'max_employees' => 5,
                    'storage_limit' => 1024,
                    'enable_chatgpt' => 'on',
                    'image' => 'free_plan.png',
                ]
            );
        }

        // Add Basic Plan if it doesn't exist
        if (!Plan::where('name', 'Basic Plan')->exists()) {
            Plan::create(
                [
                    'name' => 'Basic Plan',
                    'price' => 9.99,
                    'duration' => 'month',
                    'max_users' => 5,
                    'max_employees' => 20,
                    'storage_limit' => 5120,
                    'enable_chatgpt' => 'on',
                    'description' => 'Perfect for small businesses',
                    'image' => 'basic_plan.png',
                ]
            );
        }

        // Add Premium Plan if it doesn't exist
        if (!Plan::where('name', 'Premium Plan')->exists()) {
            Plan::create(
                [
                    'name' => 'Premium Plan',
                    'price' => 29.99,
                    'duration' => 'month',
                    'max_users' => 20,
                    'max_employees' => 100,
                    'storage_limit' => 10240,
                    'enable_chatgpt' => 'on',
                    'description' => 'Ideal for growing companies',
                    'image' => 'premium_plan.png',
                ]
            );
        }

        // Add Enterprise Plan if it doesn't exist
        if (!Plan::where('name', 'Enterprise Plan')->exists()) {
            Plan::create(
                [
                    'name' => 'Enterprise Plan',
                    'price' => 99.99,
                    'duration' => 'month',
                    'max_users' => -1,
                    'max_employees' => -1,
                    'storage_limit' => -1,
                    'enable_chatgpt' => 'on',
                    'description' => 'Unlimited everything for large organizations',
                    'image' => 'enterprise_plan.png',
                ]
            );
        }
    }
}
