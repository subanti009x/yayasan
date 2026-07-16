const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const ROOT = 'C:\\yayasansekolah';
const OUT = path.join(ROOT, 'manual-assets');
const BASE = 'http://127.0.0.1:8080';
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const USER_DATA = path.join(OUT, 'chrome-profile');
const PORT = 9223;

fs.mkdirSync(OUT, { recursive: true });

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function waitForJson(url, timeoutMs = 12000) {
  const start = Date.now();
  let lastError;
  while (Date.now() - start < timeoutMs) {
    try {
      const res = await fetch(url);
      if (res.ok) return await res.json();
      lastError = new Error(`HTTP ${res.status}`);
    } catch (err) {
      lastError = err;
    }
    await sleep(250);
  }
  throw lastError || new Error(`Timed out waiting for ${url}`);
}

class CDP {
  constructor(wsUrl) {
    this.ws = new WebSocket(wsUrl);
    this.nextId = 1;
    this.pending = new Map();
    this.events = [];
    this.ws.onmessage = (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id && this.pending.has(msg.id)) {
        const { resolve, reject } = this.pending.get(msg.id);
        this.pending.delete(msg.id);
        if (msg.error) reject(new Error(msg.error.message));
        else resolve(msg.result || {});
      } else if (msg.method) {
        this.events.push(msg);
      }
    };
  }

  async open() {
    while (this.ws.readyState === WebSocket.CONNECTING) {
      await sleep(25);
    }
  }

  send(method, params = {}) {
    const id = this.nextId++;
    this.ws.send(JSON.stringify({ id, method, params }));
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      setTimeout(() => {
        if (this.pending.has(id)) {
          this.pending.delete(id);
          reject(new Error(`CDP timeout: ${method}`));
        }
      }, 15000);
    });
  }

  async waitForEvent(method, timeoutMs = 15000) {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
      const index = this.events.findIndex((event) => event.method === method);
      if (index >= 0) return this.events.splice(index, 1)[0];
      await sleep(50);
    }
    throw new Error(`Timed out waiting for ${method}`);
  }

  close() {
    this.ws.close();
  }
}

async function newTab() {
  const target = await fetch(`http://127.0.0.1:${PORT}/json/new?about:blank`, { method: 'PUT' }).then((r) => r.json());
  const cdp = new CDP(target.webSocketDebuggerUrl);
  await cdp.open();
  await cdp.send('Page.enable');
  await cdp.send('Runtime.enable');
  await cdp.send('DOM.enable');
  await cdp.send('Emulation.setDeviceMetricsOverride', {
    width: 1440,
    height: 1000,
    deviceScaleFactor: 1,
    mobile: false,
  });
  return cdp;
}

async function navigate(cdp, url) {
  await cdp.send('Page.navigate', { url });
  await cdp.waitForEvent('Page.loadEventFired');
  await sleep(2500);
}

async function evalJs(cdp, expression, awaitPromise = false) {
  return cdp.send('Runtime.evaluate', {
    expression,
    awaitPromise,
    returnByValue: true,
  });
}

async function highlight(cdp, selectors) {
  const payload = JSON.stringify(selectors);
  await evalJs(cdp, `
    (() => {
      document.querySelectorAll('.manual-highlight, .manual-label').forEach((node) => node.remove());
      const items = ${payload};
      for (const item of items) {
        const el = document.querySelector(item.selector);
        if (!el) continue;
        const rect = el.getBoundingClientRect();
        const box = document.createElement('div');
        box.className = 'manual-highlight';
        box.style.position = 'absolute';
        box.style.left = (rect.left + window.scrollX - 6) + 'px';
        box.style.top = (rect.top + window.scrollY - 6) + 'px';
        box.style.width = (rect.width + 12) + 'px';
        box.style.height = (rect.height + 12) + 'px';
        box.style.border = '4px solid #e11d48';
        box.style.borderRadius = '10px';
        box.style.boxShadow = '0 0 0 9999px rgba(15, 23, 42, 0.10)';
        box.style.pointerEvents = 'none';
        box.style.zIndex = '2147483646';
        document.body.appendChild(box);

        const label = document.createElement('div');
        label.className = 'manual-label';
        label.textContent = item.label;
        label.style.position = 'absolute';
        label.style.left = (rect.left + window.scrollX - 6) + 'px';
        label.style.top = Math.max(8, rect.top + window.scrollY - 34) + 'px';
        label.style.background = '#e11d48';
        label.style.color = '#ffffff';
        label.style.font = '700 13px Arial, sans-serif';
        label.style.padding = '5px 9px';
        label.style.borderRadius = '7px';
        label.style.zIndex = '2147483647';
        document.body.appendChild(label);
      }
    })();
  `);
  await sleep(250);
}

async function screenshot(cdp, name, fullPage = false) {
  const result = await cdp.send('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: fullPage,
    fromSurface: true,
  });
  const file = path.join(OUT, name);
  fs.writeFileSync(file, Buffer.from(result.data, 'base64'));
  return file;
}

async function scrollTo(cdp, selector) {
  await evalJs(cdp, `document.querySelector(${JSON.stringify(selector)})?.scrollIntoView({block:'center'});`);
  await sleep(800);
}

async function loginAdmin(cdp) {
  await navigate(cdp, `${BASE}/admin.php`);
  await evalJs(cdp, `
    document.querySelector('input[name="password"]').value = 'cendekia-admin';
    document.querySelector('form').requestSubmit();
  `);
  await cdp.waitForEvent('Page.loadEventFired');
  await sleep(1000);
}

