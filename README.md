# Barangay Information System

A responsive PHP + MySQL starter application inspired by the supplied Barangay Information System reference.

## Included
- Admin login
- Dashboard with resident, household, certificate and blotter totals
- Resident and household records
- Certificate and clearance request records
- Income and expense records with net balance
- Blotter / incident records
- Ordinances and resolutions
- Asset inventory
- Reports page and CSV exports
- Responsive layout for desktop, tablet and mobile

## Requirements
- XAMPP (Apache + MySQL)
- PHP 8.0+
- Modern browser
- Internet connection for Bootstrap, Bootstrap Icons, Chart.js and Google Fonts CDN assets

## Installation (Windows / XAMPP)
1. Extract `Barangay_Information_System.zip`.
2. Copy the `Barangay_Information_System` folder into `C:\xampp\htdocs\`.
3. Open XAMPP Control Panel and start **Apache** and **MySQL**.
4. Open `http://localhost/phpmyadmin`.
5. Select **Import**, choose `database.sql`, then click **Go**.
6. Open `config.php` and update DB_HOST, DB_NAME, DB_USER and DB_PASS if your MySQL credentials differ.
7. Visit `http://localhost/Barangay_Information_System/setup.php` once to create the first administrator account.
8. **Delete `setup.php` immediately** after it reports success.
9. Visit `http://localhost/Barangay_Information_System/`.
10. Sign in using:
   - Username: `admin`
   - Password: `admin123`

The one-time `setup.php` page creates the initial admin account if the `users` table is empty. Delete that file immediately after setup.

## Important: create the initial admin account
After importing the database, open the project URL once. If the users table is empty, use the one-time setup below by temporarily visiting `setup.php` (included). Delete `setup.php` immediately after setup.

## Security before real use
This is a starter project, not yet a production-ready government records platform. Before deployment:
- Change the default admin password and remove setup.php.
- Use HTTPS and secure hosting; never expose XAMPP directly to the public internet.
- Add role-based permissions, audit logs, backups, account recovery and stronger validation.
- Restrict access to sensitive resident, financial and blotter information.
- Obtain appropriate authorization and comply with the Data Privacy Act of 2012 and applicable government records policies.
- Review certificate formats, fees and workflows with the barangay before actual issuance.

## GitHub
Create a private GitHub repository and upload the project files. Do not commit real resident data, passwords, database backups or `.env` credentials. GitHub stores source code; it does not itself host PHP/MySQL execution. For public access, use a PHP/MySQL hosting provider with HTTPS and import the database there.
