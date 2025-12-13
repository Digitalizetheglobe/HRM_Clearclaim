<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Project;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Task;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Ticket;
use App\Models\TimeSheet;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Meeting;
use App\Models\AttendanceEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json(['results' => []]);
            }

            $results = [];
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['results' => []]);
            }
            
            try {
                $creatorId = $user->creatorId();
            } catch (\Exception $e) {
                Log::error('Error getting creatorId: ' . $e->getMessage());
                return response()->json(['results' => [], 'error' => 'Error getting user information'], 500);
            }
            
            // For super admin, search all records
            $isSuperAdmin = $user->type === 'super admin';

            // First, find employees matching the search query
            $employees = Employee::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('employee_id', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();

            // For each employee found, show all their related information
            foreach ($employees as $employee) {
                // Employee Profile
                $results[] = [
                    'type' => 'Employee Profile',
                    'title' => $employee->name . ' - Profile',
                    'subtitle' => $employee->email . ' | ' . ($employee->employee_id ?? 'N/A'),
                    'url' => route('employee.show', Crypt::encrypt($employee->id)),
                    'icon' => 'ti ti-user',
                    'category' => 'Employee',
                    'employee_id' => $employee->id
                ];

                // Employee Leaves
                try {
                    $leaves = Leave::when(!$isSuperAdmin, function($q) use ($creatorId) {
                            $q->where('created_by', $creatorId);
                        })
                        ->where('employee_id', $employee->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                } catch (\Exception $e) {
                    $leaves = collect([]);
                }

                foreach ($leaves as $leave) {
                    try {
                        $leaveType = \App\Models\LeaveType::find($leave->leave_type_id);
                        $leaveTypeTitle = $leaveType ? $leaveType->title : 'N/A';
                        $results[] = [
                            'type' => 'Leave Request',
                            'title' => $employee->name . ' - Leave (' . $leaveTypeTitle . ')',
                            'subtitle' => 'Status: ' . ucfirst($leave->status ?? 'Pending') . ' | ' . ($leave->start_date ?? '') . ' to ' . ($leave->end_date ?? ''),
                            'url' => route('leave.index') . '?employee_id=' . $employee->id,
                            'icon' => 'ti ti-calendar-off',
                            'category' => 'Leave',
                            'employee_id' => $employee->id
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                // Employee Tickets
                try {
                    $tickets = Ticket::when(!$isSuperAdmin, function($q) use ($creatorId) {
                            $q->where('created_by', $creatorId);
                        })
                        ->where('employee_id', $employee->user_id ?? 0)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                } catch (\Exception $e) {
                    $tickets = collect([]);
                }

                foreach ($tickets as $ticket) {
                    $results[] = [
                        'type' => 'Ticket',
                        'title' => $employee->name . ' - ' . $ticket->title,
                        'subtitle' => 'Status: ' . ucfirst($ticket->status ?? 'Open') . ' | Priority: ' . ucfirst($ticket->priority ?? 'Medium'),
                        'url' => route('ticket.index') . '?ticket_id=' . $ticket->id,
                        'icon' => 'ti ti-ticket',
                        'category' => 'Ticket',
                        'employee_id' => $employee->id
                    ];
                }

                // Employee TimeSheets
                try {
                    $timeSheets = TimeSheet::when(!$isSuperAdmin, function($q) use ($creatorId) {
                            $q->where('created_by', $creatorId);
                        })
                        ->where('employee_id', $employee->user_id ?? 0)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                } catch (\Exception $e) {
                    $timeSheets = collect([]);
                }

                foreach ($timeSheets as $timeSheet) {
                    $results[] = [
                        'type' => 'TimeSheet',
                        'title' => $employee->name . ' - TimeSheet',
                        'subtitle' => ($timeSheet->full_name ?? 'N/A') . ' | Date: ' . ($timeSheet->date ?? 'N/A'),
                        'url' => route('timesheet.show', $timeSheet->id),
                        'icon' => 'ti ti-clock',
                        'category' => 'TimeSheet',
                        'employee_id' => $employee->id
                    ];
                }

                // Employee Attendance
                try {
                    $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                        ->orderBy('date', 'desc')
                        ->limit(3)
                        ->get();
                } catch (\Exception $e) {
                    $attendance = collect([]);
                }

                if ($attendance->count() > 0) {
                    $results[] = [
                        'type' => 'Attendance',
                        'title' => $employee->name . ' - Attendance Records',
                        'subtitle' => 'Recent attendance records',
                        'url' => route('attendanceemployee.index') . '?employee_id=' . $employee->id,
                        'icon' => 'ti ti-clock-check',
                        'category' => 'Attendance',
                        'employee_id' => $employee->id
                    ];
                }
            }

            // Also search for other entities (non-person specific)
            // Search Users
            $users = User::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->whereNotIn('id', $employees->pluck('user_id')->filter())
                ->limit(5)
                ->get();

            foreach ($users as $userItem) {
                $results[] = [
                    'type' => 'User',
                    'title' => $userItem->name,
                    'subtitle' => $userItem->email . ' | ' . ucfirst($userItem->type),
                    'url' => route('user.index'),
                    'icon' => 'ti ti-users',
                    'category' => 'User'
                ];
            }

            // Search Projects
            $projects = Project::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where(function($q) use ($query) {
                    $q->where('project_name', 'LIKE', "%{$query}%")
                      ->orWhere('location', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($projects as $project) {
                $results[] = [
                    'type' => 'Project',
                    'title' => $project->project_name,
                    'subtitle' => $project->location ?? 'N/A',
                    'url' => route('projects.index'),
                    'icon' => 'ti ti-building',
                    'category' => 'Project'
                ];
            }

            // Search Departments
            $departments = Department::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($departments as $department) {
                $results[] = [
                    'type' => 'Department',
                    'title' => $department->name,
                    'subtitle' => 'Department',
                    'url' => route('department.index'),
                    'icon' => 'ti ti-building-store',
                    'category' => 'Department'
                ];
            }

            // Search Designations
            $designations = Designation::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($designations as $designation) {
                $results[] = [
                    'type' => 'Designation',
                    'title' => $designation->name,
                    'subtitle' => 'Designation',
                    'url' => route('designation.index'),
                    'icon' => 'ti ti-briefcase',
                    'category' => 'Designation'
                ];
            }

            // Search Branches
            $branches = Branch::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($branches as $branch) {
                $results[] = [
                    'type' => 'Branch',
                    'title' => $branch->name,
                    'subtitle' => 'Branch',
                    'url' => route('branch.index'),
                    'icon' => 'ti ti-map-pin',
                    'category' => 'Branch'
                ];
            }

            // Search Tasks (only if table exists)
            try {
                $tasks = Task::when(!$isSuperAdmin, function($q) use ($creatorId) {
                        $q->where('created_by', $creatorId);
                    })
                    ->where('title', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();

                foreach ($tasks as $task) {
                    $results[] = [
                        'type' => 'Task',
                        'title' => $task->title,
                        'subtitle' => 'Task',
                        'url' => route('task.index'),
                        'icon' => 'ti ti-checklist',
                        'category' => 'Task'
                    ];
                }
            } catch (\Exception $e) {
                // Tasks table doesn't exist, skip it
            }

            // Search Events
            $events = Event::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('title', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($events as $event) {
                $results[] = [
                    'type' => 'Event',
                    'title' => $event->title,
                    'subtitle' => 'Event',
                    'url' => route('event.index'),
                    'icon' => 'ti ti-calendar-event',
                    'category' => 'Event'
                ];
            }

            // Search Announcements
            $announcements = Announcement::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('title', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($announcements as $announcement) {
                $results[] = [
                    'type' => 'Announcement',
                    'title' => $announcement->title,
                    'subtitle' => 'Announcement',
                    'url' => route('announcement.index'),
                    'icon' => 'ti ti-speakerphone',
                    'category' => 'Announcement'
                ];
            }

            // Search Meetings
            $meetings = Meeting::when(!$isSuperAdmin, function($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('title', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($meetings as $meeting) {
                $results[] = [
                    'type' => 'Meeting',
                    'title' => $meeting->title,
                    'subtitle' => 'Meeting',
                    'url' => route('meeting.index'),
                    'icon' => 'ti ti-video',
                    'category' => 'Meeting'
                ];
            }

            return response()->json(['results' => array_slice($results, 0, 50)]);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query' => $request->get('q', ''),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'results' => [], 
                'error' => 'An error occurred while searching: ' . $e->getMessage()
            ], 500);
        }
    }
}
