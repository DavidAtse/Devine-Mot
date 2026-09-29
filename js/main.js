"use strict";

// ======================
// RÉFÉRENCES DOM
// ======================
const resultsBody = document.getElementById("resultsBody");
const input       = document.getElementById("guessInput");
const bouton      = document.getElementById("guessBtn");
const message     = document.getElementById("message");
const countdownEl = document.getElementById("countdown");
const tuilesEl    = document.getElementById("tuiles");

// ======================
// STOCKAGE PAR UTILISATEUR
// ======================
const username        = window.username || "guest";
const KEY_HISTORIQUE  = `mdj_v3_hist_${username}`;
const KEY_DATE        = `mdj_v3_date_${username}`;
const KEY_CONFIRMES   = `mdj_v3_conf_${username}`; // lettres confirmées {pos: lettre}
const KEY_LONGUEUR    = `mdj_v3_len_${username}`;  // longueur du mot du jour
const KEY_DEFINITION  = `mdj_v3_def_${username}`;  // définition du mot trouvé

// ======================
// RESET QUOTIDIEN
// ======================
const today    = new Date().toISOString().split("T")[0];
const lastDate = localStorage.getItem(KEY_DATE);

if (lastDate !== today) {
    [KEY_HISTORIQUE, KEY_CONFIRMES, KEY_LONGUEUR, KEY_DEFINITION].forEach(k => localStorage.removeItem(k));
    localStorage.setItem(KEY_DATE, today);
}

// ======================
// MESSAGES
// ======================
function showMsg(texte, couleur = "orange") {
    message.innerHTML   = texte;
    message.style.color = couleur;
    message.style.fontSize   = "";
    message.style.fontWeight = "";
}
function clearMsg() { message.innerHTML = ""; }

// ======================
// TUILES D'INDICES
// Affiche N cases avec les lettres confirmées en vert et les cases vides en gris
// ======================
function renderTuiles(longueur, confirmes = {}) {
    if (!longueur || !tuilesEl) return;
    tuilesEl.innerHTML = "";
    tuilesEl.style.setProperty("--n", longueur);

    for (let i = 0; i < longueur; i++) {
        const div = document.createElement("div");
        div.className = "tuile" + (confirmes[i] ? " tuile-ok" : "");
        div.textContent = confirmes[i] || "";
        tuilesEl.appendChild(div);
    }
}

function chargerTuiles() {
    const lon  = parseInt(localStorage.getItem(KEY_LONGUEUR) || "0", 10);
    const conf = _chargerConfirmes();
    if (lon) renderTuiles(lon, conf);
}

function mettreAJourTuiles(longueur, positions, motPropose) {
    localStorage.setItem(KEY_LONGUEUR, longueur);
    const lettres  = [...motPropose];
    const confirmes = _chargerConfirmes();
    positions.forEach((ok, i) => {
        if (ok && lettres[i]) confirmes[i] = lettres[i];
    });
    localStorage.setItem(KEY_CONFIRMES, JSON.stringify(confirmes));
    renderTuiles(longueur, confirmes);
}

function _chargerConfirmes() {
    try { return JSON.parse(localStorage.getItem(KEY_CONFIRMES) || "{}"); }
    catch { return {}; }
}

// ======================
// DÉFINITION DU MOT GAGNÉ
// ======================
function _chargerDefinition() {
    try { return JSON.parse(localStorage.getItem(KEY_DEFINITION) || 'null'); }
    catch { return null; }
}

function _sauvegarderDefinition(mot, definition) {
    localStorage.setItem(KEY_DEFINITION, JSON.stringify({ mot, definition }));
}

function _montrerBoutonDef(visible) {
    const btn = document.getElementById('defBtn');
    if (btn) btn.style.display = visible ? 'block' : 'none';
}

function afficherDefinition(mot, definition) {
    const modal = document.getElementById('definitionModal');
    if (!modal) return;

    const titre  = document.getElementById('defMotTitre');
    const corps  = document.getElementById('defContenu');
    if (titre) titre.textContent = mot;

    if (corps) {
        corps.innerHTML = '';
        const p = document.createElement('p');
        if (definition && definition.trim()) {
            p.className   = 'def-texte';
            p.textContent = definition.trim();
        } else {
            p.className   = 'def-vide';
            p.textContent = 'Pas encore de définition pour ce mot. L\'administrateur peut en ajouter une via le panel admin.';
        }
        corps.appendChild(p);
    }
    modal.style.display = 'flex';
}

