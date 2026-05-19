# Super Admin Registration

This document explains how to use the Super Admin Registration functionality in the HRM Clearclaim system.

## Overview

The Super Admin Registration allows creating a super admin user account with full system permissions. This is a restricted registration that can only be used once to prevent multiple super admin accounts.

## Features

- **Same Design as Login Form**: Uses the identical design and styling as the login form for consistency
- **Form Fields**: Name, Email, Password, and Password Confirmation
- **Validation**: Full form validation including unique email check
- **Security**: Only allows one super admin account to be created
- **Auto Role Assignment**: Automatically assigns the 'super admin' role
- **Email Verification**: Super admin accounts are automatically verified

## Access

You can access the super admin registration in two ways:

1. **Direct URL**: `http://your-domain.com/super-admin/register`
2. **From Login Page**: Click the "Register as Super Admin" link at the bottom of the login form

## Routes

- `GET /super-admin/register` - Display the registration form
- `POST /super-admin/register` - Process the registration

## Security Features

1. **Single Account Constraint**: Only one super admin account can be created. If a super admin already exists, the registration will show an error message.
2. **Guest Middleware**: Only guests (non-authenticated users) can access the registration.
3. **Role Assignment**: Automatically assigns the 'super admin' role from the Spatie Permission package.
4. **Password Confirmation**: Requires password confirmation to prevent typos.

## Files Created/Modified

### New Files
- `resources/views/auth/super-admin-register.blade.php` - Registration form view
- `app/Http/Controllers/Auth/SuperAdminRegisterController.php` - Registration controller

### Modified Files
- `routes/auth.php` - Added super admin registration routes
- `resources/views/auth/login.blade.php` - Added link to super admin registration

## Usage

1. Navigate to the super admin registration page
2. Fill in the required fields:
   - **Name**: Full name of the super admin
   - **Email**: Unique email address
   - **Password**: Secure password
   - **Password Confirmation**: Re-enter password for confirmation
3. Click "Register as Super Admin"
4. If successful, you'll be automatically logged in and redirected to the dashboard
5. If a super admin already exists, you'll see an error message

## Database Changes

The registration creates a user record with:
- `type`: 'super admin'
- `email_verified_at`: Current timestamp
- `lang`: 'en' (default)
- `created_by`: 0 (system created)

## Testing

To test the functionality:

1. Ensure the development server is running: `php artisan serve`
2. Visit: `http://127.0.0.1:8001/super-admin/register`
3. Fill out the form and submit
4. Verify the user is created with super admin role and permissions

## Notes

- This registration should only be used for initial system setup
- The super admin has full system access and can manage all aspects of the application
- Consider securing this route in production environments after initial setup