async function main() {
  const chrome = spawn(CHROME, [
    '--headless=new',
    `--remote-debugging-port=${PORT}`,
    `--user-data-dir=${USER_DATA}`,
    '--disable-gpu',
    '--no-first-run',
    '--no-default-browser-check',
    'about:blank',
  ], { stdio: 'ignore' });

  try {
    await waitForJson(`http://127.0.0.1:${PORT}/json/version`);
    const cdp = await newTab();

    await navigate(cdp, `${BASE}/index.php`);
    await highlight(cdp, [
      { selector: 'header nav', label: 'Navigasi utama' },
      { selector: 'a[href="#unit-sekolah"]', label: 'Tombol unit sekolah' },
    ]);
    await screenshot(cdp, '01-beranda-dashboard.png');

    await scrollTo(cdp, '#unit-sekolah');
    await highlight(cdp, [
      { selector: '#unit-sekolah .grid article:first-child', label: 'Kartu unit sekolah' },
      { selector: '#unit-sekolah a[href="tk-paud.php"]', label: 'Lihat Sekolah' },
    ]);
    await screenshot(cdp, '02-beranda-unit-sekolah.png');

    await navigate(cdp, `${BASE}/tk-paud.php`);
    await highlight(cdp, [
      { selector: 'a[href="#pendaftaran"]', label: 'Daftar Sekarang' },
      { selector: 'a[target="_blank"]', label: 'Chat WhatsApp' },
    ]);
    await screenshot(cdp, '03-halaman-unit-hero.png');

    await scrollTo(cdp, '#pendaftaran');
    await highlight(cdp, [
      { selector: 'form[data-google-form]', label: 'Form pendaftaran' },
      { selector: 'input[name="entry.794168599"]', label: 'Nama calon siswa' },
    ]);
    await screenshot(cdp, '04-form-pendaftaran.png');

    await navigate(cdp, `${BASE}/articles.php`);
    await highlight(cdp, [
      { selector: 'main section:nth-of-type(2)', label: 'Daftar artikel' },
      { selector: 'article:first-of-type a', label: 'Buka detail artikel' },
    ]);
    await screenshot(cdp, '05-artikel.png');

    await navigate(cdp, `${BASE}/faq.php`);
    await highlight(cdp, [
      { selector: 'details:first-of-type', label: 'Pertanyaan FAQ' },
      { selector: 'a[target="_blank"]', label: 'Tanya Admin' },
    ]);
    await screenshot(cdp, '06-faq.png');

    await navigate(cdp, `${BASE}/kontak.php`);
    await highlight(cdp, [
      { selector: 'a[target="_blank"]', label: 'WhatsApp Yayasan' },
      { selector: 'iframe, .rounded-lg iframe', label: 'Peta lokasi' },
    ]);
    await screenshot(cdp, '07-kontak.png');

    await navigate(cdp, `${BASE}/admin.php`);
    await highlight(cdp, [
      { selector: 'input[name="password"]', label: 'Password admin' },
      { selector: 'button[type="submit"]', label: 'Masuk Dashboard' },
    ]);
    await screenshot(cdp, '08-admin-login.png');

    await loginAdmin(cdp);
    await highlight(cdp, [
      { selector: 'nav a[href="?tab=articles"]', label: 'Tab Artikel' },
      { selector: 'form input[name="title"]', label: 'Form artikel' },
      { selector: '.rounded-lg.border.border-slate-200.bg-white.p-6.shadow-sm h2', label: 'Daftar artikel' },
    ]);
    await screenshot(cdp, '09-admin-artikel.png');

    await navigate(cdp, `${BASE}/admin.php?tab=branding`);
    await highlight(cdp, [
      { selector: 'select[name="theme_color"]', label: 'Warna tema' },
      { selector: 'select[name="logo_type"]', label: 'Tipe logo' },
      { selector: 'button[type="submit"]', label: 'Simpan perubahan' },
    ]);
    await screenshot(cdp, '10-admin-branding.png');

    await navigate(cdp, `${BASE}/admin.php?tab=foundation`);
    await highlight(cdp, [
      { selector: 'input[name="name"]', label: 'Nama Yayasan' },
      { selector: 'input[name="phone"]', label: 'Nomor WhatsApp' },
      { selector: 'input[name="maps_embed"]', label: 'Maps embed' },
    ]);
    await screenshot(cdp, '11-admin-identitas.png');

    await navigate(cdp, `${BASE}/admin.php?tab=schools`);
    await highlight(cdp, [
      { selector: '.grid.sm\\\\:grid-cols-2 > div:first-child', label: 'Unit sekolah' },
      { selector: 'a[href*="edit_school"]', label: 'Edit konten unit' },
    ]);
    await screenshot(cdp, '12-admin-unit-sekolah.png');

    await navigate(cdp, `${BASE}/admin.php?tab=schools&edit_school=tk-paud`);
    await highlight(cdp, [
      { selector: 'input[name="name"]', label: 'Nama sekolah' },
      { selector: '#programs-container', label: 'Program unggulan' },
      { selector: 'textarea[name="facilities"]', label: 'Fasilitas' },
    ]);
    await screenshot(cdp, '13-admin-edit-unit.png');

    await navigate(cdp, `${BASE}/admin.php?tab=faq`);
    await highlight(cdp, [
      { selector: 'input[name="question"]', label: 'Pertanyaan' },
      { selector: 'textarea[name="answer"]', label: 'Jawaban' },
      { selector: '.rounded-lg.border.border-slate-200.bg-white.p-6.shadow-sm h2', label: 'Daftar FAQ' },
    ]);
    await screenshot(cdp, '14-admin-faq.png');

    cdp.close();
  } finally {
    chrome.kill();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
