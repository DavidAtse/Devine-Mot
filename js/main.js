"use strict";

// ======================
// R�F�RENCES DOM
// ======================
const resultsBody = document.getElementById("resultsBody");
const input       = document.getElementById("guessInput");
const bouton      = document.getElementById("guessBtn");
const message     = document.getElementById("message");
const countdownEl = document.getElementById("countdown");
const tuilesEl    = document.getElementById("tuiles");

// ======================
// ======================
// STOCKAGE PAR UTILISATEUR (Lancement Officiel v1)
// ======================
const username        = window.username || "guest";
const KEY_HISTORIQUE  = `imots_v1_hist_${username}`;
const KEY_DATE        = `imots_v1_date_${username}`;
const KEY_CONFIRMES   = `imots_v1_conf_${username}`; // lettres confirmées {pos: lettre}
const KEY_LONGUEUR    = `imots_v1_len_${username}`;  // longueur du mot du jour
const KEY_DEFINITION  = `imots_v1_def_${username}`;  // définition du mot trouvé

// Nettoyage immédiat des anciennes clés de test
['mdj_v4_hist_', 'mdj_v4_date_', 'mdj_v4_conf_', 'mdj_v4_len_', 'mdj_v4_def_'].forEach(prefix => {
    try { localStorage.removeItem(prefix + username); } catch(e) {}
});

// ======================
// RESET QUOTIDIEN
// ======================
const today    = new Date().toISOString().split("T")[0];
// 4 mots par jour : créneau 0 (00h-06h), 1 (06h-12h), 2 (12h-18h), 3 (18h-00h) - heure d'Abidjan = UTC
const creneau  = (typeof window.CRENEAU === "number") ? window.CRENEAU : Math.floor(new Date().getUTCHours() / 6);
const stamp    = `${today}_${creneau}`;
const lastDate = localStorage.getItem(KEY_DATE);

if (lastDate !== stamp) {
    [KEY_HISTORIQUE, KEY_CONFIRMES, KEY_LONGUEUR, KEY_DEFINITION].forEach(k => localStorage.removeItem(k));
    localStorage.setItem(KEY_DATE, stamp);
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
// Affiche N cases avec les lettres confirm�es en vert et les cases vides en gris
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
        if (ok === 2 && lettres[i]) confirmes[i] = lettres[i];
    });
    localStorage.setItem(KEY_CONFIRMES, JSON.stringify(confirmes));
    renderTuiles(longueur, confirmes);
}

function _chargerConfirmes() {
    try { return JSON.parse(localStorage.getItem(KEY_CONFIRMES) || "{}"); }
    catch { return {}; }
}

// ======================
// D�FINITION DU MOT GAGN�
// ======================
function _chargerDefinition() {
    try { return JSON.parse(localStorage.getItem(KEY_DEFINITION) || 'null'); }
    catch { return null; }
}

function _sauvegarderDefinition(mot, definition, estPartenaire = false) {
    localStorage.setItem(KEY_DEFINITION, JSON.stringify({ mot, definition, est_partenaire: !!estPartenaire }));
    renderDefInline();
}

// Carte définition permanente : visible tant que le créneau en cours n'a pas changé
// (KEY_DEFINITION est effacée automatiquement au changement de créneau).
function renderDefInline() {
    const box = document.getElementById('defInline');
    if (!box) return;
    const data = _chargerDefinition();
    if (!data) { box.hidden = true; return; }
    const motEl   = document.getElementById('defInlineMot');
    const texteEl = document.getElementById('defInlineTexte');
    const partEl  = document.getElementById('defInlinePartenaire');
    if (motEl)   { motEl.textContent = data.mot || ''; motEl.hidden = !data.mot; }
    if (partEl)  partEl.hidden = !data.est_partenaire;
    if (texteEl) {
        texteEl.textContent = (data.definition && data.definition.trim())
            ? data.definition.trim()
            : "Pas encore de définition pour ce mot.";
        texteEl.classList.toggle('vide', !(data.definition && data.definition.trim()));
    }
    box.hidden = false;
}

function _montrerBoutonDef(visible) {
    const btn = document.getElementById('defBtn');
    if (btn) btn.style.display = visible ? 'block' : 'none';
}

