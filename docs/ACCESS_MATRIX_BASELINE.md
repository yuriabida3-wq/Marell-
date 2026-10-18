# ACCESS MATRIX BASELINE

Generated: 2026-10-18T23:21:13+03:00
Actors: guest, principal, dos, bursar, teacher, guard
Method: GET only. Destructive / callback / OTP routes skipped.
Status codes only — no assertions.

Legend: `2xx` allowed · `302` redirect (login) · `403` denied · `404` not found · `5xx` error

| Route | URL | guest | principal | dos | bursar | teacher | guard |
|---|---|---|---|---|---|---|---|
| `(unnamed)` | `/api/mpesa/ping` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `(unnamed)` | `/up` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `home` | `/` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `about` | `/about` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `academics` | `/academics` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `fees` | `/fees` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `admissions` | `/admissions` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `results` | `/results` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `contact` | `/contact` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `pay` | `/pay` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `pay.wait` | `/pay/wait/999999` | 404 | 404 | 404 | 404 | 404 | 404 |
| `pay.check` | `/pay/check/999999` | 404 | 404 | 404 | 404 | 404 | 404 |
| `pay.success` | `/pay/success/999999` | 404 | 404 | 404 | 404 | 404 | 404 |
| `pay.receipt` | `/pay/receipt/999999` | 404 | 404 | 404 | 404 | 404 | 404 |
| `parent.dashboard` | `/parent` | 302 | 302 | 302 | 302 | 302 | 302 |
| `principal.dashboard` | `/principal` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.search` | `/principal/search` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.export-students` | `/principal/export/students` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.students.index` | `/principal/students` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.students.create` | `/principal/students/create` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.students.import` | `/principal/students/import` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.students.show` | `/principal/students/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.students.edit` | `/principal/students/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.finance.index` | `/principal/finance` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.finance.record` | `/principal/finance/record` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.finance.defaulters` | `/principal/finance/defaulters` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.finance.daily` | `/principal/finance/daily` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.finance.export` | `/principal/finance/export` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.finance.export-defaulters` | `/principal/finance/export-defaulters` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.sms.index` | `/principal/sms` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.users.index` | `/principal/users` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.users.create` | `/principal/users/create` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.users.edit` | `/principal/users/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.settings` | `/principal/settings` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `dos.dashboard` | `/dos` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.classes.index` | `/dos/classes` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.timetable.index` | `/dos/timetable` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.timetable.teacher` | `/dos/timetable/teacher` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.timetable.show` | `/dos/timetable/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `dos.timetable.pdf` | `/dos/timetable/999999/pdf` | 302 | 404 | 404 | 404 | 404 | 404 |
| `dos.exams.index` | `/dos/exams` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.exams.create` | `/dos/exams/create` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.exams.show` | `/dos/exams/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `dos.marks` | `/dos/marks` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.missing` | `/dos/missing-marks` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.report-cards.index` | `/dos/report-cards` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.promote.index` | `/dos/promote` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.teachers.index` | `/dos/teachers` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `bursar.dashboard` | `/bursar` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.students` | `/bursar/students` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.record` | `/bursar/record` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.payments` | `/bursar/payments` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.receipt` | `/bursar/receipt/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `bursar.statement` | `/bursar/statement/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `bursar.balances` | `/bursar/balances` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.balances.export` | `/bursar/balances/export` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.daily` | `/bursar/daily` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `bursar.bulk` | `/bursar/bulk` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `teacher.dashboard` | `/teacher` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.classes` | `/teacher/classes` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.timetable` | `/teacher/timetable` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.marks` | `/teacher/marks` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.homework` | `/teacher/homework` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.classListPdf` | `/teacher/class-list/pdf` | 302 | 403 | 403 | 403 | 302 | 403 |
| `principal.news.index` | `/principal/news` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.news.create` | `/principal/news/create` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.news.edit` | `/principal/news/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.admissions.index` | `/principal/admissions` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.admissions.show` | `/principal/admissions/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.contacts.index` | `/principal/contacts` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.contacts.show` | `/principal/contacts/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.expenses.index` | `/principal/expenses` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.expenses.export` | `/principal/expenses/export` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `news` | `/news` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `news.show` | `/news/999999` | 404 | 404 | 404 | 404 | 404 | 404 |
| `timetable` | `/timetable` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `password.show` | `/password` | 302 | 2xx | 2xx | 2xx | 2xx | 2xx |
| `verify.receipt` | `/verify-receipt/999999` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `principal.live-collection` | `/principal/live-collection` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.live-collection.feed` | `/principal/live-collection/feed` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `confession.form` | `/report` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `principal.confessions.index` | `/principal/confessions` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.confessions.show` | `/principal/confessions/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.fines.index` | `/principal/fines` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.predictions` | `/principal/predictions` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.board-report` | `/principal/board-report` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `pwa.manifest` | `/manifest.webmanifest` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `(unnamed)` | `/service-worker.js` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `(unnamed)` | `/icon-999999.png` | 404 | 404 | 404 | 404 | 404 | 404 |
| `(unnamed)` | `/webhook/whatsapp` | 403 | 403 | 403 | 403 | 403 | 403 |
| `whatsapp.demo` | `/whatsapp-demo` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `principal.voice-sms` | `/principal/voice-sms` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `dos.teacher-subjects.index` | `/dos/teacher-subjects` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.timetable-generator.index` | `/dos/timetable-generator` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.exams.controls` | `/dos/exams/999999/controls` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.audit.index` | `/principal/audit` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `dos.students.index` | `/dos/students` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.students.create` | `/dos/students/create` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.students.show` | `/dos/students/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `dos.students.edit` | `/dos/students/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.emergency.index` | `/principal/emergency` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.emergency.create` | `/principal/emergency/create` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.emergency.show` | `/principal/emergency/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `library.index` | `/library` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `library.books` | `/library/books` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `library.books.create` | `/library/books/create` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `library.books.edit` | `/library/books/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `library.loans` | `/library/loans` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `library.issue` | `/library/issue` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `library.return` | `/library/return` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `visitors.gate` | `/visitors/gate` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `visitors.index` | `/visitors` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `visitors.check-in` | `/visitors/check-in` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `visitors.pre-register` | `/visitors/pre-register` | 302 | 2xx | 2xx | 2xx | 2xx | 403 |
| `visitors.show` | `/visitors/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.results` | `/principal/results` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.results.export` | `/principal/results/export/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.teachers.index` | `/principal/teachers` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.teachers.create` | `/principal/teachers/create` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.teachers.show` | `/principal/teachers/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `principal.teachers.edit` | `/principal/teachers/999999/edit` | 302 | 404 | 404 | 404 | 404 | 404 |
| `qr.scanner` | `/qr-scanner` | 302 | 2xx | 2xx | 2xx | 403 | 403 |
| `parent.pickups` | `/parent/pickups` | 302 | 302 | 302 | 302 | 302 | 302 |
| `parent.pickups.qr` | `/parent/pickups/999999/qr` | 404 | 404 | 404 | 404 | 404 | 404 |
| `security.dashboard` | `/security/dashboard` | 302 | 302 | 302 | 302 | 302 | 302 |
| `security.scan` | `/security/scan` | 302 | 302 | 302 | 302 | 302 | 302 |
| `security.logs` | `/security/logs` | 302 | 302 | 302 | 302 | 302 | 302 |
| `parent.library` | `/parent/library` | 302 | 302 | 302 | 302 | 302 | 302 |
| `library.public` | `/library-catalog` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `student.qr.public` | `/student-qr/999999` | 2xx | 2xx | 2xx | 2xx | 2xx | 2xx |
| `canteen.index` | `/canteen` | 302 | 2xx | 2xx | 2xx | 2xx | 2xx |
| `canteen.logs` | `/canteen/logs` | 302 | 2xx | 2xx | 2xx | 2xx | 2xx |
| `parent.wallet` | `/parent/wallet` | 302 | 302 | 302 | 302 | 302 | 302 |
| `parent.wallet.qr` | `/parent/wallet/999999/qr` | 404 | 404 | 404 | 404 | 404 | 404 |
| `principal.wallet.index` | `/principal/wallet` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `principal.wallet.search` | `/principal/wallet/search` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `student-qr-cards.card` | `/student-qr-cards/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `teacher.attendance.mark` | `/teacher/attendance` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `principal.attendance.index` | `/principal/attendance` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.attendance.chronic` | `/principal/attendance/chronic` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.attendance.export` | `/principal/attendance/export` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.fee-autopilot.index` | `/principal/fee-autopilot` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.fee-autopilot.settings` | `/principal/fee-autopilot/settings` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.fee-autopilot.student` | `/principal/fee-autopilot/student/999999` | 302 | 404 | 404 | 404 | 404 | 404 |
| `teacher.lesson-plans.index` | `/teacher/lesson-plans` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `teacher.performance` | `/teacher/performance` | 302 | 403 | 403 | 403 | 2xx | 403 |
| `dos.lesson-plans.index` | `/dos/lesson-plans` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `dos.teacher-performance.index` | `/dos/teacher-performance` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.staff-attendance` | `/principal/staff-attendance` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.staff-qrcodes` | `/principal/staff-qrcodes` | 302 | 2xx | 2xx | 403 | 403 | 403 |
| `principal.canteen-report` | `/principal/canteen-report` | 302 | 2xx | 403 | 403 | 403 | 403 |
| `principal.canteen-items.index` | `/principal/canteen-items` | 302 | 2xx | 403 | 2xx | 403 | 403 |
| `canteen.menu` | `/canteen/menu` | 302 | 302 | 302 | 302 | 302 | 302 |
| `gate.console.index` | `/gate/console` | 302 | 302 | 302 | 302 | 302 | 302 |
| `gate.console.stats` | `/gate/console/stats` | 302 | 401 | 401 | 401 | 401 | 401 |
| `gate.console.recent` | `/gate/console/recent` | 302 | 401 | 401 | 401 | 401 | 401 |
| `gate.console.inside` | `/gate/console/inside` | 302 | 401 | 401 | 401 | 401 | 401 |
| `storage.local` | `/storage/999999` | 403 | 403 | 403 | 403 | 403 | 403 |

## Red flags (0)

_none_

## Errors (0)

_none_