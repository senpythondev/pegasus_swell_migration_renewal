#!/usr/bin/env python3
"""Replace pega-hero-wrap section in WP post 4865."""
import re
import subprocess
import sys

POST_ID = 4865
HERO_PATH = "/home/oono/Wordpress/scripts/top_page/hero.html"

with open(HERO_PATH, encoding="utf-8") as f:
    new_section = f.read().strip()

result = subprocess.run(
    [
        "docker", "exec", "pegasus_wp",
        "wp", "post", "get", str(POST_ID), "--field=post_content",
        "--allow-root", "--path=/var/www/html",
    ],
    capture_output=True,
    text=True,
    check=True,
)
content = result.stdout

pattern = re.compile(
    r'<!-- wp:group \{"tagName":"section","align":"full","className":"pega-hero-wrap".*?</section>\s*<!-- /wp:group -->',
    re.DOTALL,
)
match = pattern.search(content)
if not match:
    print("ERROR: pega-hero-wrap section not found", file=sys.stderr)
    sys.exit(1)

updated = content[: match.start()] + new_section + content[match.end() :]

subprocess.run(
    [
        "docker", "exec", "-i", "pegasus_wp",
        "wp", "post", "update", str(POST_ID), f"--post_content={updated}",
        "--allow-root", "--path=/var/www/html",
    ],
    check=True,
)
print("OK: post 4865 hero section updated")
