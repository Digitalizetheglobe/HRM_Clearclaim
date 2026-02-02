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
        // Update existing offboarding stages to add HR Approval as step 3
        // and shift all existing steps after step 2 down by 1
        
        $stages = DB::table('offboarding_stages')->get();
        
        if ($stages->isNotEmpty()) {
            // First, update all existing stages with order >= 3 to increment their order by 1
            DB::table('offboarding_stages')
                ->where('order', '>=', 3)
                ->increment('order');
            
            // Now insert the HR Approval stage as order 3 for each created_by user
            $createdByUsers = $stages->pluck('created_by')->unique();
            
            foreach ($createdByUsers as $createdBy) {
                // Check if HR Approval stage already exists for this user
                $existingHRApproval = DB::table('offboarding_stages')
                    ->where('created_by', $createdBy)
                    ->where('title', 'HR Approval')
                    ->first();
                
                if (!$existingHRApproval) {
                    DB::table('offboarding_stages')->insert([
                        'title' => 'HR Approval',
                        'order' => 3,
                        'created_by' => $createdBy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove HR Approval stages
        DB::table('offboarding_stages')
            ->where('title', 'HR Approval')
            ->delete();
        
        // Decrement order of all stages with order > 3
        DB::table('offboarding_stages')
            ->where('order', '>', 3)
            ->decrement('order');
    }
};
