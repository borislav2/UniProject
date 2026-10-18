"""Find the contact email each business publishes on its own website.

Harvey looks for named people on "team" pages and skips role addresses like
info@ or office@, which is exactly what small Bulgarian businesses publish. So
for every company in Harvey's database that has a website, this reads the home
page and up to three contact/about pages, takes the address the business itself
lists (its own domain, or a free mailbox such as abv.bg or gmail.com; never a
web agency's address in the footer), checks it with Harvey's verifier (Reoon if
REOON_API_KEY is set) and writes a CSV the Creatium admin imports
(„Потенциални клиенти“ → „Внеси от сървъра“).

Results are cached in site-emails.json next to the output, so each site is read
and each address verified at most once a month.

    <harvey>/.venv/bin/python find_site_emails.py <harvey folder> <out.csv>

The server's daily export (deploy/harvey/install-harvey.sh) runs it.
"""
import asyncio
import csv
import html
import json
import os
import re
import sqlite3
import sys
import time
from pathlib import Path
from urllib.parse import unquote, urljoin, urlparse

import httpx

RECHECK_DAYS = 30
RECHECK_GUESS_DAYS = 3  # an address we could not verify (no key yet, DNS hiccup) is tried again sooner
MAX_EXTRA_PAGES = 3
PAUSE = 1.0
HEADERS = {"User-Agent": "Mozilla/5.0 (compatible; CreatiumLab/1.0; +https://creatiumlab.com)"}

FREE_MAIL = {
    "abv.bg", "gmail.com", "mail.bg", "dir.bg", "yahoo.com", "hotmail.com",
    "outlook.com", "live.com", "icloud.com", "googlemail.com", "gbg.bg",
}
PREFERRED = ("info", "office", "contact", "contacts", "kontakt", "kontakti", "hello",
             "reservations", "reservation", "booking", "rezervacii", "reception", "sales")
SKIP_LOCAL = ("noreply", "no-reply", "donotreply", "webmaster", "postmaster", "abuse", "privacy", "gdpr", "dpo")
CONTACT_WORDS = ("kontakt", "контакт", "contact", "za-nas", "за нас", "about", "svarzhete", "свържете")

EMAIL_RE = re.compile(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}")
MAILTO_RE = re.compile(r"""href\s*=\s*["']mailto:([^"'?]+)""", re.I)
CFEMAIL_RE = re.compile(r"""data-cfemail\s*=\s*["']([0-9a-fA-F]+)["']""")
LINK_RE = re.compile(r"""<a\b[^>]*href\s*=\s*["']([^"'#]+)["'][^>]*>(.*?)</a>""", re.I | re.S)
TAG_RE = re.compile(r"<[^>]+>")


def decode_cfemail(hexstr: str) -> str:
    """Cloudflare's email protection: XOR every byte with the first one."""
    try:
        data = bytes.fromhex(hexstr)
        return "".join(chr(b ^ data[0]) for b in data[1:])
    except ValueError:
        return ""


def emails_in(page: str) -> list[str]:
    found = [unquote(m) for m in MAILTO_RE.findall(page)]
    found += [decode_cfemail(h) for h in CFEMAIL_RE.findall(page)]
    found += EMAIL_RE.findall(html.unescape(TAG_RE.sub(" ", page)))
    return [e.strip().lower() for e in found if e]


def acceptable(email: str, domain: str) -> bool:
    if not EMAIL_RE.fullmatch(email):
        return False
    local, _, host = email.partition("@")
    if host.endswith((".png", ".jpg", ".jpeg", ".gif", ".webp", ".svg")):
        return False
    if any(local.startswith(s) for s in SKIP_LOCAL):
        return False
    site = domain.removeprefix("www.")
    # Only the business's own address: its domain or a free mailbox. A third-party
    # domain on the page is usually the web agency or a platform, not the business.
    return host == site or host.endswith("." + site) or site.endswith("." + host) or host in FREE_MAIL


def best(emails: list[str], domain: str) -> str:
    site = domain.removeprefix("www.")

    def rank(email: str) -> tuple:
        local, _, host = email.partition("@")
        own = host == site or host.endswith("." + site) or site.endswith("." + host)
        preferred = next((i for i, p in enumerate(PREFERRED) if local == p or local.startswith(p)), len(PREFERRED))
        return (0 if own else 1, preferred, len(email))

    unique = sorted(set(emails), key=rank)
    return unique[0] if unique else ""


