#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/../../.."

tls_dir=$(mktemp -d)
openssl req -x509 -newkey rsa:2048 -noenc -days 2 \
  -subj '/CN=Shield LDAP test CA' -addext 'basicConstraints=critical,CA:TRUE' \
  -keyout "$tls_dir/ca.key" -out "$tls_dir/ca.pem"
openssl req -newkey rsa:2048 -noenc -subj '/CN=Shield LDAP fixture' \
  -keyout "$tls_dir/server.key" -out "$tls_dir/server.csr"
printf 'subjectAltName=IP:127.0.0.1\nextendedKeyUsage=serverAuth\n' > "$tls_dir/server.ext"
openssl x509 -req -days 2 -in "$tls_dir/server.csr" -CA "$tls_dir/ca.pem" \
  -CAkey "$tls_dir/ca.key" -CAcreateserial -extfile "$tls_dir/server.ext" \
  -out "$tls_dir/server.pem"
openssl req -x509 -newkey rsa:2048 -noenc -days 2 \
  -subj '/CN=Untrusted Shield LDAP test CA' -addext 'basicConstraints=critical,CA:TRUE' \
  -keyout /dev/null -out "$tls_dir/untrusted-ca.pem"

image=$(docker build --quiet tests/_support/LDAP)
container=$(docker run --rm --detach \
  -p 127.0.0.1::1389 -p 127.0.0.1::1636 \
  --mount "type=bind,src=$tls_dir,dst=/tls,readonly" "$image")

stop_fixture() {
  local status=$?
  if (( status != 0 )); then
    docker logs --tail 30 "$container" >&2 || true
    docker inspect --format '{{range .State.Health.Log}}{{.Output}}{{end}}' "$container" >&2 || true
  fi
  docker stop "$container" > /dev/null || true
}
trap stop_fixture EXIT

for (( attempt = 0; attempt < 30; attempt++ )); do
  health=$(docker inspect --format '{{.State.Health.Status}}' "$container")
  case "$health" in
    healthy|unhealthy) break ;;
  esac
  sleep 1
done

if [[ "$health" != healthy ]]; then
  printf 'OpenLDAP fixture failed its TLS and service bind checks (%s).\n' "$health" >&2
  exit 1
fi
printf 'OpenLDAP smoke check passed for LDAPS and StartTLS.\n'

starttls=$(docker port "$container" 1389/tcp)
ldaps=$(docker port "$container" 1636/tcp)
LDAPTLS_CACERT="$tls_dir/ca.pem" LDAPTLS_REQCERT=demand LDAPTLS_REQSAN=demand \
  SHIELD_LDAP_TEST_PORT="${starttls##*:}" SHIELD_LDAP_TEST_LDAPS_PORT="${ldaps##*:}" \
  SHIELD_LDAP_TEST_WRONG_HOST=localhost SHIELD_LDAP_TEST_UNTRUSTED_CA="$tls_dir/untrusted-ca.pem" \
  vendor/bin/phpunit --no-extensions --no-coverage --fail-on-skipped --fail-on-empty-test-suite \
  tests/Authentication/LDAP/NativeLDAPDirectoryIntegrationTest.php
