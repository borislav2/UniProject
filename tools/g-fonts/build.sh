#!/usr/bin/env bash
# Rebuilds resources/fonts/g (design G fonts) from the @fontsource packages: only the used weights, Cyrillic copied as is,
# Latin cut down to Basic Latin, the few punctuation marks and symbols the site uses, and the layout features that matter.
# Needs: npm ci, python3 -m pip install fonttools brotli. Run from the repository root.
set -euo pipefail
OUT=resources/fonts/g
SRC=node_modules/@fontsource
mkdir -p "$OUT"
UNICODES="U+0020-007E,U+00A0,U+00A9,U+00AB,U+00B7,U+00BB,U+00D7,U+2013,U+2014,U+2018-201E,U+2022,U+2026,U+20AC,U+2212"
FEATURES="kern,liga,ccmp,locl,mark,mkmk,calt,case,tnum,zero"
# family:weight (one file stands in for the neighbouring weights, see the @font-face rules in resources/css/g.css)
for item in geologica:300 onest:400 jetbrains-mono:500; do
    family="${item%%:*}"
    weight="${item##*:}"
    cp "$SRC/$family/files/$family-cyrillic-$weight-normal.woff2" "$OUT/$family-cyrillic-$weight.woff2"
    python3 -m fontTools.subset "$SRC/$family/files/$family-latin-$weight-normal.woff2" \
        --unicodes="$UNICODES" --flavor=woff2 --layout-features="$FEATURES" --no-hinting --desubroutinize \
        --output-file="$OUT/$family-latin-$weight.woff2"
done
ls -l "$OUT"
