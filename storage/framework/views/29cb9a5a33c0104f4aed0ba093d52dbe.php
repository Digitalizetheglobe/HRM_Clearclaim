<table>
    <tr>
        <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>ATTENDANCE REPORT</strong></td>
    </tr>
    <tr>
        <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>Period:</strong> <?php echo e(\Carbon\Carbon::parse($start_date)->format('M d, Y')); ?> To <?php echo e(\Carbon\Carbon::parse($end_date)->format('M d, Y')); ?></td>
    </tr>
    <tr>
        <td colspan="<?php echo e(count($dates) + 1); ?>"></td>
    </tr>

    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!-- Employee Information -->
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>Employee Code:</strong> <?php echo e($employee->employee_id); ?></td>
        </tr>
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>Employee Name:</strong> <?php echo e($employee->name); ?></td>
        </tr>
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"></td>
        </tr>
        
        <!-- Date Header Row -->
        <tr>
            <td><strong>Days</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e(\Carbon\Carbon::parse($date)->format('d')); ?><br><?php echo e(\Carbon\Carbon::parse($date)->format('D')); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Status Row -->
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
            <td><strong>IN Time</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['clock_in'])): ?>
                        <?php if($attendanceData[$employee->id][$date]['clock_in'] != '00:00:00'): ?>
                            <?php echo e(substr($attendanceData[$employee->id][$date]['clock_in'], 0, 5)); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Out Time Row -->
        <tr>
            <td><strong>OUT Time</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['clock_out'])): ?>
                        <?php if($attendanceData[$employee->id][$date]['clock_out'] != '00:00:00'): ?>
                            <?php echo e(substr($attendanceData[$employee->id][$date]['clock_out'], 0, 5)); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Total Hours Row -->
        <tr>
            <td><strong>Total Hours</strong></td>
            <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(isset($attendanceData[$employee->id][$date]['total'])): ?>
                        <?php if($attendanceData[$employee->id][$date]['total'] != '00:00'): ?>
                            <?php echo e($attendanceData[$employee->id][$date]['total']); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        
        <!-- Summary Section -->
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"></td>
        </tr>
        <tr>
            <td colspan="<?php echo e(count($dates) + 1); ?>"><strong>SUMMARY</strong></td>
        </tr>
        <tr>
            <td><strong>Total Working Days:</strong></td>
            <td colspan="<?php echo e(count($dates)); ?>"><?php echo e($totalWorkingDays); ?> days</td>
        </tr>
        <tr>
            <td><strong>Total Month Days:</strong></td>
            <td colspan="<?php echo e(count($dates)); ?>"><?php echo e(count($dates)); ?> days</td>
        </tr>
        <tr>
            <td><strong>Monthly Total Worked Hours:</strong></td>
            <td colspan="<?php echo e(count($dates)); ?>"><?php echo e($totalHoursFormatted); ?></td>
        </tr>
        <tr>
            <td><strong>Required Hours (<?php echo e(count($dates)); ?> days × 9 hours):</strong></td>
            <td colspan="<?php echo e(count($dates)); ?>"><?php echo e($requiredHoursFormatted); ?></td>
        </tr>
        <tr>
            <td><strong>Extra/Short Hours:</strong></td>
            <td colspan="<?php echo e(count($dates)); ?>">
                <?php if(strpos($extraShortHours, '+') === 0): ?>
                    <strong style="color: green;"><?php echo e($extraShortHours); ?> (Extra)</strong>
                <?php elseif(strpos($extraShortHours, '-') === 0): ?>
                    <strong style="color: red;"><?php echo e($extraShortHours); ?> (Short)</strong>
                <?php else: ?>
                    <?php echo e($extraShortHours); ?>

                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>

<?php /**PATH D:\HRM_Clearclaim\resources\views/attendance/export_employee.blade.php ENDPATH**/ ?>