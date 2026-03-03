# MediCare E-Commerce Platform

A high-performance, custom-built e-commerce platform explicitly tailored for a medicine brand. Developed using Object-Oriented PHP 8, PDO, and Tailwind CSS.

## Features

- **Mobile-First UI:** Responsive catalog with an intelligent AJAX product search.
- **Secure Authentication:** User registration, login, and robust session management utilizing `password_hash()` and safe HTTP-only tokens.
- **Advanced Catalog:** Dynamic product filtering by category, intelligent stock checking, and Rx (Prescription) flagging.
- **Dynamic Discount Engine:** Apply percentage-based or fixed-amount coupons with real-time cart evaluation for minimum amounts and usage limitations.
- **Razorpay Integration:** Seamless zero-redirect checkout logic mapping directly to Razorpay's API with automated webhook verification.
- **Automated Invoicing:** On successful webhook triggers, the system asynchronously generates a professional A4 Tax Invoice using `DomPDF` and emails it automatically via `PHPMailer`.
- **Dashboards:** Separate interfaces tracking granular order statuses and metrics for Customers and Administrators.

## Project Structure

This project follows an MVC-like architecture without utilizing a bloated framework:

*   **`public/`**: The entry point. Contains `index.php` initializing the custom router, along with static assets (CSS, JS, Images).
*   **`src/`**: The core application logic.
    *   `Controllers/`: Handles HTTP requests (Catalog, Cart, Auth, Checkout, Webhook, Dashboard).
    *   `Models/`: Represents entities and database queries (User, Product, Order).
    *   `Core/`: Core architectural elements (Router, Session, Database, Cart logic).
    *   `Views/`: Tailwind-styled HTML templates organized by domain.
    *   `Helpers/`: Service classes for generating PDFs and sending Emails.
*   **`database/`**: Contains `schema.sql` for migrating the database.

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd ecommerce-platform
   ```

2. **Install Dependencies:**
   Ensure you have Composer installed.
   ```bash
   composer install
   ```

3. **Database Configuration:**
   - Create a MySQL database (e.g., `ecommerce`).
   - Import the schema:
     ```bash
     mysql -u root -p ecommerce < database/schema.sql
     ```

4. **Environment Variables:**
   - Create a `.env` file in the root directory.
   - Map out the required configurations:
     ```env
     # Database
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=ecommerce
     DB_USERNAME=root
     DB_PASSWORD=

     # Razorpay
     RAZORPAY_KEY_ID=rzp_test_your_dummy_key
     RAZORPAY_KEY_SECRET=your_dummy_secret
     RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

     # SMTP Mailer (e.g., Mailtrap)
     SMTP_HOST=smtp.mailtrap.io
     SMTP_PORT=2525
     SMTP_USER=username
     SMTP_PASS=password
     MAIL_FROM_ADDRESS=no-reply@medicare.com
     MAIL_FROM_NAME="MediCare Admin"
     ```

5. **Run the Application:**
   For local development, use PHP's built-in web server:
   ```bash
   php -S localhost:8000 -t public/
   ```

   Visit `http://localhost:8000` to interact with the platform.

## Dependencies

- **[PHPMailer](https://github.com/PHPMailer/PHPMailer):** Email sending operations.
- **[Dompdf](https://github.com/dompdf/dompdf):** HTML to PDF generator for tax invoices.
- **[Razorpay SDK](https://github.com/razorpay/razorpay-php):** Razorpay API integration.
- **[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv):** Configuration loading from `.env`.
