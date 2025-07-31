# Publish Email Notification
php artisan vendor:publish --tag=laravel-mail
php artisan queue:work


# Supervisor needed
sudo dnf install epel-release -y
sudo dnf install supervisor -y

sudo systemctl enable supervisord
sudo systemctl start supervisord

# Create Conf
sudo nano /etc/supervisord.d/laravel-worker.ini

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-app/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=apache            ; or nginx/www-data depending on your setup
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/your-app/storage/logs/laravel-worker.log

# error: <class 'FileNotFoundError'>, [Errno 2] No such file or directory: ...
sudo systemctl start supervisord
sudo systemctl status supervisord
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

Enable at Boot
sudo systemctl enable supervisord

# Has changes? restart

1. Reload Supervisor to recognize new changes (if you modified laravel-worker.ini)

sudo supervisorctl reread
sudo supervisorctl update

# 2. Restart your workers (required after .env changes)

sudo supervisorctl restart laravel-worker:*

# 3. Verify status

sudo supervisorctl status
