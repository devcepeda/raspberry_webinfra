#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$SCRIPT_DIR/.env"

if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  source "$ENV_FILE"
fi

REPO_PATH="${DEPLOY_REPO_PATH:-/var/www/html/raspberry_webinfra}"
BRANCH="${1:-${DEPLOY_DEFAULT_BRANCH:-production}}"
RUN_LOG="${DEPLOY_RUN_LOG:-$SCRIPT_DIR/logs/deploy.log}"
BACKUP_DIR="${DEPLOY_BACKUP_DIR:-$SCRIPT_DIR/backups}"
LOCK_FILE="$SCRIPT_DIR/deploy.lock"

mkdir -p "$(dirname "$RUN_LOG")" "$BACKUP_DIR"

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  echo "[$(date +"%F %T")] deployment already running" | tee -a "$RUN_LOG"
  exit 1
fi

if [[ ! -d "$REPO_PATH/.git" ]]; then
  echo "[$(date +"%F %T")] invalid repository path: $REPO_PATH" | tee -a "$RUN_LOG"
  exit 1
fi

if [[ -n "${DEPLOY_ALLOWED_BRANCHES:-}" ]]; then
  IFS=',' read -r -a ALLOWED <<< "$DEPLOY_ALLOWED_BRANCHES"
  BRANCH_ALLOWED=false
  for allowed in "${ALLOWED[@]}"; do
    if [[ "$BRANCH" == "$allowed" ]]; then
      BRANCH_ALLOWED=true
      break
    fi
  done
  if [[ "$BRANCH_ALLOWED" != true ]]; then
    echo "[$(date +"%F %T")] blocked branch: $BRANCH" | tee -a "$RUN_LOG"
    exit 1
  fi
fi

echo "[$(date +"%F %T")] deployment start for branch=$BRANCH" | tee -a "$RUN_LOG"

cd "$REPO_PATH"
PREV_COMMIT="$(git rev-parse HEAD)"
TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"
BACKUP_FILE="$BACKUP_DIR/backup_${BRANCH}_${TIMESTAMP}_${PREV_COMMIT:0:7}.tar.gz"

tar \
  --exclude='.git' \
  --exclude='deploy/backups' \
  --exclude='deploy/logs' \
  -czf "$BACKUP_FILE" .

echo "[$(date +"%F %T")] backup created: $BACKUP_FILE" | tee -a "$RUN_LOG"

rollback() {
  echo "[$(date +"%F %T")] deployment failed, rollback started" | tee -a "$RUN_LOG"
  git clean -fd -- . ':!deploy/backups' ':!deploy/logs'
  tar -xzf "$BACKUP_FILE" -C "$REPO_PATH"
  git reset --hard "$PREV_COMMIT" >/dev/null 2>&1 || true
  echo "[$(date +"%F %T")] rollback completed to $PREV_COMMIT" | tee -a "$RUN_LOG"
}

trap rollback ERR

git fetch origin "$BRANCH" --prune
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

NEW_COMMIT="$(git rev-parse HEAD)"
echo "[$(date +"%F %T")] deployment success prev=$PREV_COMMIT new=$NEW_COMMIT" | tee -a "$RUN_LOG"
