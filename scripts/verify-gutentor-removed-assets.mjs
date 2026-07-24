/**
 * Verify Gutentor assets no longer load on /swell-page/
 */
import { chromium } from 'playwright';

const BASE = 'http://220.158.21.114:8080';
const AUTH = { username: 'pegasus', password: 'pegasus' };

const GUTENTOR_PATTERNS = [
  'gutentor',
  'animate.min.css',
  'wow.min.js',
  'fontawesome',
  'font-awesome',
];

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.setExtraHTTPHeaders({ Authorization: 'Basic ' + Buffer.from(`${AUTH.username}:${AUTH.password}`).toString('base64') });

  const loaded = [];
  page.on('requestfinished', (req) => {
    const url = req.url();
    if (GUTENTOR_PATTERNS.some((p) => url.toLowerCase().includes(p))) {
      loaded.push(url);
    }
  });

  await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle' });
  await browser.close();

  console.log(JSON.stringify({ gutentorAssetsLoaded: loaded.length, urls: loaded }, null, 2));
  process.exit(loaded.length > 0 ? 1 : 0);
})();