function ouvrirDefinition() {
    const data = _chargerDefinition();
    if (data) afficherDefinition(data.mot, data.definition);
}

// ======================
// HISTORIQUE LOCALSTORAGE
// Format : [{mot, positions, score, emoji}]
// ======================
function chargerHistorique() {
    try { return JSON.parse(localStorage.getItem(KEY_HISTORIQUE) || "[]"); }
    catch { return []; }
}

function sauvegarder(mot, positions, score, emoji) {
    const hist = chargerHistorique();
    hist.push({ mot, positions, score, emoji });
    localStorage.setItem(KEY_HISTORIQUE, JSON.stringify(hist));
}

// ======================
// AFFICHAGE TABLEAU
// ======================
function colorerMot(mot, positions) {
    return [...mot].map((c, i) =>
        positions[i] ? `<span class="lettre-ok">${c}</span>` : c
    ).join("");
}

function ajouterLigne(mot, positions, score, emoji, animate = true) {
    const rang  = resultsBody.querySelectorAll("tr").length + 1;
    const tr    = document.createElement("tr");
    tr.dataset.score = score;
    if (animate) tr.classList.add("row-animate");
    tr.innerHTML = `
        <td class="col-n">${rang}</td>
        <td class="col-mot">${colorerMot(mot, positions)}</td>
        <td class="col-emoji">${emoji}</td>
        <td class="col-score">${parseFloat(score).toFixed(2)}%</td>
    `;
    resultsBody.appendChild(tr);
    trierTableau();
}

function trierTableau() {
    const lignes = [...resultsBody.querySelectorAll("tr")];
    lignes.sort((a, b) => parseFloat(b.dataset.score) - parseFloat(a.dataset.score));
    lignes.forEach((tr, i) => {
        tr.querySelector(".col-n").textContent = i + 1;
        resultsBody.appendChild(tr);
    });
}

// ======================
// RECONSTRUCTION AU RECHARGEMENT
// ======================
function reconstruireTableau() {
    const hist = chargerHistorique();
    resultsBody.innerHTML = "";
    let gagne = false;

    hist.forEach(item => {
        ajouterLigne(item.mot, item.positions, item.score, item.emoji, false);
        if (parseFloat(item.score) >= 100) gagne = true;
    });

    if (gagne) bloquerJeu(hist.length);
}

// ======================
// INITIALISATION
// ======================
async function init() {
    reconstruireTableau();
    chargerTuiles();

    // Si la partie est déjà gagnée aujourd'hui (localStorage), afficher le bouton définition
    const hist  = chargerHistorique();
    const gagne = hist.some(i => parseFloat(i.score) >= 100);
    if (gagne) {
        const defData = _chargerDefinition();
        _montrerBoutonDef(!!defData);
    }

    // Appel serveur : récupère la longueur ET vérifie si déjà gagné (sync multi-appareils)
    try {
        const res  = await fetch("php/jouer.php", { credentials: "same-origin" });
        if (res.ok) {
            const data = await res.json();

            if (data.longueur) {
                localStorage.setItem(KEY_LONGUEUR, data.longueur);
                renderTuiles(data.longueur, _chargerConfirmes());
            }

            // SYNC MULTI-APPAREILS : si le serveur dit que l'utilisateur a déjà gagné
            // mais que le localStorage de cet appareil ne le sait pas encore
            if (data.deja_gagne && !gagne) {
                // Bloquer le jeu proprement
                bloquerJeu(data.tentatives || 1);
                // Sauvegarder la définition localement pour ce device
                const motGagne = ""; // on n'a pas le mot côté client (sécurité), on affiche juste la définition
                _sauvegarderDefinition(motGagne, data.definition || '');
                _montrerBoutonDef(true);
            }
        }
    } catch (e) {
        console.warn("Init serveur indisponible :", e);
    }
}

init();

