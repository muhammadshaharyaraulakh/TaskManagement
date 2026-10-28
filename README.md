# Task Manager

A simple task manager made with Laravel. You can add tasks, edit them, delete them, and drag and drop them to change the priority.

Built with:

- PHP 8.4
- Laravel 13
- MySQL 8
- Laravel Breeze (Blade) for login and register
- Tailwind CSS and plain JavaScript
- SortableJS for drag and drop

## What it can do

- Register and login. Every user only sees their own tasks.
- Create a task (name, priority and timestamps are saved in MySQL).
- Edit a task and delete a task.
- Drag and drop to reorder. #1 is on top, #2 is next, and so on. The priority number updates by itself.
- Projects (extra feature). You can make projects and filter the tasks by project.
- Everything works with AJAX, so the page does not reload.
- Design is neo-brutalism in black and white only. It works on mobile too.

## Project files

Only the main files are listed here.

```
app/Models/Task.php                        task model
app/Models/Project.php                     project model
app/Http/Controllers/TaskController.php    create, edit, delete, reorder tasks
app/Http/Controllers/ProjectController.php create and delete projects
app/Http/Controllers/DashboardController.php  loads the dashboard
app/Http/Requests/                         form request validation classes
database/migrations/                       projects and tasks tables
database/seeders/DatabaseSeeder.php        demo user, projects and tasks
resources/views/dashboard.blade.php        dashboard page (HTML only)
resources/views/welcome.blade.php          home page with login and sign up buttons
resources/js/tasks.js                      all the dashboard JavaScript
routes/web.php                             all routes
```

## How priority works

- Priority is a number in the `tasks.priority` column.
- A new task goes to the bottom of its project. Its priority is the highest number in that project plus 1.
- When you drop a task, the browser sends all task ids (top to bottom) to `POST /tasks/reorder`. The server saves them inside one database transaction. First id gets priority 1, second gets 2, and so on.
- Priority is counted inside each project, so every project has its own #1, #2, #3.
- When you delete a task or move it to another project, the remaining tasks are renumbered so there are no gaps.
- In the "All projects" view you can only drag a task between tasks of the same project.

## Routes (all need login, all return JSON)

| Method | URL | What it does |
| --- | --- | --- |
| GET | `/tasks?project_id=1` | list tasks (without `project_id` it lists all) |
| POST | `/tasks` | create a task |
| PUT | `/tasks/{task}` | edit a task |
| DELETE | `/tasks/{task}` | delete a task |
| POST | `/tasks/reorder` | save new order |
| GET | `/projects` | list projects |
| POST | `/projects` | create a project |
| DELETE | `/projects/{project}` | delete a project and its tasks |

If validation fails the server sends a `422` error and the first message is shown on the page.

## Requirements

- PHP 8.4 (extensions: mbstring, xml, curl, zip, pdo_mysql, bcmath, intl)
- Composer 2
- Node.js 20 or newer and npm
- MySQL 8

## Run it on localhost

### 1. Get the code

```bash
git clone <your-repo-url> task-manager
cd task-manager
composer install
npm install
```

The fonts and drag and drop library are npm packages. If they are not in `package.json` yet, install them:

```bash
npm install sortablejs @fontsource/space-grotesk @fontsource/space-mono
```

Make sure `resources/js/tasks.js` is added to the `input` list in `vite.config.js`:

```js
input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/tasks.js'],
```

Then build the assets:

```bash
npm run build
```

### 2. Make the database

Open MySQL and run:

```sql
CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Set up the .env file

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database details:

```env
APP_NAME="Task Manager"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations

```bash
php artisan migrate --seed
```

This makes the tables and adds some demo data. Demo login:

- Email: `demo@example.com`
- Password: `password`

If you do not want demo data, run `php artisan migrate` only and register a new account.

### 5. Start the server

```bash
php artisan serve
```

Open http://localhost:8000. While you are working on the design you can also run `npm run dev` in a second terminal.

## Where to host it

Laravel needs PHP and MySQL, so normal static hosts (like GitHub Pages or Netlify) will not work. These options work:

### Option 1: Shared hosting (cPanel), like Hostinger

Cheapest and easiest for a small project.

1. Run `npm run build` and `composer install --no-dev --optimize-autoloader` on your computer.
2. Upload the project to the server (File Manager or FTP). Do not forget the `vendor` folder and `public/build`.
3. Set the domain's document root to the `public` folder. If the host does not allow it, put the project one level above `public_html` and move the contents of `public` into `public_html`, then fix the paths in `index.php`.
4. Create a MySQL database and user in cPanel.
5. Upload `.env` with `APP_ENV=production`, `APP_DEBUG=false`, your domain in `APP_URL` and the database details.
6. Run `php artisan key:generate` and `php artisan migrate --force`. If you have no SSH, use the host's terminal in cPanel, or import the tables by SQL.
7. Check that the host runs PHP 8.4 (change it in the PHP selector in cPanel).

### Option 2: Cloud platforms (Railway, Render, Laravel Cloud)

Good if your code is on GitHub. You connect the repo and they build it.

1. Push the project to GitHub.
2. Create a new project and choose your repo.
3. Add a MySQL database from the platform.
4. Add the environment variables from `.env` (`APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and `DB_*`). You can get an app key with `php artisan key:generate --show`.
5. Build command: `composer install --no-dev --optimize-autoloader && npm install && npm run build`
6. Run `php artisan migrate --force` after deploy.

Free plans and prices change often, so check them on the website first.

### Option 3: Your own VPS (DigitalOcean, Hetzner, etc.)

More work, but you control everything.

1. Install Nginx, PHP 8.4 with the extensions above, MySQL, Composer and Node.
2. Clone the repo and run `composer install --no-dev --optimize-autoloader`, `npm install` and `npm run build`.
3. Copy `.env.example` to `.env` and fill it for production. Run `php artisan key:generate`.
4. Run `php artisan migrate --force`.
5. Point Nginx to the `public` folder and give `storage` and `bootstrap/cache` write permission to the web user.
6. Add a free SSL certificate with Let's Encrypt.

### After every deploy

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Tests

Breeze auth tests are included.

```bash
php artisan test
```

The tests use SQLite in memory, so they do not touch your MySQL data.

