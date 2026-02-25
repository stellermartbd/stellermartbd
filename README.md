```markdown
# Simple E-Commerce (Plain PHP) — Full Starter

What this includes
- Public store: product list, product page, search, cart, checkout (simple order create)
- Auth: register / login (password_hash)
- Admin panel: login, dashboard (visits & revenue), product CRUD with image upload, orders list & view
- Visit tracking: sendBeacon records visits → admin analytics
- CSRF protection for forms
- Secure DB access with PDO + prepared statements
- Upload protection (.htaccess) to disallow PHP execution in uploads

Requirements
- PHP 8.0+
- MySQL (or compatible)
- Apache or Nginx (DocumentRoot -> project-root/public)
- Optional: Composer

Quick setup
1. Copy env:
   - Linux/macOS:
     ```
     cp .env.example .env
     ```
   - Windows:
     ```
     copy .env.example .env
     ```
   Edit `.env` and fill DB credentials and ADMIN_EMAIL/PASSWORD.

2. Create database and import schema:
   ```
   mysql -u root -p -e "CREATE DATABASE shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p shop_db < sql/schema.sql
   ```

3. Create storage & set permissions:
   ```
   mkdir -p storage/uploads logs public/uploads
   sudo chown -R www-data:www-data storage logs public/uploads
   sudo chmod -R 770 storage logs public/uploads
   ```

4. (Optional) If using Apache, set DocumentRoot to `project-root/public`.

5. Visit:
   - Public: http://localhost/
   - Admin: http://localhost/admin/login.php
     - Use ADMIN_EMAIL and ADMIN_PASSWORD from `.env` for initial admin sign-in.
     - You can later create real admin user in `users` table and set `is_admin=1`.

Notes & next steps
- .env must not be committed; it is included in .gitignore.
- For production: use HTTPS, set secure cookies, move storage outside webroot, integrate proper admin users (instead of env bootstrap), configure backups and monitoring.
- To add charts: include Chart.js in admin and fetch visits/orders endpoints.
- To integrate payments: add Stripe/others in `checkout.php` and use webhooks.

If you want, I can:
- Add Stripe checkout integration next.
- Convert to Laravel (recommended for larger projects).
- Add CSV product import, image processing, or Chart.js dashboards.
```