// ======================
// BOUTON VALIDER
// ======================
bouton.addEventListener("click", async () => {
    const motPropose = [...input.value].map(c => c.toUpperCase()).join("").trim();

    if (!motPropose) {
        showMsg("⚠️ Entre un mot pour jouer.", "orange");
        return;
    }

    if (!/^[A-ZÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,30}$/u.test(motPropose)) {
        showMsg("⚠️ Lettres uniquement (2–30 caractères).", "orange");
        return;
    }

    const hist = chargerHistorique();
    if (hist.some(i => i.mot === motPropose)) {
        showMsg("⚠️ Mot déjà proposé.", "orange");
        return;
    }

    const numEssai = hist.length + 1;

    bouton.disabled    = true;
    bouton.textContent = "…";
    clearMsg();

    try {
        const res = await fetch("php/jouer.php", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-Token": window.csrfToken
            },
            body: `mot=${encodeURIComponent(motPropose)}&tentatives=${numEssai}`
        });

        if (res.status === 401) { window.location.href = "inscription/login.php"; return; }

        const data = await res.json();

        if (!res.ok || data.erreur) {
            showMsg("⚠️ " + (data.erreur || "Erreur serveur."), "red");
            return;
        }

        if (!data.ok) {
            showMsg(data.message || "❌ Mot inconnu.", "#ff6b6b");
            return;
        }

        // Succès
        input.value = "";
        input.focus();
        ajouterLigne(motPropose, data.positions, data.score, data.emoji, true);
        sauvegarder(motPropose, data.positions, data.score, data.emoji);
        mettreAJourTuiles(data.longueurMDJ, data.positions, motPropose);

        if (data.gagne) {
            bloquerJeu(numEssai);
            animationVictoire();
            // Sauvegarder la définition et l'afficher après les confettis
            _sauvegarderDefinition(motPropose, data.definition || '');
            _montrerBoutonDef(true);
            setTimeout(() => afficherDefinition(motPropose, data.definition || ''), 2500);
        } else {
            clearMsg();
        }

    } catch (err) {
        console.error(err);
        showMsg("⚠️ Impossible de joindre le serveur.", "red");
    } finally {
        if (!input.disabled) {
            bouton.disabled    = false;
            bouton.textContent = "Valider";
        }
    }
});

input.addEventListener("keydown", e => { if (e.key === "Enter") bouton.click(); });

// ======================
// FIN DE PARTIE
// ======================
function bloquerJeu(nbEssais) {
    input.disabled  = true;
    bouton.disabled = true;
    bouton.textContent = "Valider";
    showMsg(
        `🎉 Bravo ! Trouvé en ${nbEssais} essai${nbEssais > 1 ? "s" : ""} ! Reviens demain 🇨🇮`,
        "#22c55e"
    );
    message.style.fontSize   = "16px";
    message.style.fontWeight = "bold";
}

function animationVictoire() {
    const items = ["🎉", "🎊", "✨", "🥳", "🇨🇮", "🔥"];
    for (let i = 0; i < 50; i++) {
        const el = document.createElement("span");
        el.textContent = items[Math.floor(Math.random() * items.length)];
        const startX   = Math.random() * 100;
        const size     = Math.random() * 20 + 18;
        const duration = Math.random() * 2 + 2;
        const delay    = Math.random() * 1.5;
        el.style.cssText = `
            position:fixed; pointer-events:none; z-index:9999;
            left:${startX}vw; top:-60px;
            font-size:${size}px;
            animation: tomber ${duration}s ease-in ${delay}s forwards;
        `;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), (duration + delay + 0.5) * 1000);
    }
}

// ======================
// COMPTEUR MINUIT
// ======================
function updateCountdown() {
    const now = new Date();
    const minuit = new Date(); minuit.setHours(24, 0, 0, 0);
    const diff = minuit - now;
    if (diff <= 0) { countdownEl.textContent = "Nouveau mot ! 🔄"; setTimeout(() => location.reload(), 1500); return; }
    const h = Math.floor(diff / 3_600_000);
    const m = Math.floor((diff % 3_600_000) / 60_000);
    const s = Math.floor((diff % 60_000) / 1000);
    countdownEl.textContent = `Prochain mot : ${String(h).padStart(2,"0")}h ${String(m).padStart(2,"0")}m ${String(s).padStart(2,"0")}s`;
}
setInterval(updateCountdown, 1000);
updateCountdown();

