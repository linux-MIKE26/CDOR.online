#!/bin/bash
# Wrapper to start the Python mock server reliably
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PORT=$1

if [ -z "$PORT" ]; then
    echo "Error: No port specified"
    exit 1
fi

LOGFILE="$DIR/server_debug.log"

echo "[$(date)] Request to start on port $PORT" >> "$LOGFILE"

# Ensure php-cli exists
if ! command -v php &> /dev/null; then
    echo "[$(date)] Error: php (cli) not found" >> "$LOGFILE"
    exit 1
fi

# Kill any existing process on this port (cleanup)
existing_pid=$(lsof -t -i:$PORT)
if [ ! -z "$existing_pid" ]; then
    echo "[$(date)] Killing existing process $existing_pid on port $PORT" >> "$LOGFILE"
    kill -9 $existing_pid
fi

# Start the server
nohup php "$DIR/server_node.php" "$PORT" >> "$LOGFILE" 2>&1 &
PID=$!

# Verify it's running
sleep 1
if ps -p $PID > /dev/null; then
    echo "[$(date)] Success. PID: $PID" >> "$LOGFILE"
    echo $PID
else
    echo "[$(date)] Failed to start. Process died immediately." >> "$LOGFILE"
    exit 1
fi
