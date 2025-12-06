    <?php

    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::GetLogo();
    $users = \Auth::user();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $currantLang = $users->currentLanguage();
    $emailTemplate = App\Models\EmailTemplate::getemailTemplate();
    $lang = Auth::user()->lang;
    $userType = auth()->user()->type;

?>

<?php if(isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on'): ?>
<nav class="dash-sidebar light-sidebar transprent-bg" 
    style="background: linear-gradient(to bottom, #000, #575757); height: 750px;">
    
    <?php else: ?>
        <nav class="dash-sidebar light-sidebar" style="background: linear-gradient(to bottom, #000, #050505); height: 715px;">
<?php endif; ?>



<div class="navbar-wrapper">
    <div class="m-header main-logo">
    <a href="<?php echo e(route('dashboard')); ?>" class="b-brand">
            <!-- ========   change your logo hear   ============ -->
            <?php if(!empty($company_logo)): ?>
                <img src="<?php echo e($logo . $company_logo); ?>?v=<?php echo e(time()); ?>"
                    alt="<?php echo e(config('app.name', 'HRMGo')); ?>" class="logo logo-lg" style="height: 75px;">
            <?php else: ?>
                <img src="<?php echo e(asset('storage/uploads/logo/logo-dark.png')); ?>?v=<?php echo e(time()); ?>"
                    alt="<?php echo e(config('app.name', 'HRMGo')); ?>" class="logo logo-lg" style="height: 75px;">
            <?php endif; ?>
        </a>
    </div>
    <div class="navbar-content">
        <ul class="dash-navbar">

            <!-- dashboard-->
            <?php if(\Auth::user()->type != 'company'): ?>
            <li class="dash-item" style="color: white;" >
                <a href="<?php echo e(route('dashboard')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"">
                    <span class="dash-micon shadow-none" style="background: none;"><i class="ti ti-home text-white text-[30px]"></i></span>
                    <span class="dash-mtext" style="color: white;"><?php echo e(__('Dashboard')); ?></span>
                </a>
            </li>

            <?php endif; ?>
            <?php if(\Auth::user()->type == 'company'): ?>
                <li class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'null' ? 'active dash-trigger' : ''); ?>">
                    <a href="<?php echo e(route('dashboard')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                        <span class="dash-micon  shadow-none" style="background: none;">
                            <i class="ti ti-home text-white text-[30px]"></i>
                        </span>
                        <span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                        <!-- <span class="dash-arrow " style="background: none;">
                            <i data-feather="chevron-right"></i>
                        </span> -->
                    </a>

                    <!-- <ul class="dash-submenu">
                        <li class="dash-item <?php echo e(Request::segment(1) == null || Request::segment(1) == 'report' ? 'active dash-trigger' : ''); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                            href="<?php echo e(route('dashboard')); ?>">
                            <?php echo e(__('Overview')); ?>

                            </a>
                        </li>
                        <?php if(Gate::check('Manage Report')): ?>
                            <li class="dash-item dash-hasmenu">
                                <a href="#!" 
                                class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg">
                                    <span class=""><i class=""></i></span>
                                    <span class="dash-mtext"><?php echo e(__('Report')); ?></span>
                                    <span class="dash-arrow">
                                        <i data-feather="chevron-right"></i>
                                    </span>
                                </a>
                                <ul class="dash-submenu">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Report')): ?>
                                        
                                        <li class="dash-item">
                                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                                            href="<?php echo e(route('report.monthly.attendance')); ?>">
                                            <?php echo e(__('Monthly Attendance')); ?>

                                            </a>
                                        </li>
                                        
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul> -->
                </li>
            <?php endif; ?>

            <!--dashboard-->

            <!-- user-->
            <?php if(\Auth::user()->type == 'super admin'): ?>
                <li class="dash-item text-white">
                    <a href="<?php echo e(route('user.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lgflex items-center space-x-2 ">
                        <span class="dash-micon shadow-none" style="background: none;"><i class="ti ti-user text-white text-[30px]"></i></span>
                        <span class="dash-mtext"><?php echo e(__('Companies')); ?></span>
                    </a>
                </li>
            <?php else: ?>
                <?php if(Gate::check('Manage User') ||
                        Gate::check('Manage Role') ||
                        Gate::check('Manage Employee Profile') ||
                        Gate::check('Manage Employee Last Login')): ?>
                    <li class="dash-item dash-hasmenu text-white 
                        <?php echo e(Request::segment(1) == 'user' || Request::segment(1) == 'roles' || Request::segment(1) == 'lastlogin'
                            ? 'active dash-trigger'
                            : ''); ?>">
                            <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                                <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                                    <i class="ti ti-users text-white text-[30px]"></i>
                                </span>
                                <span class="dash-mtext"><?php echo e(__('Staff')); ?></span>
                                <span class="dash-arrow text-white text-[18px] shadow-none" style="background: none;">
                                    <i data-feather="chevron-right"></i>
                                </span>
                            </a>

                        <ul class="dash-submenu 
                            <?php echo e(Request::route()->getName() == 'user.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'user.edit' || Request::route()->getName() == 'lastlogin'
                                ? 'active'
                                : ''); ?>">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage User')): ?>
                                <li class="dash-item <?php echo e(Request::segment(1) == 'lastlogin' ? 'active' : ''); ?>">
                                    <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                                    href="<?php echo e(route('user.index')); ?>">
                                    <?php echo e(__('User')); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                            <!-- <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Role')): ?>
                                <li class="dash-item">
                                    <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                                    href="<?php echo e(route('roles.index')); ?>">
                                    <?php echo e(__('Role')); ?>

                                    </a>
                                </li>
                            <?php endif; ?> -->
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Employee Profile')): ?>
                                <li class="dash-item">
                                    <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                                    href="<?php echo e(route('employee.profile')); ?>">
                                    <?php echo e(__('Employee Profile')); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                            
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- user-->

            <!-- employee-->
            <?php if(Gate::check('Manage Employee')): ?>
            <?php if(\Auth::user()->type == 'employee'): ?>
                <?php
                    $employee = App\Models\Employee::where('user_id', \Auth::user()->id)->first();
                ?>
                <li class="dash-item <?php echo e(Request::segment(1) == 'employee' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                                <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                                    <i class="ti ti-users text-white text-[30px]"></i>
                                </span>
                        <span class="dash-mtext"><?php echo e(__('Employee')); ?></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="dash-item <?php echo e(Request::segment(1) == 'employee' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('employee.index')); ?>"
                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                        <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                                    <i class="ti ti-users text-white text-[30px]"></i>
                        </span>
                        <span class="dash-mtext"><?php echo e(__('Employee')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>


            <!-- employee-->



            <!-- To-Do -->
            <li class="dash-item">
                <a href="<?php echo e(route('todo.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                <i class="fas fa-tasks text-white text-[30px]"></i></span>
                    <span class="dash-mtext"><?php echo e(__('To-Do List')); ?></span>
                </a>
            </li>



             <!-- Notice -->
            <li class="dash-item">
                <a href="<?php echo e(route('notices.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                <i class="fas fa-clipboard-list text-white text-[30px]"></i></span>
                    <span class="dash-mtext"><?php echo e(__('Notice')); ?></span>
                </a>
            </li>

             <!-- enquiry -->
                <!-- <li class="dash-item">
                    <a href="<?php echo e(route('timesheet.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                        <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="fas fa-question-circle text-white text-[30px]"></i>
                        </span>
                        <span class="dash-mtext"><?php echo e(__('TimeSheet')); ?></span>
                    </a>
                </li> -->

                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'setsalary' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="fas fa-user-slash text-white text-[30px]"></i>
                          </span><span
                            class="dash-mtext"><?php echo e(__('Offboard ')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('resignation.index')); ?>"><?php echo e(__('Resignation ')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('termination.index')); ?>"><?php echo e(__('Exit Formalities')); ?></a>
                        </li>
                    </ul>
                </li>

            <!-- payroll-->
            <?php if(Gate::check('Manage Set Salary') || Gate::check('Manage Pay Slip')): ?>
                <li
                    class="dash-item dash-hasmenu  <?php echo e(Request::segment(1) == 'setsalary' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                        <span class="dash-micon text-[50px] shadow-none" style="background: none;">
                            <i class="ti ti-receipt text-white text-[30px]">
                            </i>
                        </span>
                        <span class="dash-mtext">
                            <?php echo e(__('Payroll')); ?>

                        </span>
                        <span class="dash-arrow"><i data-feather="chevron-right">
                            </i>
                        </span>
                    </a>
                    <ul class="dash-submenu ">
                        <li class="dash-item <?php echo e(Request::segment(1) == 'setsalary' ? 'active' : '-'); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('setsalary.index')); ?>"><?php echo e(__('Set Salary')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('payslip.index')); ?>"><?php echo e(__('Payslip')); ?></a>
                        </li>

                    </ul>
                </li>
            <?php endif; ?>
            <!-- payroll-->

            <?php if(\Auth::user()->type == 'employee'): ?>
                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'setsalary' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-receipt text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Payroll')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item <?php echo e(Request::segment(1) == 'setsalary' ? 'active' : '-'); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt(\Auth::user()->id))); ?>"><?php echo e(__('Salary')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('payslip.index')); ?>"><?php echo e(__('Payslip')); ?></a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'calender' && Request::segment(2) == 'leave' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-clock text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Leave')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Leave')): ?>
                            <li class="dash-item <?php echo e(Request::segment(1) == 'calender' ? ' active' : ''); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('leave.index')); ?>"><?php echo e(__('Manage Leave')); ?></a>
                            </li>              
                        <?php endif; ?>
                        
                    </ul>
                </li>





            <!-- timesheet-->
            <?php if(Gate::check('Manage Attendance') || Gate::check('Create Attendance') || Gate::check('Manage Biometric Attendance')): ?>
                <li class="dash-item dash-hasmenu <?php echo e(Request::is('attendance*') || Request::is('biometric-attendance*') ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                        <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-clock text-white text-[30px]"></i>
                        </span>
                        <span class="dash-mtext"><?php echo e(__('Attendance')); ?></span>
                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                    </a>

                    <ul class="dash-submenu">

                       <li class="dash-item <?php echo e(Request::segment(1) == 'attendance-calendar' ? ' active' : ''); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('attendance.calendar')); ?>"><?php echo e(__('Attendance Calendar')); ?></a>
                        </li>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Report')): ?>
                                        
                                        <li class="dash-item">
                                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" 
                                            href="<?php echo e(route('report.monthly.attendance')); ?>">
                                            <?php echo e(__('Monthly Attendance')); ?>

                                            </a>
                                        </li>
                                        
                                    <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Attendance')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('attendanceemployee.index')); ?>"><?php echo e(__('Marked Attendance')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Attendance')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('attendanceemployee.bulkattendance')); ?>"><?php echo e(__('Bulk Attendance')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Biometric Attendance')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('biometric-attendance.index')); ?>"><?php echo e(__('Biometric Attendance')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!--timesheet-->

            <!-- performance-->
            <?php if(Gate::check('Manage Indicator') || Gate::check('Manage Appraisal') || Gate::check('Manage Goal Tracking')): ?>
                <li class="dash-item dash-hasmenu">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-3d-cube-sphere text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Performance')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Indicator')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('indicator.index')); ?>"><?php echo e(__('Indicator')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Appraisal')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('appraisal.index')); ?>"><?php echo e(__('Appraisal')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Goal Tracking')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('goaltracking.index')); ?>"><?php echo e(__('Goal Tracking')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <!--performance-->

            <!--fianance-->
            <?php if(Gate::check('Manage Account List') ||
                    Gate::check('Manage Payee') ||
                    Gate::check('Manage Payer') ||
                    Gate::check('Manage Deposit') ||
                    Gate::check('Manage Expense') ||
                    Gate::check('Manage Transfer Balance')): ?>
                <li class="dash-item dash-hasmenu">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-wallet text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Finance')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Account List')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('accountlist.index')); ?>"><?php echo e(__('Account List')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('View Balance Account List')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('accountbalance')); ?>"><?php echo e(__('Account Balance')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Payee')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('payees.index')); ?>"><?php echo e(__('Payees')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Payer')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('payer.index')); ?>"><?php echo e(__('Payers')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Deposit')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('deposit.index')); ?>"><?php echo e(__('Deposit')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Expense')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('expense.index')); ?>"><?php echo e(__('Expense')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Transfer Balance')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('transferbalance.index')); ?>"><?php echo e(__('Transfer Balance')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <!-- fianance-->

            <!--trainning-->
            <?php if(Gate::check('Manage Trainer') || Gate::check('Manage Training')): ?>
                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'training' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-school text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Training')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Training')): ?>
                            <li class="dash-item <?php echo e(Request::segment(1) == 'training' ? ' active' : ''); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('training.index')); ?>"><?php echo e(__('Training List')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Trainer')): ?>
                            <li class="dash-item ">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('trainer.index')); ?>"><?php echo e(__('Trainer')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- tranning-->


            <!-- HR-->
            <?php if(Gate::check('Manage Awards') ||
                    Gate::check('Manage Transfer') ||
                    Gate::check('Manage Resignation') ||
                    Gate::check('Manage Travels') ||
                    Gate::check('Manage Promotion') ||
                    Gate::check('Manage Complaint') ||
                    Gate::check('Manage Warning') ||
                    Gate::check('Manage Termination') ||
                    Gate::check('Manage Announcement') ||
                    Gate::check('Manage Holiday')): ?>
                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'holiday' ? 'dash-trigger active' : ''); ?>">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-user-plus text-white text-[30px]"></i>
                        </span>
                        <span class="dash-mtext"><?php echo e(__('HR Admin Setup')); ?></span>
                        <span class="dash-arrow">
                            <i data-feather="chevron-right"></i>
                        </span>
                    </a>
                    <ul class="dash-submenu">
                        <li class="dash-item <?php echo e(Request::segment(1) == 'award' ? 'active' : ''); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('award.index')); ?>"><?php echo e(__('Award')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('transfer.index')); ?>"><?php echo e(__('Transfer')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('resignation.index')); ?>"><?php echo e(__('Resignation')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('travel.index')); ?>"><?php echo e(__('Trip')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('promotion.index')); ?>"><?php echo e(__('Promotion')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('complaint.index')); ?>"><?php echo e(__('Complaints')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('warning.index')); ?>"><?php echo e(__('Warning')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('termination.index')); ?>"><?php echo e(__('Termination')); ?></a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                href="<?php echo e(route('announcement.index')); ?>"><?php echo e(__('Announcement')); ?></a>
                        </li>
                        <li class="dash-item <?php echo e(Request::segment(1) == 'holiday' ? ' active' : ''); ?>">
                            <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('holiday.index')); ?>"><?php echo e(__('Holidays')); ?></a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
            <!-- HR-->

            <!-- recruitment-->
            <?php if(Gate::check('Manage Job') ||
                    Gate::check('Manage Job Application') ||
                    Gate::check('Manage Job OnBoard') ||
                    Gate::check('Manage Custom Question') ||
                    Gate::check('Manage Interview Schedule') ||
                    Gate::check('Manage Career')): ?>
                <li
                    class="dash-item dash-hasmenu  <?php echo e(Request::segment(1) == 'job' || Request::segment(1) == 'job-application' ? 'dash-trigger active' : ''); ?> ">
                    <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-license text-white text-[30px] "></i></span><span
                            class="dash-mtext"><?php echo e(__('Recruitment')); ?></span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Job')): ?>
                            <li
                                class="dash-item <?php echo e(Request::route()->getName() == 'job.index' ? 'active' : 'dash-hasmenu'); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('job.index')); ?>"><?php echo e(__('Jobs')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Job')): ?>
                            <li
                                class="dash-item <?php echo e(Request::route()->getName() == 'job.create' ? 'active' : 'dash-hasmenu'); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('job.create')); ?>"><?php echo e(__('Job Create ')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Job Application')): ?>
                            <li class="dash-item <?php echo e(request()->is('job-application*') ? 'active' : ''); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('job-application.index')); ?>"><?php echo e(__('Job Application')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Job Application')): ?>

                            <li class="dash-item <?php echo e(request()->is('candidates-job-applications') ? 'active' : ''); ?>">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('job.application.candidate')); ?>"><?php echo e(__('Job Candidate')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Job OnBoard')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('job.on.board')); ?>"><?php echo e(__('Job On-Boarding')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Custom Question')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('custom-question.index')); ?>"><?php echo e(__('Custom Question')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Interview Schedule')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"
                                    href="<?php echo e(route('interview-schedule.index')); ?>"><?php echo e(__('Interview Schedule')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Career')): ?>
                            <li class="dash-item">
                                <a class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg" href="<?php echo e(route('career', [\Auth::user()->creatorId(), $lang])); ?>"
                                    target="_blank"><?php echo e(__('Career')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <!-- recruitment-->

            
            <!--contract-->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Contract')): ?>
                <li
                    class="dash-item <?php echo e(Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('contract.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                        <i class="ti ti-device-floppy text-white text-[30px]">
                        </i></span><span class="dash-mtext"><?php echo e(__('Contracts')); ?></span></a>
                </li>
            <?php endif; ?>

            <!--end-->


           






            



            <!-- ticket-->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Ticket')): ?>
                <li class="dash-item <?php echo e(Request::segment(1) == 'ticket' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('ticket.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-ticket  text-white text-[30px]"></i></span><span class="dash-mtext"><?php echo e(__('Ticket')); ?></span></a>
                </li>
            <?php endif; ?>



            <!-- Event-->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Event')): ?>
                <li class="dash-item">
                    <a href="<?php echo e(route('event.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lgn flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-calendar-event text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Event')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            


             

            <!--meeting-->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Meeting')): ?>
                <li
                    class="dash-item <?php echo e(Request::segment(1) == 'meeting' || Request::segment(2) == 'meeting' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('meeting.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-calendar-time text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Meeting')); ?></span></a>
                </li>
            <?php endif; ?>


            <!-- Zoom meeting-->
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Zoom meeting')): ?>
                <?php if(\Auth::user()->type != 'super admin'): ?>
                    <li class="dash-item <?php echo e(Request::segment(1) == 'zoommeeting' ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('zoom-meeting.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                    class="ti ti-video text-white text-[30px]"></i></span><span
                                class="dash-mtext"><?php echo e(__('Zoom Meeting')); ?></span></a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- assets-->
            <?php if(Gate::check('Manage Assets')): ?>
                <li class="dash-item">
                    <a href="<?php echo e(route('account-assets.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-medical-cross text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Assets')); ?></span></a>
                </li>
            <?php endif; ?>


            <!-- document-->
            <?php if(Gate::check('Manage Document')): ?>
                <li class="dash-item">
                    <a href="<?php echo e(route('document-upload.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lgflex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-file text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Document')); ?></span></a>
                </li>
            <?php endif; ?>

            <!--company policy-->



            <?php if(Gate::check('Manage Company Policy')): ?>
                <li class="dash-item">
                    <a href="<?php echo e(route('company-policy.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-pray text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Company Policy')); ?></span></a>
                </li>
            <?php endif; ?>
            <!--chats-->
            <?php if(\Auth::user()->type != 'super admin'): ?>
                <li class="dash-item <?php echo e(Request::segment(1) == 'chats' ? 'active' : ''); ?>">
                    <a href="<?php echo e(url('chats')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i
                                class="ti ti-messages text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Messenger')); ?></span></a>
                </li>
            <?php endif; ?>

            <?php if(\Auth::user()->type == 'company'): ?>
                <li
                    class="dash-item <?php echo e(Request::route()->getName() == 'notification-templates.update' || Request::segment(1) == 'notification-templates' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('notification-templates.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text- flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-bell text-white text-[30]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Notification Template')); ?></span></a>
                </li>
            <?php endif; ?>

            <?php if(\Auth::user()->type == 'super admin'): ?>
                <?php if(Gate::check('Manage Plan')): ?>
                    <li class="dash-item ">
                        <a href="<?php echo e(route('plans.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                                <i class=" ti ti-trophy text-white text-[30px]"></i></span><span
                                class="dash-mtext"><?php echo e(__('Plan')); ?></span></a>

                    </li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(\Auth::user()->type == 'super admin'): ?>
                <li class="dash-item ">
                    <a href="<?php echo e(route('plan_request.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                            <i class="ti ti-arrow-down-right-circle text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Plan Request')); ?></span></a>

                </li>
            <?php endif; ?>


            <?php if(\Auth::user()->type == 'super admin'): ?>
                <li class="dash-item dash-hasmenu  <?php echo e(Request::segment(1) == '' ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('referral-program.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                        <i class="ti ti-discount-2 text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Referral Program')); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if(Auth::user()->type == 'super admin'): ?>
                <?php if(Gate::check('manage coupon')): ?>
                    <li
                        class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'coupons' ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('coupons.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                                <i class="ti ti-gift text-white text-[30px]"></i></span><span
                                class="dash-mtext"><?php echo e(__('Coupon')); ?></span></a>

                    </li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(\Auth::user()->type == 'super admin'): ?>
                
                <li class="dash-item ">
                    <a href="<?php echo e(route('order.index')); ?>"
                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2 <?php echo e(request()->is('orders*') ? 'active' : ''); ?>"><span
                            class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i class="ti ti-shopping-cart text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Order')); ?></span></a>

                </li>
                
            <?php endif; ?>

            <?php if(\Auth::user()->type == 'super admin'): ?>
                <!--  -->
            <?php endif; ?>
            <!--report-->
  

            <!--constant-->
            <?php if(Gate::check('Manage Department') ||
                    Gate::check('Manage Designation') ||
                    Gate::check('Manage Document Type') ||
                    Gate::check('Manage Branch') ||
                    Gate::check('Manage Site') ||
                    Gate::check('Manage Award Type') ||
                    Gate::check('Manage Termination Types') ||
                    Gate::check('Manage Payslip Type') ||
                    Gate::check('Manage Allowance Option') ||
                    Gate::check('Manage Loan Options') ||
                    Gate::check('Manage Deduction Options') ||
                    Gate::check('Manage Expense Type') ||
                    Gate::check('Manage Income Type') ||
                    Gate::check('Manage Payment Type') ||
                    Gate::check('Manage Leave Type') ||
                    Gate::check('Manage Training Type') ||
                    Gate::check('Manage Job Category') ||
                    Gate::check('Manage Job Stage')): ?>
                <li
                    class="dash-item dash-hasmenu <?php echo e(Request::route()->getName() == 'branch.index'||Request::route()->getName() == 'site.index'  ||Request::route()->getName() == 'department.index' ||Request::route()->getName() == 'designation.index' ||Request::route()->getName() == 'leavetype.index' ||Request::route()->getName() == 'document.index' ||Request::route()->getName() == 'paysliptype.index' ||Request::route()->getName() == 'allowanceoption.index' ||Request::route()->getName() == 'loanoption.index' ||Request::route()->getName() == 'deductionoption.index' ||Request::route()->getName() == 'goaltype.index' ||Request::route()->getName() == 'trainingtype.index' ||Request::route()->getName() == 'awardtype.index' ||Request::route()->getName() == 'terminationtype.index' ||Request::route()->getName() == 'job-category.index' ||Request::route()->getName() == 'job-stage.index' ||Request::route()->getName() == 'performanceType.index' ||Request::route()->getName() == 'competencies.index' ||Request::route()->getName() == 'expensetype.index' ||Request::route()->getName() == 'incometype.index' ||Request::route()->getName() == 'paymenttype.index' ||Request::route()->getName() == 'contract_type.index'? ' active': ''); ?>">
                    <a href="<?php echo e(route('branch.index')); ?>" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><span class="dash-micon text-white text-[30px] shadow-none" style="background: none;">
                               <i class="ti ti-table text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('HRM System Setup')); ?></span></a>
                </li>

            <?php endif; ?>

            <?php if(\Auth::user()->type == 'super admin'): ?>
                <?php echo $__env->make('landingpage::menu.landingpage', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>

            <?php if(Gate::check('Manage System Settings')): ?>
                <li class="dash-item ">
                    <a href="<?php echo e(route('settings.index')); ?>" class="dash-link text-white hover:text-white text-lg flex items-center space-x-2"><span
                            class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i class="ti ti-settings text-white text-[30px]"></i></span><span
                            class="dash-mtext"><?php echo e(__('Settings')); ?></span></a>
                </li>
            <?php endif; ?>
            <!--------------------- Start System Setup ----------------------------------->

            <?php if(\Auth::user()->type != 'super admin'): ?>

                <?php if(Gate::check('Manage Plan') || Gate::check('Manage Order') || Gate::check('Manage Company Settings')): ?>
                    <li class="dash-item dash-hasmenu">
                        <a href="#!" class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2">
                            <span class="dash-micon text-white text-[30px] shadow-none" style="background: none;"><i class="ti ti-settings text-white text-[30px]"></i></span><span
                                class="dash-mtext"><?php echo e(__('System Setup')); ?></span><span
                                class="dash-arrow">
                                <i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">
                            <?php if(Gate::check('Manage Company Settings')): ?>
                                <li
                                    class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'company-setting' ? ' active' : ''); ?>">
                                    <a href="<?php echo e(route('settings.index')); ?>"
                                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg flex items-center space-x-2"><?php echo e(__('System Settings')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('Manage Plan')): ?>
                                <li
                                    class="dash-item<?php echo e(Request::route()->getName() == 'plans.index' || Request::route()->getName() == 'stripe' ? ' active' : ''); ?>">
                                    <a href="<?php echo e(route('plans.index')); ?>"
                                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"><?php echo e(__('Setup Subscription Plan')); ?></a>
                                </li>
                            <?php endif; ?>
                            <li
                                class="dash-item<?php echo e(Request::route()->getName() == 'referral-program.company' ? ' active' : ''); ?>">
                                <a href="<?php echo e(route('referral-program.company')); ?>"
                                    class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"><?php echo e(__('Referral Program')); ?></a>
                            </li>
                            <?php if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company'): ?>
                                <li
                                    class="dash-item <?php echo e(Request::segment(1) == 'order' ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('order.index')); ?>"
                                        class="dash-link text-white hover:text-white hover:bg-[#001a3b] text-lg"><?php echo e(__('Order')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!--------------------- End System Setup ----------------------------------->
</ul>

</div>
</div>
</nav>
<?php /**PATH D:\TEST_time\resources\views/partial/Admin/menu.blade.php ENDPATH**/ ?>