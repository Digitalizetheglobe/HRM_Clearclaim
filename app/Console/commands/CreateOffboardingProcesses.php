<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resignation;
use App\Models\OffboardingProcess;
use App\Models\OffboardingStage;

class CreateOffboardingProcesses extends Command
{
    protected $signature = 'offboarding:create-processes';
    protected $description = 'Create offboarding processes for existing resignations';

    public function handle()
    {
        $this->info('Creating offboarding processes for existing resignations...');
        
        $resignations = Resignation::all();
        $created = 0;
        $skipped = 0;
        
        foreach ($resignations as $resignation) {
            // Check if process already exists
            $existing = OffboardingProcess::where('resignation_id', $resignation->id)
                ->where('created_by', $resignation->created_by)
                ->first();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            // Get first stage
            $firstStage = OffboardingStage::where('created_by', $resignation->created_by)
                ->orderBy('order', 'asc')
                ->first();
            
            if (!$firstStage) {
                $this->warn("No stages found for creator ID: {$resignation->created_by}");
                continue;
            }
            
            // Determine which stage to use based on resignation status
            $targetStage = $firstStage;
            if ($resignation->status == 'approved') {
                // Move to Access Removal Checklist (order 2)
                $targetStage = OffboardingStage::where('created_by', $resignation->created_by)
                    ->where('order', 2)
                    ->first() ?? $firstStage;
            }
            
            OffboardingProcess::create([
                'employee_id' => $resignation->employee_id,
                'resignation_id' => $resignation->id,
                'stage' => $targetStage->id,
                'created_by' => $resignation->created_by,
            ]);
            
            $created++;
        }
        
        $this->info("Created {$created} processes, skipped {$skipped} existing ones.");
        return 0;
    }
}


