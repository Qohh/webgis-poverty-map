<?php
session_start();

// ── CEK AKSES ──────────────────────────────────────────────
// Default: semua yang belum login dianggap guest (hanya lihat)
$isGuest = true;
$isAdmin = false;
$isLoggedIn = false;
$currentUser = '';

if (isset($_SESSION['user'])) {
    $isLoggedIn = true;
    $currentUser = $_SESSION['user'];
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $isGuest = false;
}

// Role yang dikirim ke JS
$roleJS = $isAdmin ? 'admin' : 'guest';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GIS Kemiskinan – Tempat Ibadah</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0f1117;
      color: #e2e8f0;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── HEADER ── */
    #header {
      background: linear-gradient(135deg, #1a1d2e 0%, #16213e 100%);
      border-bottom: 1px solid #2d3748;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      z-index: 1000;
      flex-shrink: 0;
    }
    #header h1 { font-size: 15px; font-weight: 700; color: #63b3ed; letter-spacing: .5px; }
    #header .subtitle { font-size: 11px; color: #718096; }

    /* ── TOOLBAR ── */
    #toolbar { display: flex; gap: 6px; margin-left: auto; align-items: center; }
    .mode-btn {
      padding: 6px 12px; border-radius: 6px; border: 1px solid #2d3748;
      background: #1a202c; color: #a0aec0; font-size: 12px; cursor: pointer; transition: all .2s;
    }
    .mode-btn:hover { border-color: #63b3ed; color: #63b3ed; }
    .mode-btn.active { background: #2b6cb0; border-color: #4299e1; color: #fff; }

    /* user badge & logout */
    .user-badge {
      display: flex; align-items: center; gap: 6px;
      background: #0f1117; border: 1px solid #2d3748; border-radius: 20px;
      padding: 4px 10px; font-size: 12px; color: #a0aec0;
    }
    .user-badge .role-tag {
      background: <?= $isAdmin ? '#2b6cb0' : '#276749' ?>;
      color: #fff; font-size: 10px; font-weight: 700;
      padding: 1px 6px; border-radius: 8px;
    }
    .btn-logout {
      padding: 5px 10px; border-radius: 6px; border: 1px solid #c53030;
      background: transparent; color: #fc8181; font-size: 11px; cursor: pointer; transition: all .2s;
    }
    .btn-logout:hover { background: #c53030; color: #fff; }
    .btn-login-small {
      padding: 5px 12px; border-radius: 6px; border: 1px solid #2b6cb0;
      background: transparent; color: #63b3ed; font-size: 11px; cursor: pointer; transition: all .2s;
    }
    .btn-login-small:hover { background: #2b6cb0; color: #fff; }

    /* ── MAIN ── */
    #main { display: flex; flex: 1; overflow: hidden; }
    #map { flex: 1; z-index: 1; }

    /* ── SIDE PANEL ── */
    #side-panel {
      width: 300px; background: #1a1d2e; border-left: 1px solid #2d3748;
      display: flex; flex-direction: column; overflow-y: auto; flex-shrink: 0;
    }
    .panel-section { border-bottom: 1px solid #2d3748; padding: 14px; }
    .panel-section h3 {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: #63b3ed; margin-bottom: 10px;
    }

    /* stats */
    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat-card { background: #0f1117; border-radius: 8px; padding: 10px; text-align: center; }
    .stat-num { font-size: 22px; font-weight: 700; }
    .stat-label { font-size: 10px; color: #718096; margin-top: 2px; }
    .stat-red    { color: #fc8181; }
    .stat-green  { color: #68d391; }
    .stat-blue   { color: #63b3ed; }
    .stat-yellow { color: #f6e05e; }

    /* selected ibadah */
    #selected-info { display: none; }
    #selected-info.visible { display: block; }
    #radius-display { font-size: 24px; font-weight: 700; color: #63b3ed; text-align: center; margin: 8px 0 4px; }
    #radius-label { text-align: center; font-size: 11px; color: #718096; margin-bottom: 10px; }

    /* slider */
    .slider-wrap { position: relative; padding: 4px 0; }
    input[type=range] {
      width: 100%; -webkit-appearance: none; height: 6px; border-radius: 3px;
      background: #2d3748; outline: none;
    }
    input[type=range]::-webkit-slider-thumb {
      -webkit-appearance: none; width: 20px; height: 20px; border-radius: 50%;
      background: #4299e1; cursor: pointer; box-shadow: 0 0 0 3px rgba(66,153,225,.3);
    }
    .slider-labels { display: flex; justify-content: space-between; font-size: 10px; color: #718096; margin-top: 4px; }
    #btn-save-radius {
      width: 100%; margin-top: 10px; padding: 7px;
      background: #2b6cb0; border: none; border-radius: 6px;
      color: #fff; font-size: 13px; cursor: pointer; transition: background .2s;
    }
    #btn-save-radius:hover { background: #2c5282; }

    /* rumah list in radius */
    #rumah-in-radius { max-height: 180px; overflow-y: auto; }
    .rumah-item { display: flex; align-items: center; gap: 6px; padding: 5px 0; border-bottom: 1px solid #2d3748; font-size: 12px; color: #a0aec0; }
    .rumah-item .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .dot-miskin { background: #fc8181; }
    .dot-aman   { background: #68d391; }

    /* layer toggle */
    .layer-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 13px; }
    .layer-icon { font-size: 18px; width: 24px; text-align: center; }
    .layer-label { flex: 1; color: #cbd5e0; }
    .layer-count { font-size: 10px; background: #2d3748; color: #a0aec0; padding: 2px 6px; border-radius: 10px; }
    .toggle { position: relative; width: 34px; height: 18px; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; inset: 0; background: #2d3748; border-radius: 18px; cursor: pointer; transition: .2s; }
    .toggle-slider:before { content: ''; position: absolute; height: 12px; width: 12px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; }
    .toggle input:checked + .toggle-slider { background: #2b6cb0; }
    .toggle input:checked + .toggle-slider:before { transform: translateX(16px); }

    /* legend */
    .legend-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 12px; color: #a0aec0; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }

    /* leaflet icons */
    .icon-ibadah { font-size: 24px; text-align: center; line-height: 32px; text-shadow: 0 2px 6px rgba(0,0,0,.5); filter: drop-shadow(0 1px 3px rgba(0,0,0,.8)); }

    /* popup */
    .leaflet-popup-content-wrapper {
      background: #1a202c !important; color: #e2e8f0 !important;
      border: 1px solid #2d3748 !important; border-radius: 10px !important;
      box-shadow: 0 10px 30px rgba(0,0,0,.5) !important;
    }
    .leaflet-popup-tip { background: #1a202c !important; }
    .leaflet-popup-content { margin: 12px 16px !important; }
    .popup-title { font-weight: 700; font-size: 14px; color: #63b3ed; margin-bottom: 4px; }
    .popup-row { font-size: 12px; color: #a0aec0; margin: 2px 0; }
    .popup-actions { margin-top: 10px; display: flex; gap: 6px; }
    .popup-btn { flex: 1; padding: 5px 8px; border: none; border-radius: 5px; font-size: 11px; cursor: pointer; font-weight: 600; transition: opacity .2s; }
    .popup-btn:hover { opacity: .85; }
    .btn-edit  { background: #2b6cb0; color: #fff; }
    .btn-del   { background: #c53030; color: #fff; }
    .btn-focus { background: #276749; color: #fff; }

    /* mode indicator */
    #mode-indicator {
      position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
      background: rgba(26,32,44,.9); border: 1px solid #4299e1; color: #63b3ed;
      padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
      z-index: 1000; pointer-events: none; display: none;
    }
    #mode-indicator.visible { display: block; }

    /* scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #1a1d2e; }
    ::-webkit-scrollbar-thumb { background: #2d3748; border-radius: 3px; }

    /* ── DIALOG OVERLAY ── */
    .dialog-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.75); z-index: 9999;
      display: flex; align-items: center; justify-content: center;
    }
    .dialog-box {
      background: #1a202c; border: 1px solid #2d3748; border-radius: 12px;
      padding: 22px 24px; min-width: 300px; max-width: 420px; width: 90%;
      max-height: 90vh; overflow-y: auto;
    }
    .dialog-title { font-weight: 700; font-size: 14px; color: #63b3ed; margin-bottom: 16px; }
    .form-row { margin-bottom: 13px; }
    .form-row label { display: block; font-size: 11px; color: #a0aec0; font-weight: 600; margin-bottom: 4px; }
    .form-row input[type=text],
    .form-row input[type=date],
    .form-row select,
    .form-row textarea {
      width: 100%; padding: 8px 10px; background: #0f1117; border: 1px solid #2d3748;
      border-radius: 6px; color: #e2e8f0; font-size: 13px; outline: none; transition: border-color .2s;
    }
    .form-row input:focus, .form-row select:focus, .form-row textarea:focus { border-color: #4299e1; }
    .form-row textarea { resize: vertical; min-height: 56px; font-family: inherit; }
    .form-row .hint { font-size: 10px; color: #4a5568; margin-top: 3px; }
    .form-row select option { background: #1a202c; }
    .dialog-actions { display: flex; gap: 8px; margin-top: 16px; }
    .dialog-actions button {
      flex: 1; padding: 9px; border: none; border-radius: 7px;
      font-size: 13px; font-weight: 700; cursor: pointer; transition: opacity .2s;
    }
    .dialog-actions button:hover { opacity: .85; }
    .btn-primary { background: #2b6cb0; color: #fff; }
    .btn-cancel  { background: #2d3748; color: #a0aec0; }
    .btn-danger  { background: #c53030; color: #fff; }
    .status-miskin     { background: #742a2a; border-color: #c53030 !important; color: #fc8181; }
    .status-tidak      { background: #1c4532; border-color: #276749 !important; color: #68d391; }
  </style>
</head>
<body>

<!-- HEADER -->
<div id="header">
  <div>
    <h1>🗺 GIS Kemiskinan — Tempat Ibadah</h1>
    <div class="subtitle" id="header-sub">
      <?= $isAdmin ? 'Klik peta untuk tambah data · Klik marker untuk detail' : 'Tampilan pengunjung — hanya lihat data' ?>
    </div>
  </div>
  <div id="toolbar">
    <?php if ($isAdmin): ?>
      <button class="mode-btn active" id="btn-mode-ibadah" onclick="setMode('ibadah')">🕌 Tambah Ibadah</button>
      <button class="mode-btn" id="btn-mode-rumah"  onclick="setMode('rumah')">🏠 Tambah Rumah</button>
      <button class="mode-btn" id="btn-mode-view"   onclick="setMode('view')">👁 Lihat Saja</button>
    <?php endif; ?>

    <div class="user-badge">
      <?php if ($isAdmin): ?>
        👤 <?= htmlspecialchars($currentUser) ?>
        <span class="role-tag">ADMIN</span>
      <?php elseif ($isLoggedIn): ?>
        👤 <?= htmlspecialchars($currentUser) ?>
        <span class="role-tag" style="background:#276749">USER</span>
      <?php else: ?>
        👁 Pengunjung <span class="role-tag" style="background:#4a5568">GUEST</span>
      <?php endif; ?>
    </div>

    <?php if ($isLoggedIn): ?>
      <a href="logout.php"><button class="btn-logout">⬅ Logout</button></a>
    <?php else: ?>
      <a href="login.php"><button class="btn-login-small">🔐 Login</button></a>
    <?php endif; ?>
  </div>
</div>

<!-- MAIN -->
<div id="main">
  <div style="position:relative;flex:1;display:flex;">
    <div id="map"></div>
    <div id="mode-indicator">Mode: Tambah Tempat Ibadah – Klik di peta</div>
  </div>

  <!-- SIDE PANEL -->
  <div id="side-panel">

    <!-- STATS -->
    <div class="panel-section">
      <h3>📊 Statistik</h3>
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-num stat-blue" id="stat-ibadah">0</div>
          <div class="stat-label">Tempat Ibadah</div>
        </div>
        <div class="stat-card">
          <div class="stat-num stat-yellow" id="stat-rumah">0</div>
          <div class="stat-label">Total Rumah</div>
        </div>
        <div class="stat-card">
          <div class="stat-num stat-red" id="stat-miskin">0</div>
          <div class="stat-label">Rumah Miskin</div>
        </div>
        <div class="stat-card">
          <div class="stat-num stat-green" id="stat-terjangkau">0</div>
          <div class="stat-label">Terjangkau Ibadah</div>
        </div>
      </div>
    </div>

    <!-- SELECTED IBADAH -->
    <div class="panel-section" id="selected-info">
      <h3>🎯 Tempat Ibadah Dipilih</h3>
      <div id="selected-name" style="font-weight:600;font-size:13px;margin-bottom:6px;color:#e2e8f0;"></div>
      <div id="selected-alamat" style="font-size:11px;color:#718096;margin-bottom:10px;"></div>
      <div id="radius-display">0 m</div>
      <div id="radius-label">Radius Jangkauan</div>
      <div class="slider-wrap">
        <input type="range" id="radius-slider" min="50" max="2000" step="50" value="200"
               oninput="onSliderMove(this.value)">
        <div class="slider-labels"><span>50 m</span><span>2.000 m</span></div>
      </div>
      <button id="btn-save-radius" onclick="saveRadius()">💾 Simpan Radius</button>

      <div style="margin-top:12px;">
        <h3 style="margin-bottom:6px;">🏠 Rumah dalam Radius (<span id="rumah-count">0</span>)</h3>
        <div id="rumah-in-radius"></div>
      </div>
    </div>

    <!-- LAYER IBADAH -->
    <div class="panel-section">
      <h3>🗂 Layer Tempat Ibadah</h3>
      <div id="layer-ibadah-list"></div>
    </div>

    <!-- LAYER RUMAH -->
    <div class="panel-section">
      <h3>🗂 Layer Rumah</h3>
      <div class="layer-row">
        <span class="layer-icon">🔴</span>
        <span class="layer-label">Rumah Miskin</span>
        <span class="layer-count" id="count-miskin">0</span>
        <label class="toggle"><input type="checkbox" checked onchange="toggleLayer('miskin', this.checked)"><span class="toggle-slider"></span></label>
      </div>
      <div class="layer-row">
        <span class="layer-icon">🟡</span>
        <span class="layer-label">Rumah Tidak Miskin</span>
        <span class="layer-count" id="count-tidak">0</span>
        <label class="toggle"><input type="checkbox" checked onchange="toggleLayer('tidak_miskin', this.checked)"><span class="toggle-slider"></span></label>
      </div>
    </div>

    <!-- LEGEND -->
    <div class="panel-section">
      <h3>📍 Legenda Rumah</h3>
      <div class="legend-row"><div class="legend-dot" style="background:#fc8181"></div> Rumah Miskin (tidak terjangkau)</div>
      <div class="legend-row"><div class="legend-dot" style="background:#68d391"></div> Rumah Miskin (dalam radius ibadah)</div>
      <div class="legend-row"><div class="legend-dot" style="background:#f6e05e"></div> Rumah Tidak Miskin</div>
    </div>

  </div><!-- /side-panel -->
</div>

<script>
// ════════════════════════════════════════════════
// ROLE (dikirim dari PHP)
// ════════════════════════════════════════════════
const ROLE = '<?= $roleJS ?>';       // 'admin' | 'guest'
const IS_ADMIN = ROLE === 'admin';

// ════════════════════════════════════════════════
// CONFIG
// ════════════════════════════════════════════════
const JENIS_CONFIG = {
  'Masjid'   : { emoji: '🕌', color: '#4299e1' },
  'Gereja'   : { emoji: '⛪', color: '#9f7aea' },
  'Pura'     : { emoji: '🛕', color: '#ed8936' },
  'Vihara'   : { emoji: '☸️', color: '#ecc94b' },
  'Klenteng' : { emoji: '🏮', color: '#fc8181' },
  'Lainnya'  : { emoji: '🙏', color: '#68d391' },
};
const JENIS_LIST = Object.keys(JENIS_CONFIG);

// ════════════════════════════════════════════════
// STATE
// ════════════════════════════════════════════════
let mode = IS_ADMIN ? 'ibadah' : 'view';
let ibadahData  = [];
let rumahData   = [];
let ibadahMarkers = {};
let rumahMarkers  = {};
let layerGroups = {};
JENIS_LIST.forEach(j => { layerGroups[j] = L.layerGroup(); });
let layerRumahMiskin      = L.layerGroup();
let layerRumahTidakMiskin = L.layerGroup();
let selectedIbadahId = null;
let activeCircle     = null;
let highlightedRumah = [];

// ════════════════════════════════════════════════
// MAP SETUP
// ════════════════════════════════════════════════
const map = L.map('map').setView([-0.055326, 109.349500], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19, attribution: '©OpenStreetMap contributors'
}).addTo(map);

JENIS_LIST.forEach(j => layerGroups[j].addTo(map));
layerRumahMiskin.addTo(map);
layerRumahTidakMiskin.addTo(map);

// ════════════════════════════════════════════════
// LAYER PANEL
// ════════════════════════════════════════════════
function buildLayerPanel() {
  const el = document.getElementById('layer-ibadah-list');
  el.innerHTML = '';
  JENIS_LIST.forEach(j => {
    const cfg = JENIS_CONFIG[j];
    const count = ibadahData.filter(d => d.jenis === j).length;
    el.innerHTML += `
      <div class="layer-row">
        <span class="layer-icon">${cfg.emoji}</span>
        <span class="layer-label">${j}</span>
        <span class="layer-count" id="lcount-${j}">${count}</span>
        <label class="toggle">
          <input type="checkbox" checked onchange="toggleIbadahLayer('${j}', this.checked)">
          <span class="toggle-slider"></span>
        </label>
      </div>`;
  });
}

function toggleIbadahLayer(jenis, visible) {
  if (visible) map.addLayer(layerGroups[jenis]);
  else         map.removeLayer(layerGroups[jenis]);
}
function toggleLayer(status, visible) {
  if (status === 'miskin') {
    if (visible) map.addLayer(layerRumahMiskin);
    else         map.removeLayer(layerRumahMiskin);
  } else {
    if (visible) map.addLayer(layerRumahTidakMiskin);
    else         map.removeLayer(layerRumahTidakMiskin);
  }
}

// ════════════════════════════════════════════════
// MODE
// ════════════════════════════════════════════════
function setMode(m) {
  if (!IS_ADMIN) return; // guest tidak bisa ubah mode
  mode = m;
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  const activeBtn = document.getElementById('btn-mode-' + m);
  if (activeBtn) activeBtn.classList.add('active');
  const ind = document.getElementById('mode-indicator');
  if (m === 'view') {
    ind.classList.remove('visible');
    map.getContainer().style.cursor = '';
  } else {
    const labels = { ibadah: 'Tambah Tempat Ibadah', rumah: 'Tambah Rumah' };
    ind.textContent = `Mode: ${labels[m]} – Klik di peta`;
    ind.classList.add('visible');
    map.getContainer().style.cursor = 'crosshair';
  }
}

// ════════════════════════════════════════════════
// ICON FACTORY
// ════════════════════════════════════════════════
function makeIbadahIcon(jenis) {
  const cfg = JENIS_CONFIG[jenis] || JENIS_CONFIG['Lainnya'];
  return L.divIcon({
    html: `<div class="icon-ibadah" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,.9))">${cfg.emoji}</div>`,
    className: '', iconSize: [32,32], iconAnchor: [16,28], popupAnchor: [0,-30]
  });
}
function makeRumahIcon(status, highlighted) {
  let color = status === 'miskin' ? (highlighted ? '#68d391' : '#fc8181') : '#f6e05e';
  return { radius: 7, fillColor: color, color: '#fff', weight: 1.5, opacity: 1, fillOpacity: 0.9 };
}

// ════════════════════════════════════════════════
// LOAD & RENDER
// ════════════════════════════════════════════════
function loadAll() {
  Promise.all([
    fetch('ambil.php').then(r => r.json()),
    fetch('ambil_rumah.php').then(r => r.json()).catch(() => [])
  ]).then(([ibadah, rumah]) => {
    ibadahData = ibadah;
    rumahData  = rumah;
    renderAll();
  });
}

function renderAll() {
  JENIS_LIST.forEach(j => layerGroups[j].clearLayers());
  layerRumahMiskin.clearLayers();
  layerRumahTidakMiskin.clearLayers();
  ibadahMarkers = {}; rumahMarkers = {}; highlightedRumah = [];

  ibadahData.forEach(addIbadahMarker);
  rumahData.forEach(r => addRumahMarker(r, false));

  updateStats();
  buildLayerPanel();
}

function addIbadahMarker(item) {
  const marker = L.marker([item.lat, item.lng], { icon: makeIbadahIcon(item.jenis) });
  marker.bindPopup(buildIbadahPopup(item), { maxWidth: 260 });
  marker.on('click', () => selectIbadah(item.id));
  layerGroups[item.jenis] = layerGroups[item.jenis] || L.layerGroup().addTo(map);
  layerGroups[item.jenis].addLayer(marker);
  ibadahMarkers[item.id] = { marker };
}

function buildIbadahPopup(item) {
  const cfg = JENIS_CONFIG[item.jenis] || JENIS_CONFIG['Lainnya'];
  let actions = `<button class="popup-btn btn-focus" onclick="selectIbadah(${item.id})">🎯 Fokus</button>`;
  if (IS_ADMIN) {
    actions += `<button class="popup-btn btn-edit" onclick="editIbadah(${item.id})">✏️ Edit</button>`;
    actions += `<button class="popup-btn btn-del"  onclick="hapusIbadah(${item.id})">🗑️</button>`;
  }
  return `
    <div class="popup-title">${cfg.emoji} ${item.jenis}${item.nama ? ' – ' + item.nama : ''}</div>
    <div class="popup-row">📍 ${item.alamat || '-'}</div>
    <div class="popup-row">📏 Radius: <b>${item.radius} m</b></div>
    <div class="popup-actions">${actions}</div>`;
}

function addRumahMarker(r, highlighted) {
  const opts = makeRumahIcon(r.status, highlighted);
  const cm = L.circleMarker([r.lat, r.lng], opts);
  cm.bindPopup(buildRumahPopup(r), { maxWidth: 280 });

  if (r.status === 'miskin') layerRumahMiskin.addLayer(cm);
  else                        layerRumahTidakMiskin.addLayer(cm);
  rumahMarkers[r.id] = cm;
}

function buildRumahPopup(r) {
  const statusColor = r.status === 'miskin' ? '#fc8181' : '#f6e05e';
  const statusLabel = r.status === 'miskin' ? 'Miskin' : 'Tidak Miskin';

  let rows = '';
  if (IS_ADMIN) {
    // Admin: tampilkan data lengkap
    rows = `
      <div class="popup-row">🪪 NIK: <b>${r.nik || '-'}</b></div>
      <div class="popup-row">👤 Nama: <b>${r.nama || '-'}</b></div>
      <div class="popup-row">📍 ${r.alamat || '-'}</div>
      <div class="popup-row">🎂 TTL: ${r.ttl || '-'}</div>
      <div class="popup-row">🎓 Pendidikan: ${r.pendidikan || '-'}</div>
      <div class="popup-row">Status: <b style="color:${statusColor}">${statusLabel}</b></div>`;
  } else {
    // Guest: NIK, nama, alamat, status
    rows = `
      <div class="popup-row">🪪 NIK: <b>${r.nik || '-'}</b></div>
      <div class="popup-row">👤 ${r.nama || '-'}</div>
      <div class="popup-row">📍 ${r.alamat || '-'}</div>
      <div class="popup-row">Status: <b style="color:${statusColor}">${statusLabel}</b></div>`;
  }

  let actions = '';
  if (IS_ADMIN) {
    actions = `
      <div class="popup-actions">
        <button class="popup-btn btn-edit" onclick="editRumah(${r.id})">✏️ Edit</button>
        <button class="popup-btn btn-del"  onclick="hapusRumah(${r.id})">🗑️</button>
      </div>`;
  }

  return `<div class="popup-title">🏠 Rumah</div>${rows}${actions}`;
}

// ════════════════════════════════════════════════
// SELECT IBADAH
// ════════════════════════════════════════════════
function selectIbadah(id) {
  if (activeCircle) { map.removeLayer(activeCircle); activeCircle = null; }
  resetRumahColors();

  const item = ibadahData.find(d => d.id == id);
  if (!item) return;
  selectedIbadahId = id;

  const cfg = JENIS_CONFIG[item.jenis] || JENIS_CONFIG['Lainnya'];
  activeCircle = L.circle([item.lat, item.lng], {
    radius: item.radius, color: cfg.color, fillColor: cfg.color,
    fillOpacity: 0.12, weight: 2, dashArray: '6,4'
  }).addTo(map);

  map.flyTo([item.lat, item.lng], Math.max(map.getZoom(), 14), { duration: 0.8 });

  const panel = document.getElementById('selected-info');
  panel.classList.add('visible');
  document.getElementById('selected-name').textContent = cfg.emoji + ' ' + item.jenis + (item.nama ? ' – ' + item.nama : '');
  document.getElementById('selected-alamat').textContent = item.alamat || '';
  document.getElementById('radius-display').textContent = item.radius + ' m';
  document.getElementById('radius-slider').value = item.radius;

  highlightRumahInRadius(item);
}

function highlightRumahInRadius(item) {
  highlightedRumah = [];
  const inRadius = [];

  rumahData.forEach(r => {
    const dist = getDistanceMeters(item.lat, item.lng, r.lat, r.lng);
    if (dist <= item.radius) {
      highlightedRumah.push(r.id);
      if (r.status === 'miskin') inRadius.push({ ...r, dist: Math.round(dist) });
      if (rumahMarkers[r.id]) rumahMarkers[r.id].setStyle(makeRumahIcon(r.status, true));
    }
  });

  document.getElementById('rumah-count').textContent = highlightedRumah.length;

  const listEl = document.getElementById('rumah-in-radius');
  if (inRadius.length === 0) {
    listEl.innerHTML = '<div style="font-size:12px;color:#718096;padding:6px 0;">Tidak ada rumah miskin dalam radius.</div>';
  } else {
    listEl.innerHTML = inRadius.map(r =>
      `<div class="rumah-item">
        <div class="dot dot-aman"></div>
        <div>${(r.nama || r.alamat || 'Rumah #' + r.id).substring(0,40)} <span style="color:#4a5568">(${r.dist} m)</span></div>
      </div>`
    ).join('');
  }
  updateStats();
}

function resetRumahColors() {
  highlightedRumah.forEach(id => {
    const r = rumahData.find(x => x.id == id);
    if (r && rumahMarkers[id]) rumahMarkers[id].setStyle(makeRumahIcon(r.status, false));
  });
  highlightedRumah = [];
}

// ════════════════════════════════════════════════
// SLIDER
// ════════════════════════════════════════════════
function onSliderMove(val) {
  document.getElementById('radius-display').textContent = val + ' m';
  if (activeCircle) activeCircle.setRadius(parseInt(val));
  if (selectedIbadahId !== null) {
    resetRumahColors();
    const item = ibadahData.find(d => d.id == selectedIbadahId);
    if (item) highlightRumahInRadius({ ...item, radius: parseInt(val) });
  }
}

function saveRadius() {
  if (selectedIbadahId === null) return;
  const newRadius = parseInt(document.getElementById('radius-slider').value);

  // Guest hanya update tampilan lokal, tidak simpan ke DB
  if (!IS_ADMIN) {
    const item = ibadahData.find(d => d.id == selectedIbadahId);
    if (item) {
      item.radius = newRadius;
      if (ibadahMarkers[selectedIbadahId])
        ibadahMarkers[selectedIbadahId].marker.setPopupContent(buildIbadahPopup(item));
    }
    showToast('👁 Radius diubah (tidak disimpan — hanya tampilan)');
    return;
  }

  fetch('edit.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${selectedIbadahId}&radius=${newRadius}&_only_radius=1`
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') {
      const item = ibadahData.find(d => d.id == selectedIbadahId);
      if (item) {
        item.radius = newRadius;
        if (ibadahMarkers[selectedIbadahId])
          ibadahMarkers[selectedIbadahId].marker.setPopupContent(buildIbadahPopup(item));
      }
      showToast('✅ Radius berhasil disimpan');
    } else {
      showToast('❌ Gagal menyimpan radius');
    }
  });
}

// ════════════════════════════════════════════════
// MAP CLICK → ADD MARKER (admin only)
// ════════════════════════════════════════════════
map.on('click', async function(e) {
  if (!IS_ADMIN || mode === 'view') return;

  const lat = e.latlng.lat;
  const lng = e.latlng.lng;
  const alamat = await getAddress(lat, lng);

  if (mode === 'ibadah') {
    const jenis = await showJenisDialog();
    if (!jenis) return;
    const nama = prompt(`Nama ${jenis} (opsional):`) || '';
    let radius = prompt('Masukkan radius awal (meter):', 300);
    radius = parseInt(radius) || 300;

    fetch('simpan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `jenis=${encodeURIComponent(jenis)}&nama=${encodeURIComponent(nama)}&lat=${lat}&lng=${lng}&radius=${radius}&alamat=${encodeURIComponent(alamat)}`
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') { showToast('✅ Tempat ibadah disimpan!'); loadAll(); }
      else showToast('❌ Gagal simpan: ' + (res.message || ''));
    });

  } else if (mode === 'rumah') {
    showFormRumah(lat, lng, alamat);
  }
});

// ════════════════════════════════════════════════
// FORM RUMAH (tambah data lengkap)
// ════════════════════════════════════════════════
function showFormRumah(lat, lng, alamat, existingData) {
  const isEdit = !!existingData;
  const d = existingData || {};

  const overlay = document.createElement('div');
  overlay.className = 'dialog-overlay';
  overlay.innerHTML = `
    <div class="dialog-box">
      <div class="dialog-title">${isEdit ? '✏️ Edit Data Rumah' : '🏠 Tambah Data Rumah'}</div>

      <div class="form-row">
        <label>NIK *</label>
        <input type="text" id="frm-nik" placeholder="16 digit NIK" maxlength="16" value="${d.nik || ''}">
      </div>
      <div class="form-row">
        <label>Nama Kepala Keluarga *</label>
        <input type="text" id="frm-nama" placeholder="Nama lengkap" value="${d.nama || ''}">
      </div>
      <div class="form-row">
        <label>Alamat</label>
        <textarea id="frm-alamat" rows="2">${d.alamat || alamat || ''}</textarea>
        <div class="hint">💡 Terisi otomatis dari titik yang diklik. Bisa diedit manual.</div>
      </div>
      <div class="form-row">
        <label>Tempat, Tanggal Lahir</label>
        <input type="text" id="frm-ttl" placeholder="Contoh: Pontianak, 01 Januari 1990" value="${d.ttl || ''}">
      </div>
      <div class="form-row">
        <label>Pendidikan Terakhir</label>
        <select id="frm-pendidikan">
          ${['Tidak Sekolah','SD/Sederajat','SMP/Sederajat','SMA/Sederajat','D3','S1','S2/S3'].map(p =>
            `<option value="${p}" ${(d.pendidikan||'')==p?'selected':''}>${p}</option>`
          ).join('')}
        </select>
      </div>
      <div class="form-row">
        <label>Status Kemiskinan</label>
        <div style="display:flex;gap:8px;margin-top:4px;">
          <button id="frm-btn-miskin" onclick="setStatusBtn('miskin')"
            style="flex:1;padding:9px;border-radius:7px;border:1px solid #c53030;cursor:pointer;font-size:12px;font-weight:700;transition:.2s;
            background:${(!d.status||d.status==='miskin')?'#742a2a':'#0f1117'};color:${(!d.status||d.status==='miskin')?'#fc8181':'#718096'}">
            🔴 Miskin
          </button>
          <button id="frm-btn-tidak" onclick="setStatusBtn('tidak_miskin')"
            style="flex:1;padding:9px;border-radius:7px;border:1px solid #276749;cursor:pointer;font-size:12px;font-weight:700;transition:.2s;
            background:${d.status==='tidak_miskin'?'#1c4532':'#0f1117'};color:${d.status==='tidak_miskin'?'#68d391':'#718096'}">
            🟡 Tidak Miskin
          </button>
        </div>
      </div>
      <input type="hidden" id="frm-status" value="${d.status || 'miskin'}">

      <div class="dialog-actions">
        <button class="btn-cancel" onclick="this.closest('.dialog-overlay').remove()">Batal</button>
        <button class="btn-primary" onclick="submitFormRumah(${lat}, ${lng}, ${isEdit ? d.id : 'null'})">
          ${isEdit ? '💾 Simpan Perubahan' : '📍 Simpan Rumah'}
        </button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
}

function setStatusBtn(val) {
  document.getElementById('frm-status').value = val;
  const bm = document.getElementById('frm-btn-miskin');
  const bt = document.getElementById('frm-btn-tidak');
  if (val === 'miskin') {
    bm.style.background = '#742a2a'; bm.style.color = '#fc8181';
    bt.style.background = '#0f1117'; bt.style.color = '#718096';
  } else {
    bt.style.background = '#1c4532'; bt.style.color = '#68d391';
    bm.style.background = '#0f1117'; bm.style.color = '#718096';
  }
}

function submitFormRumah(lat, lng, editId) {
  const nik        = document.getElementById('frm-nik').value.trim();
  const nama       = document.getElementById('frm-nama').value.trim();
  const alamat     = document.getElementById('frm-alamat').value.trim();
  const ttl        = document.getElementById('frm-ttl').value.trim();
  const pendidikan = document.getElementById('frm-pendidikan').value;
  const status     = document.getElementById('frm-status').value;

  if (!nik || !nama) { showToast('⚠️ NIK dan Nama wajib diisi!'); return; }

  const isEdit = editId !== null;
  const url = isEdit ? 'edit_rumah.php' : 'simpan_rumah.php';
  let body = `nik=${encodeURIComponent(nik)}&nama=${encodeURIComponent(nama)}&alamat=${encodeURIComponent(alamat)}&ttl=${encodeURIComponent(ttl)}&pendidikan=${encodeURIComponent(pendidikan)}&status=${status}`;
  if (isEdit) body += `&id=${editId}`;
  else        body += `&lat=${lat}&lng=${lng}`;

  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') {
      showToast(isEdit ? '✅ Data rumah diperbarui!' : '✅ Rumah disimpan!');
      document.querySelector('.dialog-overlay')?.remove();
      loadAll();
    } else {
      showToast('❌ Gagal: ' + (res.message || ''));
    }
  });
}

// ════════════════════════════════════════════════
// EDIT / HAPUS IBADAH
// ════════════════════════════════════════════════
function editIbadah(id) {
  const item = ibadahData.find(d => d.id == id);
  if (!item) return;
  const jenisBaru = prompt('Edit jenis:', item.jenis);
  if (!jenisBaru) return;
  const namaBaru = prompt('Edit nama:', item.nama || '');
  let radiusBaru = prompt('Edit radius:', item.radius);
  radiusBaru = parseInt(radiusBaru) || item.radius;

  fetch('edit.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${id}&jenis=${encodeURIComponent(jenisBaru)}&nama=${encodeURIComponent(namaBaru)}&radius=${radiusBaru}`
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') { showToast('✅ Data diupdate!'); loadAll(); }
  });
}

function hapusIbadah(id) {
  if (!confirm('Yakin hapus tempat ibadah ini?')) return;
  fetch('hapus.php', {
    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${id}`
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') {
      showToast('🗑️ Data dihapus!');
      if (activeCircle) { map.removeLayer(activeCircle); activeCircle = null; }
      document.getElementById('selected-info').classList.remove('visible');
      selectedIbadahId = null;
      loadAll();
    }
  });
}

// ════════════════════════════════════════════════
// EDIT / HAPUS RUMAH
// ════════════════════════════════════════════════
function editRumah(id) {
  const r = rumahData.find(x => x.id == id);
  if (!r) return;
  document.querySelector('.leaflet-popup-close-button')?.click(); // tutup popup
  showFormRumah(r.lat, r.lng, r.alamat, r);
}

function hapusRumah(id) {
  if (!confirm('Hapus data rumah ini?')) return;
  fetch('hapus_rumah.php', {
    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${id}`
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success') { showToast('🗑️ Rumah dihapus!'); loadAll(); }
  });
}

// ════════════════════════════════════════════════
// DIALOG JENIS IBADAH
// ════════════════════════════════════════════════
function showJenisDialog() {
  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'dialog-overlay';
    overlay.innerHTML = `
      <div class="dialog-box" style="min-width:280px;max-width:320px;">
        <div class="dialog-title">Pilih Jenis Tempat Ibadah</div>
        ${JENIS_LIST.map(j => `
          <button data-jenis="${j}"
            style="display:block;width:100%;margin-bottom:8px;padding:9px 14px;background:#2d3748;border:1px solid #4a5568;
                   border-radius:7px;color:#e2e8f0;font-size:13px;cursor:pointer;text-align:left;">
            ${JENIS_CONFIG[j].emoji} ${j}
          </button>`).join('')}
        <button data-jenis=""
          style="width:100%;padding:7px;background:transparent;border:1px solid #4a5568;border-radius:7px;color:#718096;font-size:12px;cursor:pointer;margin-top:2px;">
          Batal
        </button>
      </div>`;
    overlay.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        resolve(btn.dataset.jenis || null);
        document.body.removeChild(overlay);
      });
    });
    document.body.appendChild(overlay);
  });
}

// ════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════
function getAddress(lat, lng) {
  return fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
    .then(r => r.json())
    .then(d => d.display_name || 'Alamat tidak ditemukan')
    .catch(() => 'Gagal ambil alamat');
}

function getDistanceMeters(lat1, lng1, lat2, lng2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function updateStats() {
  document.getElementById('stat-ibadah').textContent = ibadahData.length;
  document.getElementById('stat-rumah').textContent  = rumahData.length;
  const miskin = rumahData.filter(r => r.status === 'miskin');
  document.getElementById('stat-miskin').textContent   = miskin.length;
  document.getElementById('count-miskin').textContent  = miskin.length;
  document.getElementById('count-tidak').textContent   = rumahData.filter(r => r.status !== 'miskin').length;

  const terjangkau = new Set();
  ibadahData.forEach(item => {
    miskin.forEach(r => {
      if (getDistanceMeters(item.lat, item.lng, r.lat, r.lng) <= item.radius) terjangkau.add(r.id);
    });
  });
  document.getElementById('stat-terjangkau').textContent = terjangkau.size;
}

let toastTimer;
function showToast(msg) {
  let t = document.getElementById('toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast';
    t.style.cssText = `position:fixed;bottom:20px;left:50%;transform:translateX(-50%);
      background:#1a202c;border:1px solid #2d3748;color:#e2e8f0;
      padding:9px 18px;border-radius:20px;font-size:13px;
      z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.4);transition:opacity .3s;`;
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.style.opacity = '1';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.style.opacity = '0'; }, 2500);
}

// ════════════════════════════════════════════════
// INIT
// ════════════════════════════════════════════════
setMode(IS_ADMIN ? 'ibadah' : 'view');
loadAll();
</script>
</body>
</html>