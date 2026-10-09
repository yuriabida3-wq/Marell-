#!/data/data/com.termux/files/usr/bin/bash
# Termux worker + scheduler supervisor.
# Keeps two loops alive: queue:work (mpesa priority) and schedule:run.
# Foreground:  bash deploy/termux-worker.sh
# Detached:    nohup bash deploy/termux-worker.sh >> storage/logs/worker.log 2>&1 &
# Stop:        kill $(cat storage/app/worker.pid) $(cat storage/app/scheduler.pid)

set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT" || { echo "cannot cd to $ROOT"; exit 1; }

PID_DIR="$ROOT/storage/app"
LOG_DIR="$ROOT/storage/logs"
mkdir -p "$PID_DIR" "$LOG_DIR"

QUEUE_LOG="$LOG_DIR/worker.log"
SCHED_LOG="$LOG_DIR/scheduler.log"

(
    while true; do
        php artisan queue:work --queue=mpesa,default --tries=3 --timeout=90 --sleep=3 --max-time=3600 >> "$QUEUE_LOG" 2>&1
        echo "[$(date)] worker loop exited, restarting in 5s" >> "$QUEUE_LOG"
        sleep 5
    done
) &
QUEUE_PID=$!
echo "$QUEUE_PID" > "$PID_DIR/worker.pid"

(
    while true; do
        php artisan schedule:run >> "$SCHED_LOG" 2>&1
        sleep 60
    done
) &
SCHED_PID=$!
echo "$SCHED_PID" > "$PID_DIR/scheduler.pid"

echo "queue worker pid=$QUEUE_PID (log: $QUEUE_LOG)"
echo "scheduler  pid=$SCHED_PID (log: $SCHED_LOG)"
echo "to stop:   kill $QUEUE_PID $SCHED_PID"

wait