function afficherDefinition(mot, definition, estPartenaire = false) {
    const modal = document.getElementById('definitionModal');
    if (!modal) return;

    const titre  = document.getElementById('defMotTitre');
    const corps  = document.getElementById('defContenu');
    if (titre) titre.textContent = mot;

    if (corps) {
        corps.innerHTML = '';
        if (estPartenaire) {
            const badgePart = document.createElement('div');
            badgePart.style.display = 'inline-flex';
            badgePart.style.alignItems = 'center';
            badgePart.style.gap = '6px';
            badgePart.style.background = 'linear-gradient(135deg, #ffd700, #ff8c00)';
            badgePart.style.color = '#110e08';
            badgePart.style.fontWeight = '800';
            badgePart.style.fontSize = '12px';
            badgePart.style.padding = '4px 12px';
            badgePart.style.borderRadius = '12px';
            badgePart.style.marginBottom = '12px';
            badgePart.style.textTransform = 'uppercase';
            badgePart.style.letterSpacing = '0.5px';
            badgePart.innerHTML = '<i class="fa-solid fa-star"></i> Mot Partenaire Officiel';
            corps.appendChild(badgePart);
        }
        const p = document.createElement('p');
        if (definition && definition.trim()) {
            p.className   = 'def-texte';
            p.textContent = definition.trim();
        } else {
            p.className   = 'def-vide';
            p.textContent = "Pas encore de définition pour ce mot. L'administrateur peut en ajouter une via le panel admin.";
        }
        corps.appendChild(p);
        
        // Ajout du bouton partager direct
        const divShare = document.createElement('div');
        divShare.style.marginTop = "20px";
        divShare.style.textAlign = "center";
        
        const btnShare = document.createElement('button');
        btnShare.className = "btn-principal";
        btnShare.style.width = "100%";
        btnShare.style.background = "#25D366"; // Couleur WhatsApp
        btnShare.style.color = "#fff";
        btnShare.innerHTML = '<i class="fa-brands fa-whatsapp"></i> Partager mon score';
        btnShare.onclick = partagerScore;
        
        const btnStats = document.createElement('a');
        btnStats.href = "dashboard/profile.php";
        btnStats.className = "btn-secondaire";
        btnStats.style.display = "block";
        btnStats.style.marginTop = "10px";
        btnStats.innerHTML = '📊 Voir mes statistiques';
        
        divShare.appendChild(btnShare);
        divShare.appendChild(btnStats);
        corps.appendChild(divShare);
    }
    modal.style.display = 'flex';
}

function ouvrirDefinition() {
    const data = _chargerDefinition();
    if (data) afficherDefinition(data.mot, data.definition, data.est_partenaire);
}

// ======================
// HISTORIQUE LOCALSTORAGE
// Format : [{mot, positions, score, emoji}]
// ======================
function chargerHistorique() {
    try { return JSON.parse(localStorage.getItem(KEY_HISTORIQUE) || "[]"); }
    catch { return []; }
}

function sauvegarder(mot, positions, score, emoji, gagne = false) {
    const hist = chargerHistorique();
    hist.push({ mot, positions, score, emoji, gagne: !!gagne });
    localStorage.setItem(KEY_HISTORIQUE, JSON.stringify(hist));
}

