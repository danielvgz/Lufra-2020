#!/usr/bin/env bash
set -euo pipefail

echo "== Laravel Backend Setup Script =="
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

fail(){ echo "ERROR: $1" >&2; exit 1; }

command -v php >/dev/null 2>&1 || fail "PHP is not installed. Install PHP CLI (>=8.1) and required extensions."
command -v composer >/dev/null 2>&1 || fail "Composer is not installed. Install Composer first: https://getcomposer.org"

echo "PHP and Composer found."

# If the directory doesn't contain a Laravel app, create one
if [ ! -f composer.json ]; then
  echo "No composer.json found — creating new Laravel project in this directory..."
  # create project in-place
  composer create-project laravel/laravel ./laravel --prefer-dist
else
  echo "composer.json found — assuming Laravel project exists. Skipping create-project."
fi

echo "Installing PHP dependencies (composer install)..."
composer install --no-interaction --prefer-dist

# Prepare environment
if [ -f .env.example ] && [ ! -f .env ]; then
  echo "Copying .env.example to .env"
  cp .env.example .env
fi

echo "Generating application key..."
php artisan key:generate || true

# Node assets (optional)
if [ -f package.json ]; then
  if command -v npm >/dev/null 2>&1; then
    echo "Installing npm dependencies and building assets..."
    npm install
    if npm run --silent build 2>/dev/null; then
      echo "Built assets with 'npm run build'."
    else
      echo "Try 'npm run dev' for development builds (build script not found or failed)."
    fi
  else
    echo "npm not found — skipping frontend build."
  fi
fi

# Git initialization and first commit
if [ ! -d .git ]; then
  echo "Initializing git repository..."
  git init
  # Ensure common Laravel ignores
  cat > .gitignore <<'EOF'
/vendor
/node_modules
/.env
/.idea
/.vscode
/public/storage
/storage/*.key
Homestead.json
Homestead.yaml
/.phpunit.result.cache
EOF
  git add .
  git commit -m "Initial Laravel project scaffold"
else
  echo "Git repo already initialized."
fi

echo "Setup complete. Next steps you may want to run (if not already done):"
echo "  - Edit .env with your DB and app values"
echo "  - php artisan migrate"
echo "  - php artisan storage:link"
echo "To create a GitHub repo and push, use:"
echo "  gh repo create <owner>/<repo> --public --source=. --remote=origin && git push -u origin main"

echo "== Done =="
