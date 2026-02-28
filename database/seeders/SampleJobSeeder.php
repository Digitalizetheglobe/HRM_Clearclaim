<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\User;

class SampleJobSeeder extends Seeder
{
    public function run()
    {
        $user = User::find(2);
        
        if ($user) {
            $job = new Job();
            $job->title = 'Software Developer';
            $job->branch = 1;
            $job->category = 1;
            $job->skill = 'PHP,Laravel,MySQL';
            $job->position = '2';
            $job->status = 'active';
            $job->start_date = now();
            $job->end_date = now()->addDays(30);
            $job->description = 'We are looking for a talented Software Developer to join our team. You will be responsible for developing and maintaining web applications using Laravel framework.';
            $job->requirement = '3+ years of experience in PHP/Laravel, Strong knowledge of MySQL, Experience with RESTful APIs, Bachelor degree in Computer Science or related field';
            $job->code = uniqid();
            $job->created_by = 2;
            $job->save();
            
            echo "Sample job created successfully for user ID 2\n";
        } else {
            echo "User with ID 2 not found\n";
        }
    }
}
