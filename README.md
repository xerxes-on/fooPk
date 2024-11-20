<p align="center"><img src="https://foodpunk.de/wp-content/themes/foodpunk/assets/images/foodpunk_newlogo_black.png" width="400"></p>

## Local development

In order to run Foodpunk locally [Git](https://git-scm.com/), [Docker Compose](https://docs.docker.com/compose/install/),
[Composer](https://getcomposer.org/) and [NodeJS](https://nodejs.org)(>=18.2.0) should be installed.

```sh
# obtain the source code
git clone git@bitbucket.org:foodpunk/foodpunk_laravel_v2.git
cd foodpunk_laravel_v2

# install dependencies
npm install
npm run development
composer install

# copy .env.example to .env and adjust its content if needed
cp .env.example .env

# run the project
./vendor/bin/sail up -d

# make sure DB structure is up to date with the code
# wait for few minutes (so that the DB container is finished loading) and execute the command
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

Local instance of Foodpunk should become available at `http://localhost/` or upon your local .env configuration

## Testing

Tests can be executed with

```sh
sail artisan config:clear # make sure test DB (not the main one) is used
sail artisan test --testdox
```

## Front-end assembler

To assemble front-end assets run the following command depending on the preferences:

```sh
# This will allow to assemble unminified assets, to preview purposes
npm run development
```

```sh
# This will allow to assemble minified assets,for production purposes
npm run production
```

```sh
# This will allow to assemble unminified assets and watch changes
# Assets will reassemble upon changes
npm run watch-poll
```

## Jobs

crontab -e -uwww-data

```sh
cd /srv/foodpunk_laravel_v2 && php artisan schedule:run >> /dev/null 2>&1
```

`supervisor` needs to be installed. For local development it is installed in dockerfile.

- Create `supervisor` file at `/etc/supervisor/conf.d/laravel-emails-worker.conf` and add the following:

```conf
[program:laravel-emails-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /srv/foodpunk_laravel_v2/artisan queue:work database --queue=emails --sleep=3 --tries=3 --timeout=0
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/srv/foodpunk_laravel_v2/storage/logs/artisan/worker-emails.log
```

- Create `supervisor` file at `/etc/supervisor/conf.d/laravel-worker-high.conf` and add the following:

```conf
[program:laravel-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php /srv/foodpunk_laravel_v2/artisan queue:work database --queue=high --sleep=3 --tries=3 --timeout=0
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/srv/foodpunk_laravel_v2/storage/logs/artisan/worker-high.log

```

- Create `supervisor` file at `/etc/supervisor/conf.d/laravel-worker-low.conf` and add the following:

```conf
[program:laravel-worker-low]
process_name=%(program_name)s_%(process_num)02d
command=php /srv/foodpunk_laravel_v2/artisan queue:work database --queue=low --sleep=3 --tries=3 --timeout=0
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/srv/foodpunk_laravel_v2/storage/logs/artisan/worker-low.log

```

- Create `supervisor` file at `/etc/supervisor/conf.d/laravel-worker-default.conf` and add the following:

```conf
process_name=%(program_name)s_%(process_num)02d
command=php /srv/foodpunk_laravel_v2/artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=0
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/srv/foodpunk_laravel_v2/storage/logs/artisan/worker-default.log
```
