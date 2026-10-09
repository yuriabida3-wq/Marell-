# Marell deployment helpers

Two hosts, two supervision methods. Pick one per host.

## Ubuntu VPS - Supervisor

`deploy/jenga-worker.conf` and `deploy/jenga-scheduler.conf` are Supervisor programs. Install once:

    sudo apt-get install -y supervisor
    sudo cp deploy/jenga-worker.conf    /etc/supervisor/conf.d/
    sudo cp deploy/jenga-scheduler.conf /etc/supervisor/conf.d/
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl status

Adjust /var/www/marell, user=www-data, and numprocs if your install differs.

- Queue priority mpesa,default - Safaricom callbacks jump ahead of routine SMS.
- numprocs=2 for the worker - safe because every money mutation takes a lockForUpdate(). Two processes just double throughput.
- autorestart=true - Supervisor restarts on crash.
- Scheduler runs schedule:work, which dispatches the four tasks defined in bootstrap/app.php every minute.

Firewall rules: deploy/ufw-rules.sh (deny incoming by default, allow 22/80/443).

## Termux (development) - plain shell loop

    bash deploy/termux-worker.sh                                  # foreground
    nohup bash deploy/termux-worker.sh >> storage/logs/worker.log 2>&1 &   # detached

Writes storage/app/worker.pid and storage/app/scheduler.pid.
Logs to storage/logs/worker.log and storage/logs/scheduler.log.

After a phone reboot, re-run the start command. Termux has no init system, so nothing starts automatically.

## Why not systemd on the VPS

Supervisor is already the chosen path here. Mixing systemd units with Supervisor for the same jobs creates two sources of truth. Stick with Supervisor.