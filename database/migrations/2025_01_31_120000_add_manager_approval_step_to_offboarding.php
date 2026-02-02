<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing offboarding stages to add Manager Approval as the first step
        // and shift all existing steps down by 1
        
        $stages = DB::table('offboarding_stages')->get();
        
        if ($stages->isNotEmpty()) {
            // First, update all existing stages to increment their order by 1
            DB::table('offboarding_stages')
                ->increment('order');
            
            // Now insert the Manager Approval stage as order 1 for each created_by user
            $createdByUsers = $stages->pluck('created_by')->unique();
            
            foreach ($createdByUsers as $createdBy) {
                DB::table('offboarding_stages')->insert([
                    'title' => 'Manager Approval',
                    'order' => 1,
                    'created_by' => $createdBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Manager Approval stages
        DB::table('offboarding_stages')
            ->where('title', 'Manager Approval')
            ->delete();
        
        // Decrement order of all remaining stages
        DB::table('offboarding_stages')
            ->where('order', '>', 1)
            ->decrement('order');
    }
};
