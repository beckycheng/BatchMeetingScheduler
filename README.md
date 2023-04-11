# BatchMeetingScheduler

This document provides information on how to run the Laravel project, configure the environment variables, and other necessary details.

## Getting Started

1. Clone the project from the GitHub repository:
```bash
git clone https://github.com/QixinWangCpsLab/BatchMeetingScheduler_ChengPikkei.git
```

2. Install the project dependencies using Composer:
```bash
composer install
```

3. Install the necessary frontend dependencies using npm:
```bash
npm install
```

4. Create a copy of the `.env.example` file and name it `.env`:
```bash
cp .env.example .env
```

5. Generate a new application key:
```bash
php artisan key:generate
```

6. Configure the `.env` file with the necessary details such as the database connection, mail settings, and other environment variables.

7. Run the database migrations:
```bash
php artisan migrate
```

8. Compile the frontend assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```
You can access the website at http://localhost:8000


## Laravel Task Scheduling without Cron Jobs

Laravel provides a powerful task scheduling system that allows you to schedule tasks to run at specific intervals. However, for local development, setting up cron jobs can be cumbersome. Fortunately, there's an alternative solution using `node-schedule`.

1. Installing `node-schedule`
```
npm install node-schedule --save-dev
```

2.  In the Laravel project's root directory, create a file called `dev-scheduler.js`. Paste the following code into the file:
```js
const schedule = require('node-schedule')
const { exec } = require('child_process')

new schedule.scheduleJob('* * * * *', function() {
    exec('php artisan schedule:run', function(error, stdout, stderr) {
        if (error) console.log(error)
        if (stderr) console.log(stderr)
        console.log(stdout)
    })
})
```

3. Running the scheduler
To run the scheduler, execute the following command in your Laravel project's root directory:
```bash
node dev-scheduler.js
```

This will start the node-schedule and run the Laravel scheduler at the intervals specified in your code.

Note that this solution is not meant for production. For production environments, it's recommended to use cron jobs instead.


## Configuration

The `.env` file contains various settings required for the Laravel project. Here are some of the most common settings that need to be configured:

### Database Connection
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=database_username
DB_PASSWORD=database_password
```

Replace `database_name`, `database_username`, and `database_password` with the appropriate values for your database setup.

### Mail Settings
```ini
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
```
Replace `your_email@gmail.com` and `your_email_password` with your Gmail email address and password.


## Testing

To run the tests, run the following command:
```bash
php artisan test
```
This will run all the tests in the `tests/` directory.


## Deployment

Before deploying the application, make sure to configure the `.env` file with the appropriate settings for the production environment. It's also a good idea to run the tests to ensure that everything is working correctly.

To deploy the application, you can use a service like [Forge](https://forge.laravel.com/) or [Envoyer](https://envoyer.io/), or follow the [official Laravel deployment guide](https://laravel.com/docs/8.x/deployment).