"""Teach Harvey's free OpenStreetMap source the businesses Creatium Lab targets.

Harvey's OpenStreetMap provider only knows a few US trades (dentist, lawyer,
roofer...) and refuses "restaurant" or "beauty salon". This adds the matching
OSM tags to its TAG_MAP, and makes it try the next public Overpass server when
one answers 502/503 (overloaded) instead of giving up on that city. Safe to run again: it changes nothing the second time.

    python osm_categories.py <harvey folder>

The server installer (deploy/harvey/install-harvey.sh) runs it after every update.
"""
import sys
from pathlib import Path

MARKER = "# creatium: extra categories"
ANCHOR = "    TAG_MAP = {\n"
# Needles are matched as substrings of the lower-cased industry, first match wins,
# so these go after Harvey's own entries ("roofing contractor" stays a roofer).
EXTRA = {
    "restaurant": ["amenity=restaurant"],
    "beauty": ["shop=beauty", "shop=hairdresser"],
    "salon": ["shop=beauty", "shop=hairdresser"],
    "hair": ["shop=hairdresser"],
    "car repair": ["shop=car_repair"],
    "hotel": ["tourism=hotel", "tourism=guest_house"],
    "physio": ["healthcare=physiotherapist"],
    "veterinar": ["amenity=veterinary"],
}
# Overpass answers 502/503 when overloaded; Harvey only moves to the next mirror on 429/504.
RETRY_OLD = "if r.status_code not in (429, 504):"
RETRY_NEW = "if r.status_code not in (429, 502, 503, 504):"


def add_categories(source: str) -> str:
    if MARKER in source:
        print("osm categories: already added")
        return source
    start = source.find(ANCHOR)
    end = source.find("\n    }\n", start)
    if start < 0 or end < 0:
        print("osm categories: TAG_MAP not found (Harvey changed?), nothing added")
        return source
    lines = [f"        {MARKER}\n"] + [
        f"        {needle!r}: {tags!r},\n" for needle, tags in EXTRA.items()
    ]
    print(f"osm categories: added {', '.join(EXTRA)}")
    return source[: end + 1] + "".join(lines) + source[end + 1 :]


def retry_overloaded(source: str) -> str:
    """Try the next Overpass mirror on 502/503 too, not only on 429/504."""
    if RETRY_NEW in source:
        print("osm mirrors: already retry on 502/503")
        return source
    if RETRY_OLD not in source:
        print("osm mirrors: retry check not found (Harvey changed?), left as is")
        return source
    print("osm mirrors: now also retry on 502/503")
    return source.replace(RETRY_OLD, RETRY_NEW)


def main() -> int:
    if len(sys.argv) != 2:
        print(__doc__)
        return 2
    path = Path(sys.argv[1]) / "harvey" / "collectors" / "discover.py"
    source = path.read_text(encoding="utf-8")
    patched = retry_overloaded(add_categories(source))
    if patched != source:
        path.write_text(patched, encoding="utf-8")
    return 0

if __name__ == "__main__":
    sys.exit(main())
