<table>
    <tr>
        <td colspan="<?php echo e(count($dates) + 1); ?>"><strong><?php echo e(\Carbon\Carbon::parse($start_date)->format('M d Y')); ?> To <?php echo e(\Carbon\Carbon::parse($end_date)->format('M d Y')); ?></strong></td>
    </tr>

        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"></td>
        </tr>


    
    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!-- Employee Header -->
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>Empployee Code:</strong> <?php echo e($employee->employee_id); ?> </td>
        </tr>
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>Empployee. Name:</strong> <?php echo e($employee->name); ?></td>
        </tr>
        
        <!-- Status Row -->
        <tr>
            <td><strong>Days</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e(\Carbon\Carbon::parse($date)->format('d D')); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['status'])): ?>
                        <?php echo e(substr($attendanceData[$employee->id][$date]['status'], 0, 1)); ?>

                    <?php else: ?>
                        A
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- In Time Row -->
        <tr>
            <td><strong>InTime</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['clock_in'])): ?>
                        <?php echo e(substr($attendanceData[$employee->id][$date]['clock_in'], 0, 5)); ?>

                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Out Time Row -->
        <tr>
            <td><strong>OutTime</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['clock_out'])): ?>
                        <?php echo e(substr($attendanceData[$employee->id][$date]['clock_out'], 0, 5)); ?>

                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Total Time Row -->
        <tr>
            <td><strong>Total</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['total'])): ?>
                        <?php echo e($attendanceData[$employee->id][$date]['total']); ?>

                    <?php else: ?>
                        00:00
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table><?php /**PATH D:\HRM_Clearclaim\resources\views/attendance/export.blade.php ENDPATH**/ ?>