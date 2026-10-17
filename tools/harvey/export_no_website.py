"""Write the businesses Harvey found WITHOUT a website to a CSV for the Creatium admin.

Harvey's own `harvey export` only lists people with a verified email, so a
business with no website (the best fit for selling one) never leaves its
database. This reads Harvey's SQLite file read-only and writes them out with
their phone number, for calling from the admin („Потенциални клиенти“ → „Без сайт“).

    python export_no_website.py <harvey folder> <out.csv>

The server's daily export (deploy/harvey/install-harvey.sh) runs it.
"""
import csv
import sqlite3
import sys
from pathlib import Path

COLUMNS = ["external_id", "name", "phone", "industry", "location", "source_url"]


def main() -> int:
    if len(sys.argv) != 3:
        print(__doc__)
        return 2
    db = Path(sys.argv[1]) / "data" / "harvey.db"
    out = Path(sys.argv[2])
    con = sqlite3.connect(f"file:{db}?mode=ro", uri=True)
    rows = con.execute(
        f"SELECT {', '.join(COLUMNS)} FROM companies "
        "WHERE domain = '' AND external_id != '' AND name != '' "
        "ORDER BY phone = '', location, name"
    ).fetchall()
    con.close()
    with out.open("w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(COLUMNS)
        writer.writerows(rows)
    print(f"no-website export: {len(rows)} businesses -> {out}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
