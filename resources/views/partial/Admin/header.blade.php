@php
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
@endphp

@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg" style="background: linear-gradient(to right, #fff, #fff); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
@else
    <header class="dash-header" style="background: linear-gradient(to right, #0a3772, #008ecc);">
@endif

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
        src="{{ !empty(Auth::user()->avatar) 
                ? asset('storage/uploads/avatar/' . Auth::user()->avatar) 
                : asset('storage/uploads/avatar/avatar.png') }}"
        class="header-avtar" 
        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background-color: white;">
</span>

                    <span class="hide-mob ms-2" style="background-color: white;">
                        {{ 'Hi, ' . Auth::user()->name . '!' }}
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob" style="background-color: white;"></i>
                    </span>
                </a>
                <div class="dropdown-menu dash-h-dropdown" style="background-color: white;">
                    <a href="{{ route('profile') }}" class="dropdown-item" style="background-color: white;">
                        <i class="ti ti-user"></i>
                        <span>{{ __('My Profile') }}</span>
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                       style="background-color: white;">
                        <i class="ti ti-power"></i>
                        <span>{{ __('Logout') }}</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
    
    <!-- Global Search Bar -->
    <div class="search-container" style=" display: flex; justify-content: center; align-items: center; flex-grow: 1; position: relative; max-width: 600px; margin: 0 auto;">
        <div class="global-search-wrapper" style="position: relative; width: 100%;">
            <input 
                type="text" 
                id="global-search-input" 
                class="form-control" 
                placeholder="Search employees, projects, departments..." 
                autocomplete="off"
                style="width: 100%; padding: 10px 40px 10px 15px; border-radius: 25px; border: 2px solid #0a3772; background: rgba(255,255,255,0.9); color: #333; font-size: 14px;"
            />
            <i class="ti ti-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none;"></i>
            <div id="search-results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; max-height: 400px; overflow-y: auto; margin-top: 5px;">
                <div id="search-results-content"></div>
            </div>
        </div>
    </div>

    <div class="ms-auto" style="display: flex; justify-content: flex-end; align-items: center;">
        <ul class="list-unstyled" style="display: flex; align-items: center;">
            {{-- Unified Notification Bell for All Users --}}
            <li class="dropdown dash-h-item drp-notification">
                <a class="dash-head-link dropdown-toggle arrow-none me-0 position-relative" 
                    data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false" id="notification-bell">
                    <i class="ti ti-bell fs-5"></i>
                    @if($unreadNotificationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" style="min-width: 350px;">
                    <div class="noti-header px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ __('Notifications') }}</h6>
                        @if($unreadNotificationsCount > 0)
                            <small>
                                <a href="javascript:void(0)" id="mark-all-read" class="text-primary">
                                    {{ __('Mark all as read') }}
                                </a>
                            </small>
                        @endif
                    </div>
                    <div class="noti-body" style="max-height: 400px; overflow-y: auto;" id="notifications-list">
                        @forelse($notifications as $notification)
                            <a href="javascript:void(0)" 
                               class="notification-item d-block p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}" 
                               data-notification-id="{{ $notification->id }}"
                               data-url="{{ $notification->data['url'] ?? '#' }}"
                               data-leave-id="{{ $notification->data['leave_id'] ?? '' }}"
                               style="text-decoration: none; color: inherit; cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        @if(isset($notification->data['status']))
                                            @if($notification->data['status'] == 'Pending')
                                                <i class="ti ti-clock text-warning fs-4"></i>
                                            @elseif($notification->data['status'] == 'Approved')
                                                <i class="ti ti-check-circle text-success fs-4"></i>
                                            @elseif($notification->data['status'] == 'Reject')
                                                <i class="ti ti-x-circle text-danger fs-4"></i>
                                            @else
                                                <i class="ti ti-bell text-info fs-4"></i>
                                            @endif
                                        @else
                                            <i class="ti ti-bell text-info fs-4"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1" style="font-size: 14px;">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if(!$notification->read_at)
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-center p-4" id="no-notifications">
                                <i class="ti ti-bell-off fs-1 text-muted"></i>
                                <p class="mb-0 text-muted mt-2">{{ __('No notifications') }}</p>
                            </div>
                        @endforelse
                    </div>
                    @if($notifications->count() > 0)
                    <div class="noti-footer border-top p-2">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-danger" id="clear-all-notifications">
                                <i class="ti ti-trash me-1"></i>{{ __('Clear All') }}
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </li>
        </ul>
    </div>

</div>
</header>

@push('scripts')
    <script>
        $('#msg-btn').click(function() {
            let contactsPage = 1;
            let contactsLoading = false;
            let noMoreContacts = false;
            $.ajax({
                url: url + "/getContacts",
                method: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
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
                                const userType = '{{ Auth::user()->type }}';
                                if (userType === 'company' || userType === 'hr') {
                                    // Create a temporary link element to trigger the existing modal system
                                    const tempLink = $('<a>', {
                                        'href': '#',
                                        'data-url': `/leave/${leaveId}/action`,
                                        'data-ajax-popup': 'true',
                                        'data-size': 'md',
                                        'data-title': '{{ __("Leave Action") }}'
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
                        show_toastr('{{ __("Success") }}', '{{ __("All notifications marked as read") }}', 'success');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            // Clear all notifications
            document.getElementById('clear-all-notifications')?.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!confirm('{{ __("Are you sure you want to clear all notifications?") }}')) {
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
                                <p class="mb-0 text-muted mt-2">{{ __('No notifications') }}</p>
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
                        show_toastr('{{ __("Success") }}', '{{ __("All notifications cleared") }}', 'success');
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
                                    <p class="mb-0 text-muted mt-2">{{ __('No notifications') }}</p>
                                </div>
                            `;
                            document.querySelector('.noti-footer')?.remove();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });

        // Global Search Functionality
        (function() {
            const searchInput = document.getElementById('global-search-input');
            const searchResults = document.getElementById('search-results');
            const searchResultsContent = document.getElementById('search-results-content');
            let searchTimeout;

            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });

            searchInput.addEventListener('focus', function() {
                if (searchResultsContent.innerHTML.trim() !== '') {
                    searchResults.style.display = 'block';
                }
            });

            // Close search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });

            // Handle keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.style.display = 'none';
                    searchInput.blur();
                }
            });

            function performSearch(query) {
                fetch(`{{ route('search') }}?q=${encodeURIComponent(query)}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.error || 'Server error: ' + response.status);
                        }).catch(() => {
                            throw new Error('Server error: ' + response.status);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.results) {
                        displayResults(data.results);
                    } else {
                        searchResultsContent.innerHTML = '<div class="p-3 text-center text-muted">No results found</div>';
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    let errorMsg = error.message || 'Error performing search. Please try again.';
                    searchResultsContent.innerHTML = `<div class="p-3 text-center text-danger">${errorMsg}</div>`;
                    searchResults.style.display = 'block';
                });
            }

            function displayResults(results) {
                if (results.length === 0) {
                    searchResultsContent.innerHTML = '<div class="p-3 text-center text-muted">No results found</div>';
                    searchResults.style.display = 'block';
                    return;
                }

                // Group results by employee_id or category
                const grouped = {};
                const otherResults = [];
                
                results.forEach(result => {
                    if (result.employee_id) {
                        if (!grouped[result.employee_id]) {
                            grouped[result.employee_id] = [];
                        }
                        grouped[result.employee_id].push(result);
                    } else {
                        otherResults.push(result);
                    }
                });

                let html = '<div style="padding: 4px; max-height: 500px; overflow-y: auto;">';
                
                // Display grouped employee results first
                Object.keys(grouped).forEach(employeeId => {
                    const employeeResults = grouped[employeeId];
                    const employeeProfile = employeeResults.find(r => r.type === 'Employee Profile');
                    const employeeName = employeeProfile ? employeeProfile.title.replace(' - Profile', '') : 'Employee';
                    
                    html += `
                        <div style="background: #f8f9fa; padding: 8px; margin: 4px 0; border-radius: 4px; border-left: 3px solid #0a3772;">
                            <div style="font-weight: 700; color: #0a3772; font-size: 13px; margin-bottom: 6px;">
                                <i class="ti ti-user" style="margin-right: 6px;"></i>${escapeHtml(employeeName)}
                            </div>
                    `;
                    
                    employeeResults.forEach(result => {
                        html += `
                            <a href="${result.url}" class="search-result-item" style="display: block; padding: 8px 8px 8px 24px; border-bottom: 1px solid #e9ecef; text-decoration: none; color: inherit; transition: background 0.2s; border-radius: 3px; margin: 2px 0;" 
                               onmouseover="this.style.background='#e9ecef'" 
                               onmouseout="this.style.background='transparent'">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="${result.icon}" style="font-size: 16px; color: #0a3772;"></i>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500; color: #333; font-size: 13px; margin-bottom: 2px;">${escapeHtml(result.title)}</div>
                                        <div style="font-size: 11px; color: #666;">${escapeHtml(result.subtitle)}</div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    
                    html += '</div>';
                });

                // Display other results
                if (otherResults.length > 0) {
                    html += '<div style="margin-top: 12px; padding-top: 12px; border-top: 2px solid #e9ecef;">';
                    otherResults.forEach(result => {
                        html += `
                            <a href="${result.url}" class="search-result-item" style="display: block; padding: 10px; border-bottom: 1px solid #f0f0f0; text-decoration: none; color: inherit; transition: background 0.2s; border-radius: 3px;" 
                               onmouseover="this.style.background='#f8f9fa'" 
                               onmouseout="this.style.background='white'">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="${result.icon}" style="font-size: 18px; color: #0a3772;"></i>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: #333; margin-bottom: 4px; font-size: 13px;">${escapeHtml(result.title)}</div>
                                        <div style="font-size: 11px; color: #666;">${escapeHtml(result.subtitle)}</div>
                                        <div style="font-size: 10px; color: #999; margin-top: 2px;">${result.type}</div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                }
                
                html += '</div>';
                searchResultsContent.innerHTML = html;
                searchResults.style.display = 'block';
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();
    </script>
@endpush