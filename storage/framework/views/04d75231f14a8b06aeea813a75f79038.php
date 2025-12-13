

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Create Employee')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(url('employee')); ?>"><?php echo e(__('Employee')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Create Employee')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="">
            <div class="">
                <?php echo e(Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>


                <!-- Add this error display section at the top of your form -->
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <!-- Personal Details Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5><?php echo e(__('Personal Detail')); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('name', __('Name'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                        <?php echo Form::text('name', old('name'), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee name',
                                        ]); ?>

                                    </div>
                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('phone', __('Phone'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                        <?php echo Form::text('phone', old('phone'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter employee phone',
                                            'oninput' => 'validateNumbers()',
                                        ]); ?>

                                        <span id="phone-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('office_phone_one', __('Office Phone 1'), ['class' => 'form-label']); ?>

                                        <?php echo Form::text('office_phone_one', old('office_phone_one'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter office phone 1',
                                            'oninput' => 'validateNumbers()',
                                        ]); ?>

                                        <span id="office_phone_one-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('office_phone_two', __('Office Phone 2'), ['class' => 'form-label']); ?>

                                        <?php echo Form::text('office_phone_two', old('office_phone_two'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter office phone 2',
                                            'oninput' => 'validateNumbers()',
                                        ]); ?>

                                        <span id="office_phone_two-error" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('emergency_number', __('Emergency Number'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                        <?php echo Form::text('emergency_number', old('emergency_number'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter emergency number',
                                            'oninput' => 'validateNumbers()',
                                        ]); ?>

                                        <span id="emergency_number-error" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo Form::label('dob', __('Date of Birth'), ['class' => 'form-label']); ?>

                                            <?php echo Form::date('dob', null, [
                                                'class' => 'form-control',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'dd-mm-yyyy'
                                            ]); ?>

                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo Form::label('blood_group', __('Blood-Group'), ['class' => 'form-label']); ?><span class="text-danger pl-1"></span>
                                            <?php echo Form::text('blood_group', old('Blood-Group'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter employee Blood-Group',
                                            ]); ?>

                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo Form::label('gender', __('Gender'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                            <div class="d-flex radio-check">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_male" value="Male" name="gender"
                                                        class="form-check-input" >
                                                    <label class="form-check-label "
                                                        for="g_male"><?php echo e(__('Male')); ?></label>
                                                </div>
                                                <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                    <input type="radio" id="g_female" value="Female" name="gender"
                                                        class="form-check-input">
                                                    <label class="form-check-label "
                                                        for="g_female"><?php echo e(__('Female')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('email', __('Email'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                        <?php echo Form::email('email', old('email'), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee email',
                                        ]); ?>

                                    </div>
                                    <div class="form-group col-md-6">
                                        <?php echo Form::label('password', __('Password'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                        <?php echo Form::password('password', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee password',
                                        ]); ?>

                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo Form::label('address', __('Address'), ['class' => 'form-label']); ?><span class="text-danger pl-1">*</span>
                                    <?php echo Form::textarea('address', old('address'), [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => 'Enter employee address',
                                    ]); ?>

                                </div>
                                <div class="form-group d-none">
                                    <label for="week_off_day"><?php echo e(__('Week Off Day')); ?></label>
                                    <select name="week_off_day" id="week_off_day" class="form-control">
                                        <option value="Sunday"><?php echo e(__('Sunday')); ?></option>
                                        <option value="Monday"><?php echo e(__('Monday')); ?></option>
                                        <option value="Tuesday"><?php echo e(__('Tuesday')); ?></option>
                                        <option value="Wednesday"><?php echo e(__('Wednesday')); ?></option>
                                        <option value="Thursday"><?php echo e(__('Thursday')); ?></option>
                                        <option value="Friday"><?php echo e(__('Friday')); ?></option>
                                        <option value="Saturday"><?php echo e(__('Saturday')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Details Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5><?php echo e(__('Company Detail')); ?></h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div class="row">
                                    <?php echo csrf_field(); ?>
                                    <div class="form-group">
                                        <?php echo Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']); ?>

                                        <?php echo Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']); ?>

                                    </div>

                                    <div class="form-group col-md-6">
                                        <?php echo e(Form::label('branch_id', __('Select Branch*'), ['class' => 'form-label'])); ?>

                                        <div class="form-icon-user">
                                            <?php echo e(Form::select('branch_id', $branches, null, ['class' => 'form-control branch_id', 'id' => 'branch_id', 'required' => 'required'])); ?>

                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="form-icon-user" id="department_id">
                                            <?php echo e(Form::label('department_id', __('Department'), ['class' => 'form-label'])); ?>

                                            <select class="form-control select department_id" name="department_id"
                                                id="department_id" placeholder="Select Department" required>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <?php echo e(Form::label('designation_id', __('Select Designation'), ['class' => 'form-label'])); ?>

                                        <div class="form-icon-user designation_div">
                                            <?php echo e(Form::select('designation_id', $designations, null, ['class' => 'form-control', 'id' => 'designation_id', 'placeholder' => 'Select Designation', 'required' => 'required'])); ?>

                                        </div>
                                    </div>
                                    
                                   
                                    <div class="form-group">
                                        <?php echo Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']); ?>

                                        <?php echo Form::date('company_doj', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'off',
                                            'placeholder' => 'dd-mm-yyyy'
                                        ]); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                   
                        <!-- Experience Section -->
                        <!-- Experience Section -->
                        <div class="col-md-12">
                            <div class="card md-12">
                                <div class="card-header">
                                    <h5><?php echo e(__('Total Experience')); ?></h5>
                                   
                                </div>
                                <div class="card-body employee-detail-create-body">
                                    <div id="experience-details-container">
                                        <div class="row experience-detail-row mb-3">
                                            <div class="form-group col-md-6">
                                                <?php echo Form::label('experience[0][previous_company_name]', __('Previous Company Name'), ['class' => 'form-label']); ?>

                                                <?php echo Form::text('experience[0][previous_company_name]', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Enter previous company name',
                                                ]); ?>

                                            </div>
                                            <div class="form-group col-md-6">
                                                <?php echo Form::label('experience[0][previous_designation]', __('Designation'), ['class' => 'form-label']); ?>

                                                <?php echo Form::text('experience[0][previous_designation]', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Enter designation',
                                                ]); ?>

                                            </div>
                                            <div class="form-group col-md-6">
                                                <?php echo Form::label('experience[0][start_date]', __('Start Date'), ['class' => 'form-label']); ?>

                                                <?php echo Form::date('experience[0][start_date]', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Select start date',
                                                ]); ?>

                                            </div>
                                            <div class="form-group col-md-6">
                                                <?php echo Form::label('experience[0][end_date]', __('End Date'), ['class' => 'form-label']); ?>

                                                <?php echo Form::date('experience[0][end_date]', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Select end date',
                                                ]); ?>

                                            </div>
                                            <div class="form-group col-md-12">
                                                <?php echo Form::label('experience[0][previous_salary]', __('Previous Salary'), ['class' => 'form-label']); ?>

                                                <?php echo Form::number('experience[0][previous_salary]', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Enter previous salary',
                                                ]); ?>

                                            </div>
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents and Education Section -->
                <div class="row">
                    <!-- Documents Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5><?php echo e(__('Document')); ?></h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="row">
                                        <div class="form-group col-12 d-flex">
                                            <div class="float-left col-4">
                                                <label for="document"
                                                    class="float-left pt-1 form-label"><?php echo e($document->name); ?> <?php if($document->is_required == 1): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                            <div class="float-right col-8">
                                                <input type="hidden" name="emp_doc_id[<?php echo e($document->id); ?>]" id=""
                                                    value="<?php echo e($document->id); ?>">
                                                <div class="choose-files">
                                                    <label for="document[<?php echo e($document->id); ?>]">
                                                        <div class=" bg-primary document cursor-pointer"> <i
                                                                class="ti ti-upload "></i><?php echo e(__('Choose file here')); ?>

                                                        </div>
                                                        <input type="file"
                                                            class="form-control file <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            <?php if($document->is_required == 1): ?> required <?php endif; ?>
                                                            name="document[<?php echo e($document->id); ?>]"
                                                            id="document[<?php echo e($document->id); ?>]"
                                                            data-filename="<?php echo e($document->id . '_filename'); ?>"
                                                            onchange="document.getElementById('<?php echo e('blah' . $key); ?>').src = window.URL.createObjectURL(this.files[0])">
                                                    </label>
                                                    <img id="<?php echo e('blah' . $key); ?>" src="" width="50%" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
              
                    <!-- Education Section -->
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5><?php echo e(__('Education Details')); ?></h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div id="education-details-container">
                                    <div class="row education-detail-row">
                                        <div class="form-group col-md-6">
                                            <?php echo Form::label('education[0][degree]', __('Degree'), ['class' => 'form-label']); ?>

                                            <select name="education[0][degree]" class="form-control degree">
                                                <option value="10th"><?php echo e(__('10th')); ?></option>
                                                <option value="12th"><?php echo e(__('12th')); ?></option>
                                                <option value="Bachelor"><?php echo e(__('Bachelor')); ?></option>
                                                <option value="Master"><?php echo e(__('Master')); ?></option>
                                                <option value="PhD"><?php echo e(__('PhD')); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <?php echo Form::label('education[0][college_name]', __('College Name'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('education[0][college_name]', null, [
                                                'class' => 'form-control college-name',
                                                'placeholder' => 'Enter college name',
                                            ]); ?>

                                        </div>
                                        <div class="form-group col-md-6">
                                            <?php echo Form::label('education[0][passing_year]', __('Passing Year'), ['class' => 'form-label']); ?>

                                            <select name="education[0][passing_year]" class="form-control passing-year">
                                                <option value="" disabled selected><?php echo e(__('Select Year')); ?></option>
                                                <?php for($year = 1997; $year <= 2040; $year++): ?>
                                                    <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <?php echo Form::label('education[0][grade]', __('Grade'), ['class' => 'form-label']); ?>

                                            <?php echo Form::number('education[0][grade]', null, [
                                                'class' => 'form-control grade',
                                                'placeholder' => 'Enter grade (e.g., 4.0)',
                                                'step' => '0.1',
                                                'min' => '0',
                                                'max' => '10',
                                            ]); ?>

                                        </div>
                                        <div class="form-group col-md-12">
                                                <?php echo Form::label("education[0][document]", __('Education Document'), ['class' => 'form-label']); ?>                                            <div class="choose-files">
                                                <label for="education[0][document]">
                                                    <div class="bg-primary document cursor-pointer">
                                                        <i class="ti ti-upload"></i><?php echo e(__('Choose file here')); ?>

                                                    </div>
                                                    <input type="file" 
                                                        name="education[0][document]" 
                                                        id="education[0][document]" 
                                                        class="form-control file education-document"
                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                </label>
                                            </div>
                                        </div>
                                      
                                        <div class="form-group col-md-12 text-end">
                                            <button type="button" class="btn btn-danger remove-education-row">
                                                <i class="fa fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="button" class="btn btn-primary add-education-row">
                                        <i class="fa fa-plus"></i> Add Education
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Account Detail Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5><?php echo e(__('Bank Account Detail')); ?></h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div class="row">
                                    <!-- Left Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('account_holder_name', old('account_holder_name'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter account holder name',
                                            ]); ?>

                                        </div>
                                        <div class="form-group">
                                            <?php echo Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('bank_name', old('bank_name'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter bank name',
                                            ]); ?>

                                        </div>
                                        <div class="form-group">
                                            <?php echo Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('branch_location', old('branch_location'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter branch location',
                                            ]); ?>

                                        </div>
                                    </div>
                                    <!-- Right Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo Form::label('account_number', __('Account Number'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('account_number', old('account_number'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter account number',
                                            ]); ?>

                                        </div>
                                        <div class="form-group">
                                            <?php echo Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('bank_identifier_code', old('bank_identifier_code'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter bank identifier code',
                                            ]); ?>

                                        </div>
                                        <div class="form-group">
                                            <?php echo Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']); ?>

                                            <?php echo Form::text('tax_payer_id', old('tax_payer_id'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter tax payer id',
                                            ]); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="float-end">
                    <button type="submit" class="btn btn-primary"><?php echo e('Create'); ?></button>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name = $(this).attr('data-filename');
            $('.' + file_name).append(file);
        });
    </script>
    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {
            $.ajax({
                url: '<?php echo e(route('monthly.getdepartment')); ?>',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {
                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" required>
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> <?php echo e(__('Select Department')); ?> </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var d_id = $('.department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '<?php echo e(route('employee.json')); ?>',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {
                    $('.designation_id').empty();
                    var emp_selct = `<select class="form-control designation_id" name="designation_id"
                                                 placeholder="Select Designation" required>
                                            </select>`;
                    $('.designation_div').html(emp_selct);

                    $('.designation_id').append('<option value=""> <?php echo e(__('Select Designation')); ?> </option>');
                    $.each(data, function(key, value) {
                        $('.designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>



    <script>
        // Education Details Dynamic Rows
        $(document).ready(function() {
        

            // Experience Details Dynamic Rows
            // Experience Details Dynamic Rows
            let experienceRowCount = 1;

            // Add new experience row
            $(document).on('click', '.add-experience-row', function() {
                const newRow = `
                    <div class="row experience-detail-row mb-3">
                        <div class="form-group col-md-6">
                            <label class="form-label">Previous Company Name</label>
                            <input type="text" name="experience[${experienceRowCount}][previous_company_name]" 
                                class="form-control" placeholder="Enter previous company name">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="experience[${experienceRowCount}][designation]" 
                                class="form-control" placeholder="Enter designation">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="experience[${experienceRowCount}][start_date]" 
                                class="form-control" placeholder="Select start date">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="experience[${experienceRowCount}][end_date]" 
                                class="form-control" placeholder="Select end date">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">Previous Salary</label>
                            <input type="number" name="experience[${experienceRowCount}][previous_salary]" 
                                class="form-control" placeholder="Enter previous salary">
                        </div>
                        <div class="form-group col-md-12 text-end">
                            <button type="button" class="btn btn-danger remove-experience-row">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                
                $('#experience-details-container').append(newRow);
                experienceRowCount++;
            });

            // Remove experience row
            $(document).on('click', '.remove-experience-row', function() {
                $(this).closest('.experience-detail-row').remove();
            });
        });

        // Phone number validation
        function validateNumbers() {
            const phone = document.getElementsByName('phone')[0].value;
            const officePhoneOne = document.getElementsByName('office_phone_one')[0].value;
            const officePhoneTwo = document.getElementsByName('office_phone_two')[0].value;
            const emergencyNumber = document.getElementsByName('emergency_number')[0].value;

            const numbers = [phone, officePhoneOne, officePhoneTwo, emergencyNumber];
            const errorIds = ['phone-error', 'office_phone_one-error', 'office_phone_two-error', 'emergency_number-error'];
            
            // Clear previous errors
            errorIds.forEach(id => document.getElementById(id).innerText = '');
            
            // Check for duplicates
            for (let i = 0; i < numbers.length; i++) {
                if (numbers[i]) {
                    for (let j = 0; j < numbers.length; j++) {
                        if (i !== j && numbers[i] && numbers[i] === numbers[j]) {
                            document.getElementById(errorIds[i]).innerText = 'Do not use the same number in multiple fields.';
                            document.getElementById(errorIds[j]).innerText = 'Do not use the same number in multiple fields.';
                        }
                    }
                }
            }
        }
    </script>

    <script>
        $(document).ready(function() {

            let educationRowCount = 1;
            
            // Add new education row
            $('.add-education-row').click(function() {
                const newRow = `
                    <div class="row education-detail-row mb-3">
                        <div class="form-group col-md-6">
                            <label class="form-label">Degree</label>
                            <select name="education[${educationRowCount}][degree]" class="form-control degree">
                                <option value="10th">10th</option>
                                <option value="12th">12th</option>
                                <option value="Bachelor">Bachelor</option>
                                <option value="Master">Master</option>
                                <option value="PhD">PhD</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">College Name</label>
                            <input type="text" name="education[${educationRowCount}][college_name]" 
                                   class="form-control college-name" placeholder="Enter college name">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Passing Year</label>
                            <select name="education[${educationRowCount}][passing_year]" class="form-control passing-year">
                                <option value="" disabled selected>Select Year</option>
                                <?php for($year = 1997; $year <= 2040; $year++): ?>
                                    <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Grade</label>
                            <input type="number" name="education[${educationRowCount}][grade]" 
                                   class="form-control grade" placeholder="Enter grade" step="0.1" min="0" max="10">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">Education Document</label>
                            <div class="choose-files">
                                <label for="education[${educationRowCount}][document]">
                                    <div class="bg-primary document cursor-pointer">
                                        <i class="ti ti-upload"></i> Choose file here
                                    </div>
                                    <input type="file" name="education[${educationRowCount}][document]"
                                           id="education[${educationRowCount}][document]"
                                           class="form-control file education-document"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12 text-end">
                            <button type="button" class="btn btn-danger remove-education-row">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                
                $('#education-details-container').append(newRow);
                educationRowCount++;
            });
            
            // Remove education row
            $(document).on('click', '.remove-education-row', function() {
                $(this).closest('.education-detail-row').remove();
            });
        });  
        
        // Add this new code after all the existing JavaScript
        $(document).ready(function() {
            // Handle education document preview
            $(document).on('change', '.education-document', function() {
                const input = this;
                const row = $(this).closest('.education-detail-row');
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Remove any existing preview
                        row.find('.document-preview').remove();
                        
                        // Add preview for image files
                        if (input.files[0].type.match('image.*')) {
                            const preview = $('<img class="document-preview mt-2" style="max-width: 200px; max-height: 200px;">');
                            preview.attr('src', e.target.result);
                            row.find('.choose-files').append(preview);
                        }
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\HRM_Clearclaim\resources\views/employee/create.blade.php ENDPATH**/ ?>