// ======================
// MODALS
// ======================
document.addEventListener("DOMContentLoaded", () => {
    const openModal  = id => { const m = document.getElementById(id); if (m) m.style.display = "flex"; };
    const closeModal = m  => { if (m) m.style.display = "none"; };

    document.getElementById("openRules")?.addEventListener("click", e => { e.preventDefault(); openModal("rulesModal"); });

    document.querySelectorAll(".close").forEach(btn => {
        btn.addEventListener("click", () => closeModal(document.getElementById(btn.dataset.close)));
    });
    document.querySelectorAll(".modal").forEach(m => {
        m.addEventListener("click", e => { if (e.target === m) closeModal(m); });
    });
});

// ======================
// PARTAGE
// ======================
function partagerScore() {
    const hist  = chargerHistorique();
    const nb    = hist.length;
    const gagne = hist.some(i => parseFloat(i.score) >= 100);
    const txt   = gagne
        ? `🥳 Mot du Jour CI — Trouvé en ${nb} essai${nb > 1 ? "s" : ""} ! Joue sur devine-mot.ci 🇨🇮`
        : `😅 Mot du Jour CI — Non trouvé après ${nb} essai${nb > 1 ? "s" : ""}. Tente ta chance ! 🇨🇮`;
    window.open("https://wa.me/?text=" + encodeURIComponent(txt), "_blank");
}

// ======================
// PWA — SERVICE WORKER + PUSH NOTIFICATIONS
// ======================

/* Enregistrement du Service Worker */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const reg = await navigator.serviceWorker.register(window.BASE_PATH + '/sw.js', {
                scope: window.BASE_PATH + '/'
            });
            window._swReg = reg;
            _initNotifButton(reg);
            /* Lazy push trigger : envoie les notifs du jour si pas encore fait */
            _triggerDailyPush();
        } catch (err) {
            console.warn('SW registration failed:', err);
        }
    });
}

/* Affiche le bouton notification selon l'état de permission */
function _initNotifButton(reg) {
    const btn = document.getElementById('notifBtn');
    if (!btn || !('PushManager' in window)) return;
    btn.style.display = 'block';
    _updateNotifButton();
}

async function _updateNotifButton() {
    const btn = document.getElementById('notifBtn');
    if (!btn) return;
    const perm = Notification.permission;
    if (perm === 'granted') {
        const reg = window._swReg;
        const sub = reg ? await reg.pushManager.getSubscription() : null;
        if (sub) {
            btn.textContent = '🔕 Désactiver les rappels';
            btn.style.background = 'rgba(255,80,80,0.1)';
            btn.style.borderColor = 'rgba(255,80,80,0.35)';
            btn.style.color = '#ff5050';
        } else {
            btn.textContent = '🔔 Activer les rappels quotidiens';
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '#F77F00';
        }
    } else if (perm === 'denied') {
        btn.textContent = '🚫 Notifications bloquées';
        btn.disabled = true;
        btn.style.opacity = '0.5';
    } else {
        btn.textContent = '🔔 Activer les rappels quotidiens';
    }
}

async function toggleNotification() {
    if (!('PushManager' in window) || !window._swReg) return;

    const reg = window._swReg;
    const existing = await reg.pushManager.getSubscription();

    if (existing) {
        /* Désabonnement */
        await existing.unsubscribe();
        await fetch(window.BASE_PATH + '/php/push-subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken },
            body: JSON.stringify({ action: 'unsubscribe', subscription: { endpoint: existing.endpoint } })
        });
        _updateNotifButton();
        return;
    }

    /* Demande permission */
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        _updateNotifButton();
        return;
    }

    /* Abonnement push */
    try {
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: _urlBase64ToUint8Array(window.vapidKey)
        });
        const subJson = sub.toJSON();
        await fetch(window.BASE_PATH + '/php/push-subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken },
            body: JSON.stringify({ action: 'subscribe', subscription: subJson })
        });
        _updateNotifButton();
    } catch (err) {
        console.error('Push subscribe error:', err);
    }
}

