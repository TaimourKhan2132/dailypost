DAILYPOST PHP + MYSQL UPLOAD

1. Create a MySQL database in your hosting panel.
2. Import database.sql in phpMyAdmin.
3. Edit config/db.php with the database name, username and password.
4. Create an admin password hash using:
   php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
   Insert the hash into the admins table as shown in database.sql.
5. Upload all files/folders into public_html or htdocs.
6. Open your website.
7. Admin: /admin/login.php

Workflow:
Write -> Submit -> Admin Review -> Publish -> Everyone can read.

Authors have no edit function after submission/publication.
This package intentionally does not include a hard-coded admin password.
