NEXT TASK 13052026 NEW CLIENT REQUEST

Prepared from the latest client request list so we can start working through the items when needed.

1. Dashboard Direct Ticket Access
- Category: Bug
- Status: COMPLETED
- Description: Enable direct access to tickets from the dashboard by redirecting users to the ticket interior page when clicked.

2. Forgot Password Redirect Issue
- Category: Bug
- Status: Pending
- Description: Update password reset links and redirects to point to the client server instead of the development server: https://support.mcdonalds.mu/#

3. Email Sender Configuration
- Category: Bug
- Status: ON HOLD
- Description: Review why notifications are sent from encantodeveloper@gmail.com instead of the client email address.

4. Ticket Email Notification Redirect Fix
- Category: Bug
- Status: Pending
- Description: Review and resolve ticket email notification redirection issues to ensure correct user redirection.

5. User Creation Email Notification
- Category: Bug
- Status: Pending
- Description: Verify and correct automatic email notifications triggered during user creation.

6. Urgency Level Field Position
- Category: Bug
- Status: Pending
- Description: Move urgency level selection to the top of the ticket creation form under the Title of Request field.

7. SLA Reminder Threshold Notifications
- Category: New Feature
- Status: Pending
- Description: Introduce automated email reminders based on SLA priority until ticket assignment.
- SLA reminder intervals:
  - P1 every 1 hour
  - P2 every 2 hours
  - P3 every 4 hours
  - P4 every 24 hours
- Scope: Includes frontend and admin panel development.
- Distribution: MCD Team will provide department-level distribution email lists for these notifications.

8. SLA Resolution Timer
- Category: New Feature
- Status: Pending
- Description: Implement post-assignment SLA timers for each urgency level.
- SLA resolution targets:
  - P1 = 1 hour
  - P2 = 2 hours
  - P3 = 4 hours
  - P4 = 24 hours
- Note: The timer MUST be PAUSED when the ticket status is "Waiting for Parts".

9. Technician Resolution KPI Tracking
- Category: New Feature
- Status: Pending
- Description: Provide visibility on total time taken by technicians to resolve issues from ticket creation until closure.

10. Waiting for Parts Status
- Category: New Feature
- Status: Pending
- Description: Introduce a new ticket status called "Waiting for Parts" applicable to IT and Maintenance departments only.
- Workflow: CREATE -> ASSIGN -> IN PROGRESS -> WAITING FOR PARTS -> IN PROGRESS -> RESOLVED.

11. Trends Analysis Reporting Tab
- Category: New Feature
- Status: Pending
- Description: Add a "Trends Analysis" tab beside "Weekly Summary" with filters for Outlet, Department, SLA, Ticket Status, and Date Range.

12. SUPER ADMIN Role
- Category: New Feature
- Status: Pending
- Description: Add a SUPER ADMIN role with exclusive permissions to manage the admin panel and system configurations.
- Requested capabilities:
  - Allow SUPER ADMIN users to create and configure outlets directly from the admin panel (includes Outlet Phone, Email, and Name).
  - Allow SUPER ADMIN to create users and allocate accesses.
  - Allow SUPER ADMIN to modify SLA timers, reminder intervals, and SLA thresholds dynamically without developer intervention (e.g., changing P1 from 1 hour to 30 minutes).
  - Access to a new "SLA Management" sidebar item and a consolidated dashboard with User Management and Notifications widgets.

13. Disable User Account Functionality
- Category: New Feature
- Status: Pending
- Description: Add functionality to disable user accounts without deleting user data.

14. Outlet and User Additional Fields
- Category: New Feature
- Status: Pending
- Description: Allow entry of outlet/store phone number, outlet email, outlet name, and optional mobile number during user creation.

15. Complete Ticket History Tracking
- Category: New Feature
- Status: Pending
- Description: Implement ticket history tracking with timestamps for each status update throughout the workflow.

IMPLEMENTATION PLAN FOR POINTS 7 TO 15

Backward compatibility rule:
- Existing API response keys must remain unchanged.
- Existing API value types must remain unchanged.
- Changes to existing APIs should be additive only when needed.
- New functionality should prefer new tables, new optional fields, and new endpoints instead of repurposing old payloads.

Recommended phased execution:

Phase 1: Workflow foundation
- Goal: Add the backend structure needed for SLA, reporting, new statuses, and history without breaking current flows.
- Work items:
  - Review whether current ticket priority values should stay as they are or map internally to SLA levels P1 to P4.
  - Keep current `priority` response values unchanged unless the frontend explicitly changes.
  - Add a `ticket_histories` table to track status changes, assignment changes, SLA changes, and key workflow actions.
  - Add support for the `Waiting for Parts` status with backend validation limited to IT and Maintenance departments.
  - Add user account active/disabled support with a new field such as `is_active` or `account_status`.
- Notes:
  - This phase supports points 9, 10, 13, and 15.
  - This phase should be completed before building trend and KPI reporting.

