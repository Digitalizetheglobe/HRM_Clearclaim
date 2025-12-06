<?php
    use App\Models\Utility;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    $users = Auth::user();
    $currantLang = $users->currentLanguage();
    $profile = asset('storage/uploads/avatar/'); // Updated path to public storage
    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
        ->where('seen', 0)
        ->count();
    $unseen_count = DB::select('SELECT from_id, COUNT(*) AS totalmasseges FROM ch_messages WHERE seen = 0 GROUP BY from_id');

    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::id())
        ->where('seen', 0)
        ->count();

    // Get Laravel notifications for the current user
    $notifications = Auth::user()->notifications()->take(10)->get();
    $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
?>

<?php if(isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on'): ?>
    <header class="dash-header transprent-bg" style="background: linear-gradient(to right, #fff, #fff); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
<?php else: ?>
    <header class="dash-header" style="background: linear-gradient(to right, #0a3772, #008ecc);">
<?php endif; ?>

<div class="header-wrapper" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
    <div class="me-auto dash-mob-drp">
        <ul class="list-unstyled" style="display: flex; align-items: center;">
            <li class="dash-h-item mob-hamburger">
                <a href="#!" class="dash-head-link" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
            </li>
            <li class="dropdown dash-h-item drp-company">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                   role="button" aria-haspopup="false" aria-expanded="false" style="background-color: white;">
<span class="theme-avtar" style="background-color: white;">
    <img alt="User Avatar"
        src="<?php echo e(!empty(Auth::user()->avatar) 
                ? asset('storage/uploads/avatar/' . Auth::user()->avatar) 
                : asset('storage/uploads/avatar/avatar.png')); ?>"
        class="header-avtar" 
        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background-color: white;">
