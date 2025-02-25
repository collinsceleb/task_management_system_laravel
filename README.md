# task_management_system_laravel


git clone https://github.com/your-repo/task-management-laravel.git

cd task-management-laravel

Install dependencies: compose install

Set up environment variables. Copy .env.example to .env:

cp .env.example .env

Update the database credentials in .env:

i. DB_CONNECTION=mysql
ii. DB_HOST=127.0.0.1
iii. DB_PORT=3306
iv. DB_DATABASE=your_database
v. DB_USERNAME=your_username
vi. DB_PASSWORD=your_password

Run migrations and seed database:  
php artisan migrate --seed

Start the server

php artisan serve

NOTE: Laravel API will be running at:   http://127.0.0.1:8000
