
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Appointment Letter')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::GetLogo();
    $settings = \App\Models\Utility::settings();
    $date = date('Y-m-d');
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card mt-5" id="printTable">
            <div class="card-body p-5" id="boxes" style="background: white;">
                
                
                <div class="page-break" style="page-break-after: always; min-height: 900px;">
                    
                    <div class="letter-header mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php if($company_logo): ?>
                                    <img src="<?php echo e($logo . '/' . $company_logo); ?>" 
                                         alt="<?php echo e($settings['company_name'] ?? env('APP_NAME')); ?>" 
                                         style="max-height: 80px; max-width: 200px;">
                                <?php else: ?>
                                    <img src="<?php echo e(asset('storage/uploads/logo/logo.svg')); ?>" 
                                         alt="<?php echo e($settings['company_name'] ?? env('APP_NAME')); ?>" 
                                         style="max-height: 80px; max-width: 200px;">
                                <?php endif; ?>
                            </div>
                            <div class="text-end" style="font-size: 11px; color: #666;">
                                <div><strong>Date:</strong> <?php echo e(\Auth::user()->dateFormat($date)); ?></div>
                            </div>
                        </div>
                        <div style="border-top: 2px solid #000; width: 100%; margin-top: 15px;"></div>
                    </div>

                    
                    <div class="company-address mb-4" style="font-size: 12px; line-height: 1.6;">
                        <div><strong><?php echo e($settings['company_name'] ?? env('APP_NAME')); ?></strong></div>
                        <?php if(!empty($settings['company_address'])): ?>
                            <div><?php echo e($settings['company_address']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($settings['company_city']) || !empty($settings['company_state']) || !empty($settings['company_zipcode'])): ?>
                            <div>
                                <?php if(!empty($settings['company_city'])): ?><?php echo e($settings['company_city']); ?>, <?php endif; ?>
                                <?php if(!empty($settings['company_state'])): ?><?php echo e($settings['company_state']); ?> <?php endif; ?>
                                <?php if(!empty($settings['company_zipcode'])): ?><?php echo e($settings['company_zipcode']); ?><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($settings['company_country'])): ?>
                            <div><?php echo e($settings['company_country']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($settings['company_telephone'])): ?>
                            <div>Phone: <?php echo e($settings['company_telephone']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($settings['company_email'])): ?>
                            <div>Email: <?php echo e($settings['company_email']); ?></div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="employee-address mb-4" style="font-size: 12px;">
                        <div><strong><?php echo e($employees->name); ?></strong></div>
                        <?php if(!empty($employees->address)): ?>
                            <div><?php echo e($employees->address); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($employees->email)): ?>
                            <div>Email: <?php echo e($employees->email); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($employees->phone)): ?>
                            <div>Phone: <?php echo e($employees->phone); ?></div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="subject-line mb-4" style="font-size: 14px; font-weight: bold;">
                        <div>Subject: <u>APPOINTMENT LETTER</u></div>
                    </div>

                    
                    <div class="letter-body" style="font-size: 12px; line-height: 1.8; text-align: justify;">
                        <p>Dear <strong><?php echo e($employees->name); ?></strong>,</p>
                        
                        <p>We are pleased to offer you the position of <strong><?php echo e($employees->designation->name ?? 'N/A'); ?></strong> at <strong><?php echo e($settings['company_name'] ?? env('APP_NAME')); ?></strong>. This appointment letter outlines the terms and conditions of your employment with us.</p>

                        <p>We are confident that your skills, experience, and dedication will be valuable assets to our organization. We look forward to welcoming you to our team and working together to achieve our common goals.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">1. POSITION AND DEPARTMENT</h5>
                        <p>You will be appointed as <strong><?php echo e($employees->designation->name ?? 'N/A'); ?></strong> in the <strong><?php echo e($employees->department->name ?? 'N/A'); ?></strong> department.</p>
                        <?php
                            $branchName = null;
                            if (!empty($employees->branch) && !empty($employees->branch->name)) {
                                $branchName = $employees->branch->name;
                            } elseif (!empty($employees->Branch) && !empty($employees->Branch->name)) {
                                $branchName = $employees->Branch->name;
                            }
                        ?>
                        <?php if($branchName): ?>
                            <p>Your place of work will be at our <strong><?php echo e($branchName); ?></strong> branch.</p>
                        <?php endif; ?>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">2. DATE OF JOINING</h5>
                        <p>Your employment will commence on <strong><?php echo e($employees->company_doj ? \Auth::user()->dateFormat($employees->company_doj) : 'To be decided'); ?></strong>.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">3. COMPENSATION</h5>
                        <p>You will be paid a gross salary of <strong><?php echo e(!empty($employees->salary) ? $settings['site_currency_symbol'] ?? '$' : ''); ?><?php echo e(number_format($employees->salary ?? 0, 2)); ?></strong> per 
                        <?php if(!empty($employees->salaryType)): ?>
                            <strong><?php echo e($employees->salaryType->name); ?></strong>.
                        <?php else: ?>
                            month.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>

                
                <div class="page-break" style="page-break-after: always; min-height: 900px; padding-top: 20px;">
                    <div class="letter-body" style="font-size: 12px; line-height: 1.8; text-align: justify;">
                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 0; margin-bottom: 10px;">4. WORKING HOURS</h5>
                        <p>Your normal working hours will be from <strong><?php echo e($settings['company_start_time'] ?? '09:00'); ?></strong> to <strong><?php echo e($settings['company_end_time'] ?? '18:00'); ?></strong>, 
                        <?php
                            $secs = strtotime($settings['company_start_time'] ?? '09:00') - strtotime("00:00");
                            $result = date("H:i", strtotime($settings['company_end_time'] ?? '18:00') - $secs);
                        ?>
                        with a total of <strong><?php echo e($result); ?></strong> working hours per day, Monday through Friday, unless otherwise specified.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">5. PROBATION PERIOD</h5>
                        <p>You will be on probation for a period of <strong>3 (three) months</strong> from the date of joining. During this period, your performance will be evaluated, and upon successful completion, your employment will be confirmed.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">6. LEAVE ENTITLEMENT</h5>
                        <p>You will be entitled to annual leave as per the company's leave policy. Details regarding leave application and approval procedures will be provided in the employee handbook.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">7. CONFIDENTIALITY</h5>
                        <p>You agree to maintain strict confidentiality regarding all company information, trade secrets, client data, and any proprietary information that you may come across during your employment. This obligation will continue even after the termination of your employment.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">8. TERMINATION</h5>
                        <p>Either party may terminate this employment relationship by giving <strong>30 (thirty) days</strong> written notice or payment in lieu thereof. The company reserves the right to terminate your employment immediately in case of misconduct, breach of company policies, or any other serious violation.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">9. COMPANY POLICIES</h5>
                        <p>You will be required to comply with all company policies, rules, and regulations as may be in force from time to time. A copy of the employee handbook will be provided to you upon joining.</p>

                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">10. OTHER TERMS AND CONDITIONS</h5>
                        <p>Any other terms and conditions of employment not specifically mentioned in this letter will be governed by the company's policies and applicable labor laws.</p>
                    </div>
                </div>

                
                <div class="page-break" style="min-height: 900px; padding-top: 20px;">
                    <div class="letter-body" style="font-size: 12px; line-height: 1.8; text-align: justify;">
                        <h5 style="font-size: 13px; font-weight: bold; margin-top: 0; margin-bottom: 10px;">11. ACCEPTANCE</h5>
                        <p>Please confirm your acceptance of this appointment by signing and returning a copy of this letter within <strong>7 (seven) days</strong> from the date of this letter. Your failure to do so will be considered as non-acceptance of this offer.</p>

                        <p>We are excited about the prospect of you joining our team and contributing to our continued success. We believe that your expertise and enthusiasm will make a significant impact on our organization.</p>

                        <p>If you have any questions or need clarification on any aspect of this appointment letter, please do not hesitate to contact us.</p>

                        <p>We look forward to welcoming you to <strong><?php echo e($settings['company_name'] ?? env('APP_NAME')); ?></strong> and wish you a successful and rewarding career with us.</p>

                        <div style="margin-top: 60px;">
                            <p>Yours sincerely,</p>
                            <br><br>
                            <div style="border-top: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                            <div style="font-weight: bold;">Authorized Signatory</div>
                            <div><?php echo e($settings['company_name'] ?? env('APP_NAME')); ?></div>
                        </div>

                        <div style="margin-top: 80px; border: 2px solid #000; padding: 20px;">
                            <h5 style="font-size: 13px; font-weight: bold; margin-top: 0; margin-bottom: 15px; text-align: center;">ACCEPTANCE OF APPOINTMENT</h5>
                            <p>I, <strong><?php echo e($employees->name); ?></strong>, acknowledge that I have read and understood the terms and conditions of this appointment letter. I accept the position of <strong><?php echo e($employees->designation->name ?? 'N/A'); ?></strong> on the terms and conditions set forth in this letter.</p>
                            
                            <div style="margin-top: 40px;">
                                <div style="margin-bottom: 30px;">
                                    <div style="border-top: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                                    <div><strong>Employee Signature</strong></div>
                                </div>
                                <div>
                                    <div style="border-top: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                                    <div><strong>Date</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @media print {
        .page-break {
            page-break-after: always;
            page-break-inside: avoid;
        }
    }

    .letter-header {
        margin-bottom: 30px;
    }

    .letter-body {
        font-family: 'Times New Roman', serif;
        color: #000;
    }

    .letter-body h5 {
        color: #000;
        text-transform: uppercase;
    }

    #printTable {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .card-body {
        background: white;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
<script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
<script>
    function closeScript() {
        setTimeout(function () {
            window.open(window.location, '_self').close();
        }, 1000);
    }

    $(window).on('load', function () {
        var element = document.getElementById('boxes');
        var opt = {
            filename: '<?php echo e($employees->name); ?>_Appointment_Letter',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {
                scale: 4, 
                dpi: 72, 
                letterRendering: true,
                useCORS: true
            },
            jsPDF: {
                unit: 'in', 
                format: 'A4',
                orientation: 'portrait'
            },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(closeScript);
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.contractheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/employee/template/custom_appointment_letter_pdf.blade.php ENDPATH**/ ?>