</span>

                    <span class="hide-mob ms-2" style="background-color: white;">
                        <?php echo e('Hi, ' . Auth::user()->name . '!'); ?>

                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob" style="background-color: white;"></i>
                    </span>
                </a>
                <div class="dropdown-menu dash-h-dropdown" style="background-color: white;">
                    <a href="<?php echo e(route('profile')); ?>" class="dropdown-item" style="background-color: white;">
                        <i class="ti ti-user"></i>
                        <span><?php echo e(__('My Profile')); ?></span>
                    </a>
                    <a href="<?php echo e(route('logout')); ?>" class="dropdown-item"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                       style="background-color: white;">
                        <i class="ti ti-power"></i>
                        <span><?php echo e(__('Logout')); ?></span>
                    </a>
                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                        <?php echo csrf_field(); ?>
                    </form>
                </div>
            </li>
        </ul>
    </div>
    
    <!-- Marquee Section for Daily Quote -->
    <div class="quote-container" style="display: flex; justify-content: center; align-items: center; flex-grow: 1;">
        <marquee behavior="scroll" direction="left" scrollamount="6" style="color: #0a3c77; font-size: 18px; font-weight: bold; width: 100%;margin: left 11px;">
            " <?php echo e($quote->quote ?? 'No quote for today!!'); ?> "
        </marquee>
    </div>

    <div class="ms-auto" style="display: flex; justify-content: flex-end; align-items: center;">
        <ul class="list-unstyled" style="display: flex; align-items: center;">
            
            <li class="dropdown dash-h-item drp-notification">
                <a class="dash-head-link dropdown-toggle arrow-none me-0 position-relative" 
                    data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false" id="notification-bell">
                    <i class="ti ti-bell fs-5"></i>
                    <?php if($unreadNotificationsCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                            <?php echo e($unreadNotificationsCount); ?>

                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" style="min-width: 350px;">
                    <div class="noti-header px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?php echo e(__('Notifications')); ?></h6>
                        <?php if($unreadNotificationsCount > 0): ?>
                            <small>
                                <a href="javascript:void(0)" id="mark-all-read" class="text-primary">
                                    <?php echo e(__('Mark all as read')); ?>

                                </a>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="noti-body" style="max-height: 400px; overflow-y: auto;" id="notifications-list">
                        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="javascript:void(0)" 
                               class="notification-item d-block p-3 border-bottom <?php echo e($notification->read_at ? '' : 'bg-light'); ?>" 
                               data-notification-id="<?php echo e($notification->id); ?>"
                               data-url="<?php echo e($notification->data['url'] ?? '#'); ?>"
                               data-leave-id="<?php echo e($notification->data['leave_id'] ?? ''); ?>"
                               style="text-decoration: none; color: inherit; cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <?php if(isset($notification->data['status'])): ?>
                                            <?php if($notification->data['status'] == 'Pending'): ?>
                                                <i class="ti ti-clock text-warning fs-4"></i>
                                            <?php elseif($notification->data['status'] == 'Approved'): ?>
                                                <i class="ti ti-check-circle text-success fs-4"></i>
                                            <?php elseif($notification->data['status'] == 'Reject'): ?>
                                                <i class="ti ti-x-circle text-danger fs-4"></i>
                                            <?php else: ?>
                                                <i class="ti ti-bell text-info fs-4"></i>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <i class="ti ti-bell text-info fs-4"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1" style="font-size: 14px;"><?php echo e($notification->data['message'] ?? 'New notification'); ?></p>
                                        <small class="text-muted"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                                    </div>
                                    <?php if(!$notification->read_at): ?>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center p-4" id="no-notifications">
                                <i class="ti ti-bell-off fs-1 text-muted"></i>
                                <p class="mb-0 text-muted mt-2"><?php echo e(__('No notifications')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($notifications->count() > 0): ?>
                    <div class="noti-footer border-top p-2">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-danger" id="clear-all-notifications">
                                <i class="ti ti-trash me-1"></i><?php echo e(__('Clear All')); ?>

                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </div>

</div>
</header>

<?php $__env->startPush('scripts'); ?>
    <script>
        $('#msg-btn').click(function() {
            let contactsPage = 1;
            let contactsLoading = false;
            let noMoreContacts = false;
            $.ajax({
                url: url + "/getContacts",
                method: "GET",
                data: {
                    _token: "<?php echo e(csrf_token()); ?>",
                    page: contactsPage,
                    type: 'custom',
                },
                dataType: "JSON",
                success: (data) => {
                    if (contactsPage < 2) {
                        $(".count-listOfContacts").html(data.contacts);
                    } else {
                        $(".count-listOfContacts").append(data.contacts);
                    }
                    $('.count-listOfContacts').find('.messenger-list-item').each(function(e) {
                        $('.noti-body .activeStatus').remove()
                        $('.noti-body .avatar').remove()
                        $(this).find('span').remove()
                        $(this).find('p').addClass("d-inline")
                        $(this).find('b').css({
                            "position": "absolute",
                            "right": "50px"
                        });
                        $(this).find('tr').remove('td')
                    })
                },
                error: (error) => {
                    setContactsLoading(false);
                    console.error(error);
                },
            });
        })
        
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';
            
            // Handle notification item clicks
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.getAttribute('data-notification-id');
                    const url = this.getAttribute('data-url');
                    const leaveId = this.getAttribute('data-leave-id');
                    
                    // Mark notification as read
                    fetch(`/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove unread indicator
                            this.classList.remove('bg-light');
                            this.querySelector('.badge.bg-primary')?.remove();
                            
                            // Update notification count
                            updateNotificationCount();
                            
                            // If this is a leave notification and user is company/hr, open the action modal
                            if (leaveId && (url.includes('/leave/') || url.includes('action'))) {
                                // Check if user can approve/reject (company or hr)
                                const userType = '<?php echo e(Auth::user()->type); ?>';
                                if (userType === 'company' || userType === 'hr') {
                                    // Create a temporary link element to trigger the existing modal system
                                    const tempLink = $('<a>', {
                                        'href': '#',
                                        'data-url': `/leave/${leaveId}/action`,
                                        'data-ajax-popup': 'true',
                                        'data-size': 'md',
                                        'data-title': '<?php echo e(__("Leave Action")); ?>'
                                    });
                                    
                                    // Append to body temporarily
                                    $('body').append(tempLink);
                                    
                                    // Trigger click to open modal using existing system
                                    tempLink.trigger('click');
                                    
                                    // Store notification ID for deletion after action
                                    setTimeout(function() {
                                        $('#commonModal').data('notification-id', notificationId);
                                        // Remove temporary link
                                        tempLink.remove();
                                    }, 100);
                                } else {
                                    // For employees, redirect to leave index
                                    if (url && url !== '#') {
                                        window.location.href = url;
                                    }
                                }
                            } else {
                                // For other notifications, just redirect
                                if (url && url !== '#') {
                                    window.location.href = url;
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
            
            // Mark all as read
            document.getElementById('mark-all-read')?.addEventListener('click', function(e) {
                e.preventDefault();
                
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove all unread indicators
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.classList.remove('bg-light');
                            item.querySelector('.badge.bg-primary')?.remove();
                        });
                        
                        // Update notification count to 0
                        const badge = document.getElementById('notification-count');
                        if (badge) {
                            badge.remove();
                        }
                        
                        // Hide mark all as read link
                        this.closest('small').remove();
                        
                        // Show success message
                        show_toastr('<?php echo e(__("Success")); ?>', '<?php echo e(__("All notifications marked as read")); ?>', 'success');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            // Clear all notifications
            document.getElementById('clear-all-notifications')?.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!confirm('<?php echo e(__("Are you sure you want to clear all notifications?")); ?>')) {
                    return;
                }
                
                fetch('/notifications/clear-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear all notifications from UI
                        const notificationsList = document.getElementById('notifications-list');
                        notificationsList.innerHTML = `
                            <div class="text-center p-4" id="no-notifications">
                                <i class="ti ti-bell-off fs-1 text-muted"></i>
                                <p class="mb-0 text-muted mt-2"><?php echo e(__('No notifications')); ?></p>
                            </div>
                        `;
                        
                        // Remove count badge
                        const badge = document.getElementById('notification-count');
                        if (badge) {
                            badge.remove();
                        }
                        
                        // Hide footer
                        document.querySelector('.noti-footer')?.remove();
                        
                        // Show success message
                        show_toastr('<?php echo e(__("Success")); ?>', '<?php echo e(__("All notifications cleared")); ?>', 'success');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            // Function to update notification count
            function updateNotificationCount() {
                fetch('/notifications/count', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-count');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                        } else {
                            // Create badge if it doesn't exist
                            const bell = document.getElementById('notification-bell');
                            const newBadge = document.createElement('span');
                            newBadge.id = 'notification-count';
                            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                            newBadge.textContent = data.count;
                            bell.appendChild(newBadge);
                        }
                    } else {
                        if (badge) {
                            badge.remove();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Listen for modal form submission to delete notification after action
            $(document).on('submit', '#commonModal form', function(e) {
                const notificationId = $('#commonModal').data('notification-id');
                
                if (notificationId) {
                    // Delete the notification immediately
                    deleteNotificationFromUI(notificationId);
                    
                    // Close dropdown after a short delay
                    setTimeout(function() {
                        $('.dropdown-menu').removeClass('show');
                    }, 100);
                }
            });
            
            // Function to delete notification from UI
            function deleteNotificationFromUI(notificationId) {
                fetch(`/notifications/${notificationId}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the notification from UI
                        const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notificationItem) {
                            notificationItem.remove();
                        }
                        
                        // Update notification count
                        updateNotificationCount();
                        
                        // Check if there are any notifications left
                        const remainingNotifications = document.querySelectorAll('.notification-item').length;
                        if (remainingNotifications === 0) {
                            document.getElementById('notifications-list').innerHTML = `
                                <div class="text-center p-4" id="no-notifications">
                                    <i class="ti ti-bell-off fs-1 text-muted"></i>
                                    <p class="mb-0 text-muted mt-2"><?php echo e(__('No notifications')); ?></p>
                                </div>
                            `;
                            document.querySelector('.noti-footer')?.remove();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    </script>
<?php $__env->stopPush(); ?><?php /**PATH D:\TEST_time\resources\views/partial/Admin/header.blade.php ENDPATH**/ ?>