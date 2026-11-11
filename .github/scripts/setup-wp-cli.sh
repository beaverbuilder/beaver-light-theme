#!/usr/bin/env bash
#
# Install a pinned WP-CLI phar as `wp`, after checking it against the
# checksum WP-CLI publishes next to the release.
#
# Usage: .github/scripts/setup-wp-cli.sh [install-dir]   (default: /usr/local/bin, via sudo)

set -euo pipefail

version="2.12.0"
base="https://github.com/wp-cli/wp-cli/releases/download/v${version}"
dest="${1:-/usr/local/bin}"
work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT

curl -fsSL -o "${work}/wp-cli.phar" "${base}/wp-cli-${version}.phar"
expected="$(curl -fsSL "${base}/wp-cli-${version}.phar.sha512")"
echo "${expected}  ${work}/wp-cli.phar" | shasum -a 512 -c -

chmod +x "${work}/wp-cli.phar"
if [[ -w "$dest" ]]; then
	mv "${work}/wp-cli.phar" "${dest}/wp"
else
	sudo mv "${work}/wp-cli.phar" "${dest}/wp"
fi
"${dest}/wp" --info > /dev/null
