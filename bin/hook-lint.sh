#!/usr/bin/env bash
# PostToolUse hook: syntax-check the file Claude just wrote.
#
# Reads the hook payload on stdin, pulls out tool_input.file_path and runs the
# cheapest possible check for that file type. Exit code 2 feeds stderr back to
# Claude as a correction; anything else is silent.

set -uo pipefail

payload="$(cat)"

file_path="$(printf '%s' "$payload" | python3 -c '
import json, sys
try:
    data = json.load(sys.stdin)
except Exception:
    sys.exit(0)
print(data.get("tool_input", {}).get("file_path", "") or "")
')"

[ -n "$file_path" ] || exit 0
[ -f "$file_path" ] || exit 0

case "$file_path" in
	*.php)
		if ! output="$(php -l "$file_path" 2>&1)"; then
			echo "PHP syntax error in $file_path:" >&2
			echo "$output" >&2
			exit 2
		fi
		;;
	*.json)
		if ! output="$(python3 -m json.tool "$file_path" 2>&1 >/dev/null)"; then
			echo "Invalid JSON in $file_path:" >&2
			echo "$output" >&2
			exit 2
		fi
		;;
esac

exit 0
