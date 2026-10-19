# Soda Beach Resort – Online Day-Pass E-Ticketing & Cottage Booking

## Setup (XAMPP / Laragon)
1. Copy this folder to `htdocs/` (keep the folder name, or any name – the base URL is auto-detected).
2. Start Apache + MySQL. In phpMyAdmin, import `database.sql` (creates DB `soda_beach_resort`).
3. Edit `config/db.php`: DB credentials and your PayPal **Sandbox** Client ID / Secret
   (developer.paypal.com > Apps & Credentials > Sandbox). Make sure `extension=curl` is enabled in php.ini.
4. Put your resort photo at `assets/img/bg.jpg` (the lobby picture used in your design).
5. Open `http://localhost/SODA_BEACH_RESORT/login.php`.

## Accounts
- Staff: user `Admin`, password `123`  → staff dashboard
- Visitors: register, then log in with their **email** + password.
- Pay with a PayPal Sandbox *personal* (buyer) test account.

## Sample data
| Cottage | Capacity | Price | Units |
|---|---|---|---|
| Small | 4–6 pax | ₱300 | 10 |
| Medium | 8–10 pax | ₱500 | 6 |
| Large | 12–14 pax | ₱1,200 | 4 |

Day pass: ₱150/person, daily capacity 200 (both editable in Staff > Manage).

## Styles
Edit `assets/scss/*.scss` and compile to `assets/css/style.css` (e.g. `sass assets/scss/style.scss assets/css/style.css`).
