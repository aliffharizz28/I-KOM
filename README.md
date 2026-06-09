# I-KOM — Sistem Penilaian dan Pengurusan Kursus Inovasi Digital & Komuniti Digital

A web-based assessment and course management system built for managing Special Interest Groups (SIG), student evaluations, attendance tracking, and assignment workflows.

---

## 🛠 Technologies

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Blade Templates, Tailwind CSS 4, Vite |
| Database | MySQL (via XAMPP) |
| PDF Export | Barryvdh DomPDF |
| Email | Laravel Mail (SMTP) |
| Auth | Laravel built-in authentication |

---

## ✨ Features

### Role-Based Access (4 Roles)

**Pentadbir (Admin)**
- Manage course sessions (semester settings)
- Register and assign SIG coordinators
- View and export SIG reports (PDF/CSV)
- Dashboard with overview of all SIGs, students, and coordinators

**Penyelaras SIG (Coordinator)**
- Register students (individual or bulk upload)
- Create and manage grading rubrics (criteria & sub-criteria)
- Create, publish, and manage assignments with file attachments
- Grade student submissions and review marks
- Conduct final assessments with weighted scoring and auto-grade calculation
- Verify attendance records and export reports (CSV)
- Email notifications to students on assignment publish

**Pelajar (Student)**
- View and submit assignments with file uploads
- Check published marks and grades
- View attendance records

**Majlis Tertinggi (Student Leader)**
- All student features, plus:
- Create and manage meeting sessions
- Record attendance for meetings

### Other Highlights
- Multi-session support (active semester scoping)
- Weighted rubric system with criteria → sub-criteria → descriptions
- Auto-deactivation of past-due assignments
- Attendance percentage auto-calculated into final assessment
- Collapsible sidebar with persistent state
- Password reset via email
- Rate-limited login for security

---

## 🔨 How I Built It

1. **Database Design** — Designed the relational schema: users (`pengguna`), students (`pelajar`), SIG groups, courses (`kursus`), assignments (`tugasan`), submissions (`penghantaran`), attendance (`kehadiran`/`perjumpaan`), grading rubrics (`kriteria`/`subkriteria`), and results (`penilaian`/`keputusan`).

2. **Authentication** — Used Laravel's `Authenticatable` model with a custom `pengguna` table and role-based middleware to control access per route group.

3. **Core Modules** — Built each module incrementally:
   - Student registration (individual + CSV bulk)
   - Assignment CRUD with publish/unpublish toggle
   - Rubric builder with SIG-specific sub-criteria
   - Attendance tracking with coordinator verification
   - Final assessment engine with weighted scoring

4. **Assessment Engine** — The grading system combines three sources: manual rubric marks, assignment scores (out of 10), and attendance percentage — all weighted and calculated server-side into a final grade (A+ to F).

5. **Reporting** — Added CSV export for both attendance and assessment data, plus PDF report generation using DomPDF.

6. **Frontend** — Styled with Tailwind CSS and Blade components, with a responsive sidebar and role-specific navigation.

---

## 📚 What I Learned

- **Role-based architecture** — Structuring middleware and route groups to cleanly separate access for 4 different user roles.
- **Eloquent relationships** — Working with complex model relationships (belongsTo, hasMany, through pivots) across a normalized database.
- **Multi-session scoping** — Ensuring all queries (students, assignments, grades) are scoped to the active course session to prevent data leaks between semesters.
- **Weighted scoring logic** — Implementing a flexible grading engine that pulls marks from multiple sources (rubric, assignments, attendance) and normalizes them into a single weighted score.
- **File handling** — Managing secure file uploads and downloads for assignment attachments and student submissions using Laravel Storage.
- **Email integration** — Sending automated email notifications to students when assignments are published.

---

## 🚀 How It Can Be Improved

- **API layer** — Build a REST API to support a mobile app companion.
- **Real-time notifications** — Replace email-only notifications with in-app real-time alerts using Laravel Broadcasting (Pusher/WebSockets).
- **Drag-and-drop rubric builder** — Make the rubric/sub-criteria setup more visual and interactive.
- **Analytics dashboard** — Add charts and trends (grade distribution, attendance over time) using Chart.js or similar.
- **Automated testing** — Add comprehensive Pest/PHPUnit test coverage for controllers and grading logic.
- **Audit logging** — Track who changed grades, verified attendance, etc. for accountability.
- **Pagination & search** — Improve performance on student lists and assignment tables with server-side pagination and filtering.
- **Localization** — Add full i18n support for English/Malay with Laravel's localization system.
