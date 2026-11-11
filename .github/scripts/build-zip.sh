#!/usr/bin/env bash
#
# Build the release zip with `git archive`, which honors the export-ignore
# lines in .gitattributes, then fail if the zip leaks an excluded path or
# lacks a required file. Prints the zip path. The zip is built from HEAD, so
# uncommitted changes are not in it.
#
# Usage: .github/scripts/build-zip.sh   (from anywhere inside the repo)

set -euo pipefail

root="$(git rev-parse --show-toplevel)"
cd "$root"

# The zip's folder and file name come from the Text Domain header.
slug="$(sed -n 's/^[[:space:]]*Text Domain:[[:space:]]*//p' style.css | head -n 1 | tr -d '\r' | sed 's/[[:space:]]*$//')"
if [[ -z "$slug" ]]; then
	echo "build-zip: no Text Domain header in style.css" >&2
	exit 1
fi

zip_path="build/${slug}.zip"
mkdir -p build
rm -f "$zip_path"
git archive --format=zip --prefix="${slug}/" -o "$zip_path" HEAD

listing="$(unzip -Z1 "$zip_path")"
failed=0

# The excluded paths are the export-ignore lines in .gitattributes plus the
# floor below, which keeps the guard meaningful if someone deletes a line. A
# directory matches its whole subtree; a name matches the top level only.
excluded="/.github /.claude /agents /tests /CONTRIBUTING.md /CLAUDE.md"
excluded+=" $(awk '$2 == "export-ignore" { print $1 }' .gitattributes | tr '\n' ' ')"

for path in $excluded; do
	name="${path#/}"
	if grep -Eq "^${slug}/${name//./\\.}(/|$)" <<<"$listing"; then
		echo "build-zip: excluded path is in the zip: ${name}" >&2
		failed=1
	fi
done

for required in AGENTS.md style.css functions.php readme.txt; do
	if ! grep -qx "${slug}/${required}" <<<"$listing"; then
		echo "build-zip: required file is missing from the zip: ${required}" >&2
		failed=1
	fi
done

if [[ "$failed" -ne 0 ]]; then
	exit 1
fi

echo "$zip_path"
