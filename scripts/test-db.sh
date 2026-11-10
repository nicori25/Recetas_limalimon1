#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
container="limalimon-policy-test-$$"
docker_args=(--host=unix:///var/run/docker.sock)
docker_local() { env -u DOCKER_HOST -u DOCKER_CONTEXT -u DOCKER_TLS -u DOCKER_TLS_VERIFY -u DOCKER_CERT_PATH docker "${docker_args[@]}" "$@"; }
trap 'docker_local rm -f "$container" >/dev/null 2>&1 || true' EXIT
docker_local run --network none -d --name "$container" -e POSTGRES_HOST_AUTH_METHOD=trust postgres:17-alpine >/dev/null
for attempt in {1..30}; do if docker_local exec "$container" pg_isready -U postgres >/dev/null 2>&1; then break; fi; sleep 1; done
for file in tests/sql/bootstrap.sql supabase/migrations/202611090001_limalimon.sql tests/sql/policies.sql; do
 docker_local exec -i "$container" psql -v ON_ERROR_STOP=1 -U postgres < "$file"
done
