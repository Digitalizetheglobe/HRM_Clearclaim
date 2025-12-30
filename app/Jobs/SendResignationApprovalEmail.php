<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ResignationApproved;
use Illuminate\Support\Facades\Mail;

class SendResignationApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $resignation;
    protected $email;

    public function __construct($resignation, $email)
    {
        $this->resignation = $resignation;
        $this->email = $email;
    }

    public function handle()
    {
        try {
            // Set a reasonable timeout just for this job
            set_time_limit(60);

            Mail::to($this->email)->send(new ResignationApproved($this->resignation));
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            \Log::error('Failed to send resignation approval email: ' . $e->getMessage());
            // Don't rethrow - allow the job to complete successfully even if email fails
        }
    }
}