/* Déclenche l'envoi des notifs du jour côté serveur (une fois par jour) */
async function _triggerDailyPush() {
    const todayKey = 'mdj_push_triggered_' + new Date().toISOString().split('T')[0];
    if (sessionStorage.getItem(todayKey)) return;
    sessionStorage.setItem(todayKey, '1');
    try {
        await fetch(window.BASE_PATH + '/php/push-send.php', { credentials: 'same-origin' });
    } catch (_) {}
}

/* Utilitaire : convertit la clé VAPID base64url → Uint8Array */
function _urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

// ======================
// MODAL DONATION & PAIEMENT SIMUL�
// ======================
function ouvrirDonate() {
    const modal = document.getElementById('donateModal');
    if (modal) {
        document.getElementById('donateStep1').style.display = 'block';
        document.getElementById('donateStep2').style.display = 'none';
        document.getElementById('donateLoading').style.display = 'none';
        document.getElementById('donateSuccess').style.display = 'none';
        modal.style.display = 'flex';
    }
}

// Gestion des boutons de montants
document.querySelectorAll('.btn-amount').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.btn-amount').forEach(b => {
            b.style.background = 'transparent';
            b.style.color = 'var(--orange)';
        });
        this.style.background = 'var(--orange)';
        this.style.color = '#1a1008';
        window.selectedDonationAmount = this.dataset.amount;
    });
});

if (document.getElementById('btnProceedDonate')) {
    document.getElementById('btnProceedDonate').addEventListener('click', () => {
        const amt = window.selectedDonationAmount || '1000';
        document.getElementById('donateAmountStr').textContent = amt;
        document.getElementById('donateStep1').style.display = 'none';
        document.getElementById('donateStep2').style.display = 'block';
    });
}

// Initialisation de CinetPay
function startCinetPayPayment() {
    document.getElementById('donateStep2').style.display = 'none';
    
    // Si l'utilisateur n'a pas encore configur� ses cl�s API dans Railway
    if (window.cinetpayApiKey === 'AJOUTER_APIKEY_DANS_RAILWAY' || window.cinetpaySiteId === 'AJOUTER_SITEID_DANS_RAILWAY') {
        alert('CinetPay n\'est pas encore configur� ! Ajoute CINETPAY_APIKEY et CINETPAY_SITE_ID dans Railway.');
        window.location.reload();
        return;
    }

    const amt = parseInt(window.selectedDonationAmount || '1000');
    const transactionId = 'MDJ_DON_' + Math.floor(Math.random() * 100000000).toString();

    CinetPay.setConfig({
        apikey: window.cinetpayApiKey,
        site_id: window.cinetpaySiteId,
        notify_url: 'https://devine-mot-production.up.railway.app/',
        mode: 'PRODUCTION' // Mettre 'TEST' si test
    });

    CinetPay.getCheckout({
        transaction_id: transactionId,
        amount: amt,
        currency: 'XOF',
        channels: 'ALL',
        description: 'Soutien pour le jeu DevineMot CI',
        customer_name: window.username || 'Joueur',
        customer_surname: 'CI',
        customer_email: 'hello@devinemot.ci',
        customer_phone_number: '',
        customer_address: 'Abidjan',
        customer_city: 'Abidjan',
        customer_country: 'CI',
        customer_state: 'CI',
        customer_zip_code: '225',
    });

    CinetPay.waitResponse(function(data) {
        if (data.status == "REFUSED") {
            alert("Le paiement a �chou� ou a �t� annul�.");
            window.location.reload();
        } else if (data.status == "ACCEPTED") {
            document.getElementById('donateSuccess').style.display = 'block';
        }
    });

    CinetPay.onError(function(data) {
        console.error("Erreur CinetPay:", data);
        alert("Erreur lors de l'initialisation du paiement.");
        window.location.reload();
    });
}

if (document.getElementById('payWave')) {
    document.getElementById('payWave').addEventListener('click', startCinetPayPayment);
}
if (document.getElementById('payOrange')) {
    document.getElementById('payOrange').addEventListener('click', startCinetPayPayment);
}

// Fermeture des modales
document.querySelectorAll('.close, .close-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.close;
        if (id && document.getElementById(id)) {
            document.getElementById(id).style.display = 'none';
        } else {
            const m = this.closest('.modal') || this.closest('.modal-overlay');
            if (m) m.style.display = 'none';
        }
    });
});
