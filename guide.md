# 🧠 Laravel Quiz App

This is a simple Laravel-based quiz application that allows users to answer multiple-choice questions and view their results.

---

## 🚀 How to Run the Laravel Quiz App

Follow these steps to set up and run the project locally.

---

### 🔁 1. Clone the Repository

```bash
git clone https://github.com/musamakhizr/QuizApp.git
cd QuizApp
```

---

### 📦 2. Install PHP Dependencies

```bash
composer install
```

> Ensure you have **PHP ≥ 8.1**, **Composer**, and **MySQL** installed.

---

### ⚙️ 3. Copy `.env` and Set App Key

```bash
cp .env.example .env
php artisan key:generate
```

> Make sure to configure your `.env` database credentials:
```
DB_DATABASE=quiz_app
DB_USERNAME=root
DB_PASSWORD=
```

---

### 🗃️ 4. Migrate and Seed the Database

This command will:
- Drop and recreate all tables
- Seed users, quiz questions, and answer options

```bash
php artisan migrate:fresh --seed
```

---

### 🌐 5. Run the Development Server

```bash
php artisan serve
```

> The app will be accessible at: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

### 🎨 6. Compile Frontend Assets

If your project uses Tailwind, Vue, or any assets via Vite:

```bash
npm install
npm run dev
```

Or using Composer script (if defined):

```bash
composer run dev
```

---

### ✅ Done!

You can now start the quiz by visiting the homepage.

---
