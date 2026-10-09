#!/data/data/com.termux/files/usr/bin/bash
# CP-33: restore drill. Verifies a backup can actually be restored.
# Live database is opened read-only. All writes go to marell_restore_drill.
# Exit 0 on PASS, 1 on FAIL.

set -u

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT" || exit 1

TS=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$HOME/db_backups"
BACKUP_FILE="$BACKUP_DIR/marell_drill_${TS}.sql"
SCRATCH="marell_restore_drill"

mkdir -p "$BACKUP_DIR"

# Read password from .env without printing it.
PW=$(grep '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"')
if [ -z "$PW" ]; then
    echo "FAIL: DB_PASSWORD not found in .env"
    exit 1
fi

echo "==> 1. Backup live database to $BACKUP_FILE"
mysqldump -umarell -p"$PW" marell > "$BACKUP_FILE" 2>/dev/null
if [ ! -s "$BACKUP_FILE" ]; then
    echo "FAIL: backup file is empty"
    exit 1
fi
echo "    ok ($(wc -l < "$BACKUP_FILE") lines)"

echo "==> 2. Recreate scratch database $SCRATCH"
mysql -uroot -e "DROP DATABASE IF EXISTS $SCRATCH; CREATE DATABASE $SCRATCH CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON $SCRATCH.* TO 'marell'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null
echo "    ok"

echo "==> 3. Restore backup into scratch"
mysql -uroot "$SCRATCH" < "$BACKUP_FILE" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "FAIL: restore failed"
    mysql -uroot -e "DROP DATABASE IF EXISTS $SCRATCH;" 2>/dev/null
    exit 1
fi
echo "    ok"

echo "==> 4. Compare row counts (live vs restored)"
TABLES="students users payments wallet_transactions visitors staff_attendances canteen_items"
PASS=1
printf "    %-22s %10s %10s\n" "table" "live" "restored"
for t in $TABLES; do
    LIVE=$(mysql -uroot -N -B -e "SELECT COUNT(*) FROM \`$t\`;" marell 2>/dev/null)
    REST=$(mysql -uroot -N -B -e "SELECT COUNT(*) FROM \`$t\`;" "$SCRATCH" 2>/dev/null)
    MARK="ok"
    if [ "$LIVE" != "$REST" ]; then
        MARK="MISMATCH"
        PASS=0
    fi
    printf "    %-22s %10s %10s  %s\n" "$t" "$LIVE" "$REST" "$MARK"
done

echo "==> 5. Drop scratch database"
mysql -uroot -e "DROP DATABASE IF EXISTS $SCRATCH;" 2>/dev/null
echo "    ok"

echo ""
if [ "$PASS" -eq 1 ]; then
    echo "RESTORE DRILL: PASS"
    echo "backup: $BACKUP_FILE"
    exit 0
else
    echo "RESTORE DRILL: FAIL (row count mismatch)"
    echo "backup kept for investigation: $BACKUP_FILE"
    exit 1
fi