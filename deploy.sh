#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -f "$SCRIPT_DIR/scripts/deploy.sh" ]]; then
  echo "Error: scripts/deploy.sh not found" >&2
  exit 1
fi

bash "$SCRIPT_DIR/scripts/deploy.sh" "$@"