Phase 2: SLA engine
- Goal: Build a configurable SLA system instead of hardcoding rules in multiple places.
- Work items:
  - Create SLA configuration tables such as:
    - `sla_levels`
    - `sla_reminder_rules`
    - optional `department_sla_rules`
  - Store reminder intervals, assignment thresholds, resolution thresholds, and active status in the database.
  - Add a scheduled background job to send reminder notifications for unassigned tickets:
    - P1 every 1 hour
    - P2 every 2 hours
    - P3 every 4 hours
    - P4 every 24 hours
  - Start SLA resolution tracking when a ticket is assigned.
  - Store SLA deadlines and current SLA state internally for future dashboard/report usage.
- Notes:
  - This phase covers points 7 and 8 directly.
  - Reminder sending should be logged to avoid duplicate sends.

Phase 3: Reporting and KPI
- Goal: Expose SLA and technician performance data without disturbing current ticket APIs.
- Work items:
  - Define technician KPI calculations:
    - ticket creation to resolution
    - assignment to resolution
    - resolution to closure if needed
  - Build new reporting endpoints for trends and KPI dashboards.
  - Add a `Trends Analysis` tab backed by new API endpoints with filters for:
    - Outlet
    - Department
    - SLA
    - Ticket Status
    - Date Range
  - Add backend aggregate queries for:
    - average resolution time
    - SLA breaches
    - ticket volume over time
    - technician performance trends
- Notes:
  - This phase covers points 9 and 11.
  - New reporting should use new endpoints instead of changing the shape of existing summary APIs.

Phase 4: SUPER ADMIN capability
- Goal: Introduce a new administrative layer safely and keep current admin flows working.
- Work items:
  - Add a new `super_admin` role in the database and middleware logic.
  - Separate normal admin capabilities from SUPER ADMIN-only configuration access.
  - Add SUPER ADMIN-only outlet management from the admin panel.
  - Add SUPER ADMIN-only user creation and access allocation tools.
  - Add settings management for SLA timers, reminder intervals, and thresholds so future changes do not require developer edits.
- Notes:
  - This phase covers point 12.
  - If access allocation needs more flexibility than roles alone, consider a permissions table rather than overloading role checks.

Phase 5: Data capture improvements
- Goal: Extend outlet and user data without disrupting current forms and API consumers.
- Work items:
  - Add outlet/store phone number, outlet email, outlet name, and optional user mobile number fields.
  - Review whether current `locations` table already represents outlets/stores.
  - If yes, extend `locations`.
  - If no, design a proper `outlets` table and map relationships carefully.
- Notes:
  - This phase covers point 14.
  - Existing response fields should remain unchanged; new fields should be optional additions only.

Detailed execution order:
1. Complete ticket history tracking
2. Disable user account functionality
3. Waiting for Parts status
4. SLA configuration tables and admin-ready structure
5. SLA reminder threshold notifications
6. SLA resolution timer
7. Technician resolution KPI tracking
8. Trends Analysis reporting tab
9. SUPER ADMIN role and permission split
10. Dynamic SLA configuration under SUPER ADMIN
11. Outlet and user additional fields

Database changes likely required:
- New tables:
  - `ticket_histories`
  - `sla_levels`
  - `sla_reminder_rules`
  - optional `department_sla_rules`
  - optional `permissions` and `role_permissions` if role-only access is not enough
- New columns likely required:
  - `users.is_active` or `users.account_status`
  - ticket SLA-related fields such as:
    - `sla_level`
    - `sla_assignment_due_at`
    - `sla_resolution_due_at`
    - `sla_status`
  - outlet/location additional contact fields

API strategy:
- Keep current endpoints stable.
- Use additive request fields only where required.
- Use additive response fields only where required by the frontend.
- Prefer new endpoints for:
  - trends analysis
  - KPI reporting
  - ticket history view
  - SLA configuration management
  - SUPER ADMIN access management

Frontend and admin panel impact:
- Ticket creation/edit flows:
  - urgency placement adjustments
  - SLA visibility where useful
  - Waiting for Parts shown only for allowed departments
- Dashboard and reporting:
  - new Trends Analysis tab
  - KPI cards/charts
  - SLA breach visibility
- Admin panel:
  - SUPER ADMIN settings screens
  - outlet configuration screens
  - access allocation screens
  - SLA configuration screens
- User management:
  - disable/enable account controls
  - additional outlet/user fields

Compatibility notes:
- Do not replace existing `priority` values unless the client confirms a full frontend change.
- Do not rename existing `status`, `location`, `department`, or auth response fields.
- Adding `Waiting for Parts` is safe at API level, but frontend status lists must be reviewed.
- Disabled users will change runtime behavior but should not require response shape changes.
- Reporting should be delivered through new endpoints so current dashboard consumers continue to work.

Open decisions to confirm before implementation:
- Should current priority values remain user-facing, with SLA levels mapped internally?
- Does `location` already mean outlet/store in the client's terminology?
- Should disabled users be logged out immediately and blocked from existing tokens?
- Is a single `super_admin` role enough, or is a full permissions system needed?
- For technician KPI, should the main resolution metric be from ticket creation or from ticket assignment?

Execution note:
- Before starting development on these items, prepare technical migrations based on the real local database schema so new features are built on top of accurate table definitions.
