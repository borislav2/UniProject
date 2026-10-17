"""Teach Harvey's free OpenStreetMap source the businesses Creatium Lab targets.

Harvey's OpenStreetMap provider only knows a few US trades (dentist, lawyer,
roofer...) and refuses "restaurant" or "beauty salon". This adds the matching
OSM tags to its TAG_MAP. Safe to run again: it changes nothing the second time.

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


def main() -> int:
    if len(sys.argv) != 2:
        print(__doc__)
        return 2
    path = Path(sys.argv[1]) / "harvey" / "collectors" / "discover.py"
    source = path.read_text(encoding="utf-8")
    if MARKER in source:
        print("osm categories: already added")
        return 0
    start = source.find(ANCHOR)
    end = source.find("\n    }\n", start)
    if start < 0 or end < 0:
        print("osm categories: TAG_MAP not found (Harvey changed?), nothing added")
        return 1
    lines = [f"        {MARKER}\n"] + [
        f"        {needle!r}: {tags!r},\n" for needle, tags in EXTRA.items()
    ]
    path.write_text(source[: end + 1] + "".join(lines) + source[end + 1 :], encoding="utf-8")
    print(f"osm categories: added {', '.join(EXTRA)}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
