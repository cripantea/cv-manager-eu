# CV Manager EU

A web application for collecting, editing, and exporting professional CVs in the **DIGIT-TM II** format — the standardised template required for EU framework contracts (e.g. DIGIT, ENISA, EIB).

Built with **Laravel 11**, **Tailwind CSS**, and the **Anthropic Claude API**.

---

## ✨ Features

### For Candidates
- Structured CV editor with all DIGIT-TM II fields
- Project management — add, edit, delete work experience entries
- Technology tagging with competence ratings (1–5) per project
- Language proficiency table
- **AI Import** — extract project history automatically from:
  - Pasted text (existing CV, LinkedIn export, etc.)
  - PDF upload (max 5 MB)
- Auto-save

### For Administrators
- Admin dashboard with CV status overview (draft / locked / archived)
- Invite candidates via email
- Lock / unlock candidate editing
- Archive CVs
- Export CV as **.docx** (DIGIT-TM II template) — one click download
- Suspend / unsuspend user accounts
- Reset AI import quota per candidate

### Output
The exported Word document includes:
- Personal and professional overview with automatic checkbox fields
- Profile summary page
- Trainings and certifications table
- Technology expertise table — months of experience calculated automatically across all projects (overlapping dates merged)
- One page per project with full structured detail

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Authentication | Laravel Breeze + Sanctum |
| AI extraction | Anthropic Claude API (`claude-haiku-4-5-20251001`) |
| PDF extraction | `pdftotext` (Poppler CLI) |
| Word generation | PHPWord |
| Deployment | Envoy |

---

## 🚀 Installation

### Requirements
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL or PostgreSQL
- `pdftotext` installed on the server (`apt install poppler-utils`)
- Anthropic API key

### Steps

```bash
# Clone the repository
git clone https://github.com/your-username/cv-manager-eu.git
cd cv-manager-eu

# Install PHP dependencies
composer install

# Install JS dependencies
npm install && npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env (see below)

# Run migrations
php artisan migrate

# (Optional) Seed with a default admin user
php artisan db:seed
```

---

## ⚙️ Environment Configuration

Key variables to set in your `.env`:

```env
APP_NAME="CV Manager EU"
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cv_manager
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your.smtp.host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="CV Manager EU"

ANTHROPIC_API_KEY=sk-ant-...
```

---

## 👥 User Roles

| Role | Access |
|---|---|
| `candidate` | CV editor only — self-registered or invited |
| `admin` | Full dashboard, user management, DOCX export |

Admin accounts are created manually (set `role = 'admin'` in the `users` table, or via seeder).

Candidates can self-register at `/register` or be invited by an admin from `/admin/users`.

---

## 🤖 AI Import

The AI import feature uses **Anthropic Claude** (`claude-haiku-4-5-20251001`) to extract structured project data from unstructured text or PDF files.

- Each candidate has a quota of **3 AI imports**
- Admins can reset the quota from the users management page
- The extraction prompt is written in Italian (matching the target user base)
- Supported input: plain text paste or PDF upload (max 5 MB, requires `pdftotext`)

---

## 📄 DIGIT-TM II Export

The Word export is generated using **PHPWord** and follows the official DIGIT-TM II CV template structure:

- Page 1 — Personal overview, education, languages, top 5 technologies
- Page 2 — Profile summary
- Page 3 — Trainings and certifications
- Page 4 — Full technology expertise table (competence + months, auto-calculated)
- Project pages — one per project, structured layout

Technology experience months are computed automatically. Overlapping date ranges across projects are merged to avoid double-counting.

---

## 🚢 Deployment with Envoy

The project includes an `Envoy.blade.php` for zero-downtime deployment:

```bash
envoy run deploy
```

Deployments use a `releases/` + `current/` symlink structure. Configure your server's document root to point to `current/public`.

---

## 🔒 Security

- Login rate-limited to 5 attempts per minute per IP
- Email verification required for self-registered users (invited candidates are pre-verified)
- Forced password change on first login for invited candidates
- CV lock prevents editing even by authenticated candidates
- Suspended users are logged out immediately on their next request

---

## 📁 Key Routes

| Method | Route | Description |
|---|---|---|
| GET | `/cv/edit` | CV editor (candidate) |
| POST | `/cv/ai-import/text` | AI import from text |
| POST | `/cv/ai-import/pdf` | AI import from PDF |
| GET | `/admin/dashboard` | CV list with status |
| GET | `/admin/cvs/{id}/export` | Download DOCX |
| POST | `/admin/users/invite` | Invite a candidate |
| PATCH | `/admin/cvs/{id}/lock` | Lock a CV |
| PATCH | `/admin/users/{id}/reset-ai-import` | Reset AI quota |

---

## 📝 License

This project is open source and free to use. If you use it or adapt it, a mention is appreciated.

---

*Developed by [Cristian Pantea](https://fusionsoft.it)*
