<?php
    $plan = Utility::getChatGPTSettings();
?>

<?php echo e(Form::open(['url' => 'termination', 'method' => 'post'])); ?>

<div class="modal-body">

    <?php if($plan->enable_chatgpt == 'on'): ?>
    <div class="card-footer text-end">
        <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true" data-url="<?php echo e(route('generate', ['termination'])); ?>"
            data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(__('Generate')); ?>"
            data-title="<?php echo e(__('Generate Content With AI')); ?>">
            <i class="fas fa-robot"></i><?php echo e(__(' Generate With AI')); ?>

        </a>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('employee_id', __('Employee'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('termination_type', __('Termination Type'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::select('termination_type', $terminationtypes, null, ['class' => 'form-control select2', 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-lg-12 col-md-12">
            <?php echo e(Form::label('termination_date', __('Termination Date'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::text('termination_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off' ,'required' => 'required'])); ?>

        </div>
        <div class="form-group  col-lg-12">
            <?php echo e(Form::label('description', __('Description'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows' => '3' ,'required' => 'required'])); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Create')); ?>" class="btn btn-primary">
</div>

<?php echo e(Form::close()); ?>


<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);
    });
</script><?php /**PATH D:\HRM_Clearclaim\resources\views/termination/create.blade.php ENDPATH**/ ?>