# CSS Not Loading When Running via CLI - Fix Guide

## Problem
CSS files are not loading when running Laravel project using `php artisan serve` (CLI server).

## ✅ SOLUTION APPLIED

**Issue Found**: `APP_URL` in `.env` was set to production URL (`https://hrm.risingspaces.in/`) instead of local development URL.

**Fix Applied**: 
- Changed `APP_URL` from `https://hrm.risingspaces.in/` to `http://127.0.0.1:8000`
- Cleared config cache
- Asset helper now generates correct local URLs

**Next Steps**:
1. **Restart your server**: Stop `php artisan serve` (Ctrl+C) and restart it
2. **Hard refresh browser**: Press `Ctrl + F5` to clear browser cache
3. CSS should now load correctly!

## Root Causes

1. **APP_URL Mismatch**: The `APP_URL` in `.env` doesn't match the actual server URL ⚠️ **THIS WAS THE ISSUE**
2. **Cache Issues**: Cached config/views may have old URLs
3. **Asset Path Resolution**: Asset helper generating incorrect paths
4. **Routing Conflicts**: Routes might be catching asset requests

## Solutions

### Solution 1: Set Correct APP_URL

Edit your `.env` file and set:
```
APP_URL=http://127.0.0.1:8000
```
or if using localhost:
```
APP_URL=http://localhost:8000
```

Then clear the config cache:
```bash
php artisan config:clear
php artisan config:cache
```

### Solution 2: Clear All Caches

Run these commands:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Solution 3: Verify Asset Paths

Make sure you're accessing the correct URL:
- If running `php artisan serve`, access: `http://127.0.0.1:8000`
- The asset helper will generate paths like: `/assets/css/style.css`
- These should resolve to: `http://127.0.0.1:8000/assets/css/style.css`

### Solution 4: Check Browser Console

Open browser DevTools (F12) and check:
1. Network tab - Are CSS files returning 404?
2. Console tab - Any JavaScript errors?
3. Check the actual URL being requested for CSS files

### Solution 5: Verify File Permissions

Ensure CSS files are readable:
```bash
# On Windows (PowerShell)
icacls public\assets\css\style.css
```

### Solution 6: Restart Server

After making changes, restart the server:
```bash
php artisan serve
```

## Quick Fix Checklist

- [ ] Set APP_URL in .env to match server URL
- [ ] Clear all caches (config, view, route, application)
- [ ] Restart `php artisan serve`
- [ ] Hard refresh browser (Ctrl+F5)
- [ ] Check browser console for 404 errors
- [ ] Verify CSS files exist in `public/assets/css/`

## Testing

After applying fixes:
1. Access your site: `http://127.0.0.1:8000`
2. View page source (Ctrl+U)
3. Check CSS link hrefs are correct
4. Click on CSS link URL to verify it loads
5. Check browser Network tab shows CSS files loading (status 200)

