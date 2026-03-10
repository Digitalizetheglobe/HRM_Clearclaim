# Attendance Status System Changes

## Overview
The attendance system has been enhanced to provide more granular status tracking with proper late mark handling and punch-out miss detection.

## New Status Types

### 1. **Present (P)**
- **Condition**: Worked ≥ 8.5 hours (510 minutes) with no late mark issues
- **Display**: Green badge in web interface, "P" in export

### 2. **Late (L)**
- **Condition**: Clock-in late AND ≤ 3 late marks in current month (excluding current day)
- **Display**: Yellow badge in web interface, "L" in export
- **Note**: First 3 late marks are allowed and show as "Late" status with full day credit if worked ≥8.5 hours

### 3. **Half Day (Late) (HL)**
- **Condition**: Clock-in late AND ≥ 3 late marks already exist in current month (this is 4th+ late mark)
- **Display**: Red badge in web interface, "HL" in export
- **Note**: For 4th+ late marks, system automatically calculates 4.5 hours from punch-in time

### 4. **Half Day (Punch Miss) (HP)**
- **Condition**: Clock-in exists but no clock-out (missed punch-out)
- **Display**: Blue badge in web interface, "HP" in export
- **Note**: System automatically calculates 4.5 hours from punch-in time

### 5. **Half Day (H)**
- **Condition**: Worked < 4.5 hours (with proper clock-in and clock-out)
- **Display**: Gray badge in web interface, "H" in export

### 6. **Absent (A)**
- **Condition**: No clock-in at all
- **Display**: Dark badge in web interface, "A" in export

## Logic Flow

```
1. Check clock_in exists → Absent if no
2. Check if late mark → Determine late status
3. Count late marks in month (excluding current day) → Apply penalty if ≥3 already exist
4. Check clock_out exists → Half Day (Punch Miss) if missing
5. Calculate worked hours → Apply thresholds
6. For late marks:
   - If ≤3 late marks already: 
     * Worked ≥8.5 hours → "Present" (full day credit)
     * Worked <8.5 hours → "Late" (partial credit)
   - If ≥3 late marks already: "Half Day (Late)" (auto-calculate 4.5 hours)
7. For non-late marks:
   - Worked ≥8.5 hours → "Present"
   - Worked <4.5 hours → "Half Day"
   - 4.5-8.5 hours → "Half Day"
8. Apply final status based on all conditions
```

## Key Changes

### Model Updates (AttendanceEmployee.php)
- Added new status constants:
  - `STATUS_LATE = 'Late'`
  - `STATUS_HALF_DAY_LATE = 'Half Day (Late)'`
  - `STATUS_HALF_DAY_PUNCH_MISS = 'Half Day (Punch Miss)'`

### Controller Updates (AttendanceEmployeeController.php)
- Enhanced `calculateAttendanceStatusWithNewRules()` method
- Added `countLateMarksInMonthExcludingCurrent()` for accurate late mark counting
- Added `handleHalfDayLateMark()` for automatic 4.5 hour calculation
- Updated missing punch-out processing logic
- Added `getStatusAbbreviation()` helper method
- Improved late mark counting and penalty application

### View Updates

#### Index Table (attendance/index.blade.php)
- Added color-coded status badges:
  - Present: Green (`bg-success`)
  - Late: Yellow (`bg-warning`)
  - Half Day (Late): Red (`bg-danger`)
  - Half Day (Punch Miss): Blue (`bg-info`)
  - Half Day: Gray (`bg-secondary`)
  - Absent: Dark (`bg-dark`)

#### Export (attendance/export.blade.php)
- Updated status abbreviations:
  - Present → P
  - Late → L
  - Half Day (Late) → HL
  - Half Day (Punch Miss) → HP
  - Half Day → H
  - Absent → A

### Missing Punch-Out Logic
- Automatically calculates 4.5 hours from punch-in time
- Caps at end of day (23:59:59) if calculation exceeds
- Applies appropriate status based on late mark count

### 4th+ Late Mark Logic
- Automatically calculates clock-out as punch-in + 4 hours 30 minutes
- Updates status to "Half Day (Late)"
- Calculates early leaving and sets overtime to zero
- Ensures consistent half-day credit for excessive late marks

## Migration
- Created migration to update existing attendance records
- Recalculates all statuses using new logic
- Preserves existing data while applying enhanced rules

## Benefits
1. **Clearer Status Tracking**: Distinguishes between different types of half days
2. **Fair Late Mark System**: First 3 late marks allowed, then penalty applies
3. **Better Export Clarity**: Meaningful abbreviations instead of single letters
4. **Visual Distinction**: Color-coded badges for quick status identification
5. **Automated Processing**: Handles missing punch-outs intelligently

## Usage Examples

### Scenario 1: Employee arrives late (4th time this month)
- Clock-in: 10:30 AM (late)
- Previous late marks this month: 3
- **Status**: Half Day (Late)
- **Action**: System automatically calculates clock-out as 3:00 PM (4.5 hours later)

### Scenario 2: Employee arrives late (2nd time this month) - works full day
- Clock-in: 10:30 AM (late)
- Clock-out: 7:00 PM
- Total: 8.5 hours
- Previous late marks this month: 1
- **Status**: Present - because it's within the first 3 late marks and worked full day

### Scenario 3: Employee arrives late (1st time this month) - partial day
- Clock-in: 10:30 AM (late)
- Clock-out: 6:00 PM
- Total: 7.5 hours
- Previous late marks this month: 0
- **Status**: Late - because it's within the first 3 late marks but didn't work full 8.5 hours

### Scenario 4: Employee works normal hours, not late
- Clock-in: 9:00 AM
- Clock-out: 6:00 PM
- Total: 9.0 hours
- **Status**: Present - normal full day

### Scenario 5: Employee works genuine half day (not late)
- Clock-in: 9:00 AM
- Clock-out: 1:00 PM
- Total: 4.0 hours
- **Status**: Half Day - genuine half day (< 4.5 hours)

### Scenario 6: Employee forgets to punch out
- Clock-in: 9:00 AM
- Clock-out: Missing
- **Status**: Half Day (Punch Miss) - automatically calculated 4.5 hours

## Implementation Notes
- Department-specific punch-in times are respected
- Late mark counting is per employee, per month
- All calculations include date context for accuracy
- Export format remains compact but more informative
