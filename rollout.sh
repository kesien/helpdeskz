#!/bin/bash
cp -fi ./assets/helpdeskz/js/staff.js /home/helpdeskz/helpdeskz/assets/js/

cp -fi ./hdz/app/Config/Services.php /home/helpdeskz/helpdeskz/hdz/app/Config/

cp -fi ./hdz/app/Models/EmailRule.php /home/helpdeskz/helpdeskz/hdz/app/Models/

cp -fi ./hdz/app/Views/staff/departments_form.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/
cp -fi ./hdz/app/Views/staff/template.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/
cp -fi ./hdz/app/Views/staff/departments.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/
cp -fi ./hdz/app/Views/staff/ticket_view.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/
cp -fi ./hdz/app/Views/staff/ticket_new.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/
cp -fi ./hdz/app/Views/staff/tickets.php /home/helpdeskz/helpdeskz/hdz/app/Views/staff/

cp -fi ./hdz/app/Views/client/ticket_agents.php /home/helpdeskz/helpdeskz/hdz/app/Views/client/
cp -fi ./hdz/app/Views/client/ticket_form.php /home/helpdeskz/helpdeskz/hdz/app/Views/client/

cp -fi ./hdz/app/Language/en/Admin.php /home/helpdeskz/helpdeskz/hdz/app/Language/en/
cp -fi ./hdz/app/Language/en/Client.php /home/helpdeskz/helpdeskz/hdz/app/Language/en/

cp -fi ./hdz/app/Libraries/EmailRules.php /home/helpdeskz/helpdeskz/hdz/app/Libraries/
cp -fi ./hdz/app/Libraries/Departments.php /home/helpdeskz/helpdeskz/hdz/app/Libraries/
cp -fi ./hdz/app/Libraries/Staff.php /home/helpdeskz/helpdeskz/hdz/app/Libraries/
cp -fi ./hdz/app/Libraries/Tickets.php /home/helpdeskz/helpdeskz/hdz/app/Libraries/
cp -fi ./hdz/app/Libraries/MailFetcher.php /home/helpdeskz/helpdeskz/hdz/app/Libraries/

cp -fi ./hdz/app/Controllers/Staff/Departments.php /home/helpdeskz/helpdeskz/hdz/app/Controllers/Staff/
cp -fi ./hdz/app/Controllers/Staff/Tickets.php /home/helpdeskz/helpdeskz/hdz/app/Controllers/Staff/
cp -fi ./hdz/app/Controllers/Staff/Settings.php /home/helpdeskz/helpdeskz/hdz/app/Controllers/Staff/
cp -fi ./hdz/app/Controllers/Ticket.php /home/helpdeskz/helpdeskz/hdz/app/Controllers/Staff/

cp -fi ./hdz/app/Helpers/FilterHelper.php /home/helpdeskz/helpdeskz/hdz/app/Helpers/

cp -fi ./hdz/install/db.sql /home/helpdeskz/helpdeskz/hdz/install/