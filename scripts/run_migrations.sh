#!/bin/bash
set -e

if [ -z "$DATABASE_URL" ]; then
  echo "DATABASE_URL is not set."
  exit 1
fi

psql "$DATABASE_URL" -f migrations/001_create_companies.sql
psql "$DATABASE_URL" -f migrations/002_create_documents.sql
psql "$DATABASE_URL" -f migrations/003_create_diligence_items.sql

echo "Migrations completed."