// ======================
// AFFICHAGE TABLEAU
// ======================
function colorerMot(mot, positions) {
    return [...mot].map((c, i) => {
        if (positions[i] === 2 || positions[i] === true) {
            return `<span class="tuile-demo vert" style="padding:2px 4px; border-radius:4px; color:#22c55e;">${c}</span>`;
        } else {
            return `<span class="tuile-demo gris" style="padding:2px 4px; border-radius:4px;">${c}</span>`;
        }
    }).join("");
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
function _estItemGagnant(item) {
    if (!item) return false;
    if (item.gagne === true) return true;
    if (parseFloat(item.score) >= 100) {
        // Pour être une vraie victoire, toutes les lettres doivent être bien placées
        if (Array.isArray(item.positions) && item.positions.length > 0) {
            return item.positions.every(p => p === 2 || p === true);
        }
        return true;
    }
    return false;
}

function reconstruireTableau() {
    const hist = chargerHistorique();
    resultsBody.innerHTML = "";
    let gagne = false;

    hist.forEach(item => {
        ajouterLigne(item.mot, item.positions, item.score, item.emoji, false);
        if (_estItemGagnant(item)) gagne = true;
    });

    if (gagne) bloquerJeu(hist.length);
}

// ======================
// INITIALISATION
// ======================
async function init() {
    reconstruireTableau();
    chargerTuiles();
    renderDefInline();

    // Si la partie est déjà gagn�e aujourd'hui (localStorage), afficher le bouton définition
    const hist  = chargerHistorique();
    const gagne = hist.some(_estItemGagnant);
    if (gagne) {
        const defData = _chargerDefinition();
        _montrerBoutonDef(!!defData);
    }

    // Appel serveur : r�cup�re la longueur ET v�rifie si déjà gagn� (sync multi-appareils)
    try {
        const res  = await fetch("php/jouer.php", { credentials: "same-origin" });
        if (res.ok) {
            const data = await res.json();

            if (data.longueur) {
                const ancienneLongueur = localStorage.getItem(KEY_LONGUEUR);
                if (ancienneLongueur && parseInt(ancienneLongueur) !== data.longueur) {
                    [KEY_HISTORIQUE, KEY_CONFIRMES, KEY_LONGUEUR, KEY_DEFINITION].forEach(k => localStorage.removeItem(k));
                    document.getElementById('resultsBody').innerHTML = '';
                }
                localStorage.setItem(KEY_LONGUEUR, data.longueur);
                renderTuiles(data.longueur, _chargerConfirmes());
            }

            // SYNC MULTI-APPAREILS : si le serveur dit que l'utilisateur a déjà gagn�
            // mais que le localStorage de cet appareil ne le sait pas encore
            if (data.deja_gagne && !gagne) {
                // Remplir la grille avec le mot gagnant si on a changé d'appareil/PWA
                if (data.mot) {
                    const positions = Array(data.longueur).fill(2);
                    sauvegarder(data.mot, positions, "100.00", "🔥");
                    ajouterLigne(data.mot, positions, "100.00", "🔥");
                    mettreAJourTuiles(data.longueur, positions, data.mot);
                }
                if (data.definition) {
                    _sauvegarderDefinition(data.mot || '', data.definition, data.est_partenaire);
                    _montrerBoutonDef(true);
                }
                // Bloquer le jeu proprement
                bloquerJeu(data.tentatives || 1);
                // Sauvegarder la définition localement pour ce device
                const motGagne = data.mot || ""; // on n'a pas le mot côté client (s�curit�), on affiche juste la définition
                _sauvegarderDefinition(motGagne, data.definition || '', data.est_partenaire);
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
        showMsg("?? Entre un mot pour jouer.", "orange");
        return;
    }

    if (!/^[A-Z���������������]{2,30}$/u.test(motPropose)) {
        showMsg("⚠️ Lettres uniquement (2 à 30 caractères).", "orange");
        return;
    }

    const hist = chargerHistorique();
    if (hist.some(i => i.mot === motPropose)) {
        showMsg("?? Mot déjà proposé.", "orange");
        return;
    }

    const numEssai = hist.length + 1;

    bouton.disabled    = true;
    bouton.textContent = "�";
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
            showMsg("?? " + (data.erreur || "Erreur serveur."), "red");
            return;
        }

        if (!data.ok) {
            showMsg(data.message || "❌ Mot inconnu.", "#ff6b6b");
            return;
        }

        // Succ�s
        input.value = "";
        input.focus();
        ajouterLigne(motPropose, data.positions, data.score, data.emoji, true);
        sauvegarder(motPropose, data.positions, data.score, data.emoji, data.gagne);
        mettreAJourTuiles(data.longueurMDJ, data.positions, motPropose);

        if (data.gagne) {
            bloquerJeu(numEssai);
            animationVictoire();
            // Sauvegarder la définition et l'afficher apr�s les confettis
            _sauvegarderDefinition(motPropose, data.definition || '', data.est_partenaire);
            _montrerBoutonDef(true);
            setTimeout(() => afficherDefinition(motPropose, data.definition || '', data.est_partenaire), 2500);
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
    showMsg(`🎉 Bravo ! Trouvé en ${nbEssais} essai${nbEssais > 1 ? "s" : ""} ! Reviens au prochain mot 🇨🇮`, "#22c55e");
    message.style.fontSize   = "16px";
    message.style.fontWeight = "bold";
}

function animationVictoire() {
    const items = ["🎉", "🎊", "🥳", "✨", "🔥", "🤩", "🙌"];
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
// COMPTEUR PROCHAIN MOT (toutes les 6h)
// ======================
const PROCHAIN_MOT_FIN = (typeof window.PROCHAIN_MOT_DANS === "number")
    ? Date.now() + window.PROCHAIN_MOT_DANS * 1000
    : (() => { const d = new Date(); d.setUTCHours((Math.floor(d.getUTCHours() / 6) + 1) * 6, 0, 0, 0); return d.getTime(); })();

function updateCountdown() {
    const diff = PROCHAIN_MOT_FIN - Date.now();
    if (diff <= 0) { countdownEl.textContent = "Nouveau mot ! ??"; setTimeout(() => location.reload(), 1500); return; }
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
    
    // G�n�ration de la grille (Wordle style)
    let grille = "";
    hist.forEach(h => {
        if (!h.positions) return;
        h.positions.forEach(p => {
            if (p === 2 || p === true) grille += "🟩";
            else if (p === 1) grille += "🟩";
            else grille += "⬛";
        });
        grille += "\n";
    });

    const jourNode = document.querySelector(".instruction h3");
    const jourNum = jourNode ? jourNode.innerText.replace("Jour n�", "").trim() : "?";
    
    const txt = gagne
        ? `J'ai trouvé le mot ivoirien du jour sur iMots CI en ${nb} essai${nb > 1 ? "s" : ""} ! 🇨🇮

Viens tester ton vocabulaire et relève le défi ici : https://devine-mot-production.up.railway.app/`
        : `Le mot ivoirien du jour sur iMots CI est vraiment chaud ! 🇨🇮🔥

Viens tester ton vocabulaire et relève le défi ici : https://devine-mot-production.up.railway.app/`;
    
    if (navigator.share) {
        navigator.share({
            title: "iMots CI",
            text: txt
        }).catch(err => {
            window.open("https://wa.me/?text=" + encodeURIComponent(txt), "_blank");
        });
    } else {
        window.open("https://wa.me/?text=" + encodeURIComponent(txt), "_blank");
    }
}

// ======================
// PWA � SERVICE WORKER + PUSH NOTIFICATIONS
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

/* Affiche le bouton notification selon l'�tat de permission */
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
            btn.textContent = 'Désactiver les rappels';
            btn.style.background = 'rgba(255,80,80,0.1)';
            btn.style.borderColor = 'rgba(255,80,80,0.35)';
            btn.style.color = '#ff5050';
        } else {
            btn.textContent = 'Activer les rappels';
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '#F77F00';
        }
    } else if (perm === 'denied') {
        btn.textContent = '?? Notifications bloquées';
        btn.disabled = true;
        btn.style.opacity = '0.5';
    } else {
        btn.textContent = 'Activer les rappels';
    }
}

async function toggleNotification() {
    if (!('PushManager' in window) || !window._swReg) return;

    const reg = window._swReg;
    const existing = await reg.pushManager.getSubscription();

    if (existing) {
        /* D�sabonnement */
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

/* D�clenche l'envoi des notifs du jour côté serveur (une fois par jour) */
async function _triggerDailyPush() {
    const todayKey = 'mdj_push_triggered_' + new Date().toISOString().split('T')[0];
    if (sessionStorage.getItem(todayKey)) return;
    sessionStorage.setItem(todayKey, '1');
    try {
        await fetch(window.BASE_PATH + '/php/push-send.php', { credentials: 'same-origin' });
    } catch (_) {}
}

/* Utilitaire : convertit la clé VAPID base64url ? Uint8Array */
function _urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

// ======================
// MODAL DONATION & PAIEMENT SIMUL?
// ======================
// MODAL DONATION & PAIEMENT SÉCURISÉ (JEKO)
// ======================
function openPaymentLink() {
    const rawLink = window.paymentLink || window.wavePaymentLink || 'https://pay.jeko.africa/pl/de22040e-0dc4-4558-870a-d08a4ffa8f79';
    if (!rawLink || rawLink.trim() === '') {
        alert("Le lien de paiement n'est pas encore configuré !");
        return;
    }

    const amtInput = document.getElementById('customAmount');
    const amt = amtInput ? amtInput.value.trim() : '1000';

    // Affiche l'écran de succès
    const step2 = document.getElementById('donateStep2');
    const success = document.getElementById('donateSuccess');
    if (step2) step2.style.display = 'none';
    if (success) success.style.display = 'block';

    // Enregistrement du don côté serveur
    if (amt && parseInt(amt, 10) > 0) {
        fetch('php/enregistrer-don.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': window.csrfToken
            },
            body: `montant=${encodeURIComponent(amt)}&moyen=Jeko`
        }).catch(e => console.warn('Erreur don:', e));
    }

    let finalLink = rawLink;
    if (amt && parseInt(amt, 10) > 0) {
        finalLink += (finalLink.includes('?') ? '&' : '?') + 'amount=' + encodeURIComponent(amt);
    }

    window.open(finalLink, '_blank');
}

// Alias pour compatibilité
window.openWaveLink = openPaymentLink;

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

if (document.getElementById('btnProceedDonate')) {
    document.getElementById('btnProceedDonate').addEventListener('click', () => {
        const amtInput = document.getElementById('customAmount');
        const amt = amtInput ? amtInput.value.trim() : '';
        
        if (!amt || parseInt(amt, 10) < 100) {
            alert("Merci d'entrer un montant (minimum 100 FCFA) pour soutenir le jeu !");
            return;
        }

        const formattedAmt = Number(amt).toLocaleString('fr-FR');
        const strEl = document.getElementById('donateAmountStr');
        if (strEl) strEl.textContent = formattedAmt;
        
        document.getElementById('donateStep1').style.display = 'none';
        document.getElementById('donateStep2').style.display = 'block';
    });
}

// Retour à l'étape 1
const btnBack = document.getElementById('btnBackToStep1');
if (btnBack) {
    btnBack.addEventListener('click', () => {
        document.getElementById('donateStep2').style.display = 'none';
        document.getElementById('donateStep1').style.display = 'block';
    });
}

document.getElementById('payJeko')?.addEventListener('click', openPaymentLink);
document.getElementById('payWave')?.addEventListener('click', openPaymentLink);

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






// ======================
// PWA INSTALLATION PROMPT
// ======================
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Empêcher l'affichage automatique du navigateur (Android)
    e.preventDefault();
    deferredPrompt = e;
    
    // Si l'utilisateur a déjà fermé la bannière, on ne lui montre plus
    if (localStorage.getItem('pwa_dismissed') === 'true') return;

    const banner = document.getElementById('pwaInstallBanner');
    const btn = document.getElementById('pwaInstallBtn');
    const txt = document.getElementById('pwaInstallText');
    
    if (banner && btn) {
        txt.innerHTML = "Joue plus facilement, installe le jeu directement sur ton écran d'accueil !";
        banner.style.display = 'block';
        btn.style.display = 'block';
        
        btn.addEventListener('click', async () => {
            banner.style.display = 'none';
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
        });
    }
});

// Détection iOS pour instruction manuelle
const isIos = () => {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test(userAgent);
};
const isStandalone = () => ('standalone' in window.navigator) && window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;

// Afficher la bannière iOS si pas déjà installé et pas fermé
if (isIos() && !isStandalone() && localStorage.getItem('pwa_dismissed') !== 'true') {
    // Petit délai pour ne pas gêner immédiatement le chargement de la page
    setTimeout(() => {
        const banner = document.getElementById('pwaInstallBanner');
        const txt = document.getElementById('pwaInstallText');
        if (banner && txt) {
            txt.innerHTML = "Pour installer l'app, touche l'icône de partage <i class='fa-solid fa-arrow-up-from-bracket' style='color:var(--orange);'></i> puis <br><b>Sur l'écran d'accueil <i class='fa-solid fa-plus' style='color:var(--orange);'></i></b>.";
            banner.style.display = 'block';
        }
    }, 2000);
}

window.fermerPwaBanner = function() {
    document.getElementById('pwaInstallBanner').style.display = 'none';
    localStorage.setItem('pwa_dismissed', 'true');
};
