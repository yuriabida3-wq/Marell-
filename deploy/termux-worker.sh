#!/data/data/com.termux/files/usr/bin/bash
cd ~/projects/marell
while true; do
    php artisan queue:work --sleep=3 --tries=3 --timeout=90 --queue=mpesa,default >> storage/logs/worker.log 2>&1
    echo "[$(date)] Worker crashed, restarting in 5s..." >> storage/logs/worker.log
    sleep 5
done