def contact_links(page: str, base: str) -> list[str]:
    host = urlparse(base).netloc
    links = []
    for href, text in LINK_RE.findall(page):
        target = urljoin(base, href.strip())
        if urlparse(target).netloc != host or target.rstrip("/") == base.rstrip("/"):
            continue
        hay = (unquote(href) + " " + TAG_RE.sub(" ", text)).lower()
        if any(w in hay for w in CONTACT_WORDS) and target not in links:
            links.append(target)
    return links[:MAX_EXTRA_PAGES]


async def fetch(client: httpx.AsyncClient, url: str) -> str:
    try:
        r = await client.get(url)
    except httpx.HTTPError:
        return ""
    if r.status_code != 200 or "html" not in r.headers.get("content-type", "html"):
        return ""
    return r.text[:2_000_000]


async def find_on_site(client: httpx.AsyncClient, domain: str, website: str) -> tuple[str, str]:
    """(email, page it was found on) or ("", "")."""
    start = website if website.startswith(("http://", "https://")) else f"https://{domain}/"
    home = await fetch(client, start)
    if not home and start.startswith("https://"):
        start = "http://" + start[len("https://"):]
        home = await fetch(client, start)
    if not home:
        return "", ""

    pages = [(start, home)]
    for link in contact_links(home, start):
        await asyncio.sleep(PAUSE)
        pages.append((link, await fetch(client, link)))

    for url, page in reversed(pages):  # contact pages first, then the home page
        email = best([e for e in emails_in(page) if acceptable(e, domain)], domain)
        if email:
            return email, url
    return "", ""


async def verify(email: str, env) -> str:
    """Harvey's own verifier: verified / risky / invalid, or guess when it can't tell."""
    try:
        from harvey.integrations.email_finder import _verify_candidate, classify_mx, get_mx_host
    except ImportError:
        return "guess"
    mx = await get_mx_host(email.split("@")[1])
    # No MX answer can be a DNS hiccup; never call a published address invalid on that alone.
    status, catch_all = await _verify_candidate(email, classify_mx(mx or ""), env)
    return status or ("risky" if catch_all else "guess")


async def main() -> int:
    if len(sys.argv) != 3:
        print(__doc__)
        return 2
    harvey_dir, out = Path(sys.argv[1]).resolve(), Path(sys.argv[2])
    cache_path = out.with_name("site-emails.json")
    cache = json.loads(cache_path.read_text(encoding="utf-8")) if cache_path.exists() else {}

    os.chdir(harvey_dir)  # Harvey reads .env (REOON_API_KEY) from its own folder
    sys.path.insert(0, str(harvey_dir))
    try:
        from harvey.config import load_env
        env = load_env()
    except Exception:
        env = None

    con = sqlite3.connect(f"file:{harvey_dir / 'data' / 'harvey.db'}?mode=ro", uri=True)
    companies = con.execute(
        "SELECT id, name, domain, website, industry FROM companies WHERE domain != '' ORDER BY name"
    ).fetchall()
    con.close()

    now = time.time()
    checked = 0
    async with httpx.AsyncClient(timeout=10, follow_redirects=True, headers=HEADERS) as client:
        for cid, name, domain, website, industry in companies:
            entry = cache.get(cid)
            days = RECHECK_GUESS_DAYS if entry and entry.get("status") == "guess" else RECHECK_DAYS
            if entry and now - entry.get("checked_at", 0) < days * 86400:
                continue
            email, page = await find_on_site(client, domain, website or "")
            status = await verify(email, env) if email else ""
            cache[cid] = {"email": email, "status": status, "page": page, "checked_at": now}
            checked += 1
            if checked % 20 == 0:
                cache_path.write_text(json.dumps(cache, ensure_ascii=False), encoding="utf-8")
            await asyncio.sleep(PAUSE)
    cache_path.write_text(json.dumps(cache, ensure_ascii=False), encoding="utf-8")

    rows = []
    for cid, name, domain, website, industry in companies:
        entry = cache.get(cid) or {}
        if not entry.get("email") or entry.get("status") == "invalid":
            continue
        rows.append({
            "email": entry["email"], "first_name": "", "last_name": "", "company_name": name,
            "title": "", "website": website or f"https://{domain}", "industry": industry,
            "personalization": f"Имейлът е публикуван на сайта: {entry.get('page', '')}",
            "email_status": entry["status"], "score": 60 if entry["status"] == "verified" else 40,
        })
    with out.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=list(rows[0]) if rows else ["email"])
        writer.writeheader()
        writer.writerows(rows)

    statuses = {}
    for row in rows:
        statuses[row["email_status"]] = statuses.get(row["email_status"], 0) + 1
    print(f"site emails: {len(companies)} sites, {checked} read now, {len(rows)} addresses {statuses} -> {out}")
    return 0


if __name__ == "__main__":
    sys.exit(asyncio.run(main()))
