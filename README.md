# College Event Management System
BCA Capstone Project 2026-27, COCSIT Latur. Stack: HTML, CSS, JavaScript, PHP, MySQL.

## Quick Start (XAMPP / WAMP)
1. Project is located in `C:\xampp\htdocs\event_management`.
2. Apache and MySQL are running in XAMPP.
3. Open [http://localhost/event_management/](http://localhost/event_management/).

## Demo Credentials
| Role | Email | Password | Features Accessible |
|---|---|---|---|
| **Head Admin** | `vaishnavikhatile@gmail.com` | `admin123` | Master Admin Control Center |
| **Faculty Coordinator** | `sachin.kulkarni@cocsit.edu` | `admin123` | Event Convener & Management |
| **Cultural & Sports Head** | `nrkadam@cocsit.edu` | `admin123` | Cultural Fest & Sports Meet Admin |
| **Tech Committee Lead** | `pooja.joshi@cocsit.edu` | `admin123` | Hackathons & Technical Bootcamp Admin |
| **Student (Demo 1)** | `rohit.shinde@cocsit.edu` | `student123` | Browse Events, 1-Click Register, Digital Event Pass |
| **Student (Demo 2)** | `ananya.deshmukh@cocsit.edu` | `student123` | Browse Events, 1-Click Register, Digital Event Pass |

*(Note: Both login screens feature 1-Click "Demo Login Autofill" buttons for instant evaluation!)*

## Interactive Features Added
- **Live Event Countdowns**: Real-time ticker clocks (Days, Hours, Minutes, Seconds) on the homepage spotlight, event details, and quick-view modals.
- **Dynamic Category Filtering**: Interactive category pills (`Technical`, `Workshop`, `Cultural`, `Sports`, `Seminar`) with real-time DOM card filtering without page reload.
- **Instant Search**: Real-time typing filter matching event name, organizer, venue, or keywords with live result counts.
- **Quick View Modal**: Glassmorphism preview modal with event details, Google Calendar link, and share button.
- **Printable Digital Event Pass**: Student admission badge with simulated QR verification code and dedicated print stylesheet (`window.print()`).
- **Calendar & Social Sharing**: 1-Click Google Calendar synchronization and WhatsApp / direct link sharing.
- **Animated Campus Stats**: Hero section counter metrics for total events, active registrations, and verified passes.

## Modules (as in the synopsis)
| Module | Files |
|---|---|
| Admin Management | `admin/login.php`, `admin/dashboard.php`, `admin/admins.php` |
| Event Management | `admin/events.php`, `admin/event_form.php`, `events.php`, `event.php` |
| Student Management | `register.php`, `login.php`, `student/profile.php` |
| Event Registration | `student/register_event.php`, `student/my_events.php`, `admin/registrations.php` |
| Attendance and Reports | `admin/attendance.php`, `admin/reports.php` (print + CSV) |

## Database Schema
`admin`, `student`, `events` (with `category`), `registrations`, `attendance`.
