<?php
session_start();
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/csrf.php';

// --- Accès admin uniquement ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../inscription/login.php');
    exit();
}

$conn   = db_connect();
$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT username, is_admin FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

if (!$me || !$me['is_admin']) {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="fr"><body style="font-family:sans-serif;text-align:center;padding:60px;background:#0f0c07;color:#fdf8f0">
        <h2>🚫 Accès refusé</h2><p>Cette page est réservée à l\'administrateur.</p>
        <a href="../index.php" style="color:#F77F00">← Retour au jeu</a></body></html>';
    $conn->close();
    exit();
}

$adminName = htmlspecialchars($me['username']);
$message   = '';
$erreur    = '';

// ============================================================
// ACTIONS POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_form();
    $action = $_POST['action'] ?? '';

    // --- Ajouter un mot ---
    if ($action === 'ajouter') {
        $nouveauMot = mb_strtoupper(trim($_POST['mot'] ?? ''), 'UTF-8');

        if ($nouveauMot === '' || !preg_match('/^[A-ZÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,30}$/u', $nouveauMot)) {
            $erreur = 'Mot invalide (2–30 lettres, pas de chiffres ni de symboles).';
        } else {
            // Vérifier doublon
            $chk = $conn->prepare('SELECT id FROM mots WHERE UPPER(mot) = ?');
            $chk->bind_param('s', $nouveauMot);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $erreur = "« {$nouveauMot} » est déjà dans la liste.";
            } else {
                // Ordre max + 1
                $maxOrdre = (int) $conn->query('SELECT MAX(ordre) FROM mots')->fetch_row()[0];
                $ins = $conn->prepare('INSERT INTO mots (mot, ordre) VALUES (?, ?)');
                $nextOrdre = $maxOrdre + 1;
                $ins->bind_param('si', $nouveauMot, $nextOrdre);
                if ($ins->execute()) {
                    $message = "✅ « {$nouveauMot} » ajouté avec succès (ordre #{$nextOrdre}).";
                } else {
                    $erreur = 'Erreur lors de l\'ajout.';
                }
            }
        }
    }

    // --- Supprimer un joueur ---
    if ($action === 'supprimer_joueur') {
        $joueurId = (int) ($_POST['joueur_id'] ?? 0);
        if ($joueurId > 0 && $joueurId !== $userId) { // ne peut pas se supprimer lui-même
            $conn->begin_transaction();
            try {
                $d1 = $conn->prepare('DELETE FROM push_subscriptions WHERE user_id = ?');
                $d1->bind_param('i', $joueurId); $d1->execute();
                $d2 = $conn->prepare('DELETE FROM scores WHERE user_id = ?');
                $d2->bind_param('i', $joueurId); $d2->execute();
                $d3 = $conn->prepare('DELETE FROM users WHERE id = ?');
                $d3->bind_param('i', $joueurId); $d3->execute();
                $conn->commit();
                $message = '🗑️ Joueur supprimé avec succès.';
            } catch (Exception $e) {
                $conn->rollback();
                $erreur = 'Erreur lors de la suppression du joueur.';
            }
        } elseif ($joueurId === $userId) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
        }
    }

    // --- Programmer un mot (partenariat ou ordre personnalisé) ---
    if ($action === 'programmer_partenariat') {
        $motRaw = mb_strtoupper(trim($_POST['mot'] ?? ''), 'UTF-8');
        $cible  = $_POST['cible'] ?? 'prochain';
        $def    = trim($_POST['definition'] ?? '');
        $isPart = isset($_POST['est_partenaire']) ? 1 : 0;

        if ($motRaw === '' || !preg_match('/^[A-ZÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,30}$/u', $motRaw)) {
            $erreur = "Mot invalide (2 à 30 lettres, sans chiffres ni symboles).";
        } else {
            if ($cible === 'prochain') {
                $nxt           = prochain_creneau();
                $targetDate    = $nxt['date'];
                $targetCreneau = $nxt['creneau'];
            } else {
                $targetDate    = trim($_POST['date_jour'] ?? '');
                $targetCreneau = (int)($_POST['creneau'] ?? 0);
            }

            $creneauActuel = creneau_actuel();
            $todayStr      = date('Y-m-d');
            $isPasse       = ($targetDate < $todayStr || ($targetDate === $todayStr && $targetCreneau < $creneauActuel));

            if ($targetDate === '' || $isPasse) {
                $erreur = "Impossible de programmer un mot sur un créneau déjà passé.";
            } else {
                // 1. Ajouter ou mettre à jour dans mots
                $chkM = $conn->prepare("SELECT id FROM mots WHERE UPPER(mot) = ?");
                $chkM->bind_param('s', $motRaw);
                $chkM->execute();
                $mRow = $chkM->get_result()->fetch_assoc();
                if (!$mRow) {
                    $maxO = (int) $conn->query('SELECT MAX(ordre) FROM mots')->fetch_row()[0];
                    $nextO = $maxO + 1;
                    $insM = $conn->prepare("INSERT INTO mots (mot, ordre, definition) VALUES (?, ?, ?)");
                    $defVal = $def !== '' ? $def : null;
                    $insM->bind_param('sis', $motRaw, $nextO, $defVal);
                    $insM->execute();
                } elseif ($def !== '') {
                    $updDef = $conn->prepare("UPDATE mots SET definition = ? WHERE UPPER(mot) = ?");
                    $updDef->bind_param('ss', $def, $motRaw);
                    $updDef->execute();
                }

                // 2. Fixer dans mots_du_jour avec flag partenaire
                $insMDJ = $conn->prepare("INSERT INTO mots_du_jour (date_jour, creneau, mot, est_partenaire) VALUES (?, ?, ?, ?)
                                          ON DUPLICATE KEY UPDATE mot = VALUES(mot), est_partenaire = VALUES(est_partenaire)");
                $insMDJ->bind_param('sisi', $targetDate, $targetCreneau, $motRaw, $isPart);
                $insMDJ->execute();

                $typeTxt = $isPart ? "en partenariat 🤝" : "au programme 📅";
                $message = "✨ « {$motRaw} » a été programmé avec succès {$typeTxt} pour ({$targetDate} · " . creneau_libelle($targetCreneau) . ").";
            }
        }
    }

    // --- Modifier le mot d'un créneau spécifique ---
    if ($action === 'changer_mot_creneau') {
        $dateSlot    = trim($_POST['date_jour'] ?? '');
        $creneauSlot = (int)($_POST['creneau'] ?? 0);
        $nouveauMot  = mb_strtoupper(trim($_POST['nouveau_mot'] ?? ''), 'UTF-8');
        $defSlot     = trim($_POST['definition'] ?? '');
        $isPartSlot  = isset($_POST['est_partenaire']) ? 1 : 0;
        $mode        = $_POST['mode'] ?? 'choisir';

        $creneauActuel = creneau_actuel();
        $todayStr      = date('Y-m-d');
        $isPasse       = ($dateSlot < $todayStr || ($dateSlot === $todayStr && $creneauSlot < $creneauActuel));

        if ($isPasse) {
            $erreur = "Impossible de modifier un créneau déjà passé.";
        } elseif ($mode === 'random') {
            $conn->query("DELETE FROM mots_du_jour WHERE date_jour = '{$dateSlot}' AND creneau = {$creneauSlot}");
            $motAttribue = assigner_mot_creneau($conn, $dateSlot, $creneauSlot);
            $message = "🎲 Nouveau mot attribué au créneau ({$dateSlot} · " . creneau_libelle($creneauSlot) . ") : « {$motAttribue} »";
        } elseif ($nouveauMot === '') {
            $erreur = "Veuillez indiquer un mot valide.";
        } else {
            // S'assurer que le mot existe dans le dictionnaire
            $chkM = $conn->prepare("SELECT id FROM mots WHERE UPPER(mot) = ?");
            $chkM->bind_param('s', $nouveauMot);
            $chkM->execute();
            $mRow = $chkM->get_result()->fetch_assoc();
            if (!$mRow) {
                $maxO = (int) $conn->query('SELECT MAX(ordre) FROM mots')->fetch_row()[0];
                $nextO = $maxO + 1;
                $insM = $conn->prepare("INSERT INTO mots (mot, ordre, definition) VALUES (?, ?, ?)");
                $defVal = $defSlot !== '' ? $defSlot : null;
                $insM->bind_param('sis', $nouveauMot, $nextO, $defVal);
                $insM->execute();
            } elseif ($defSlot !== '') {
                $updDef = $conn->prepare("UPDATE mots SET definition = ? WHERE UPPER(mot) = ?");
                $updDef->bind_param('ss', $defSlot, $nouveauMot);
                $updDef->execute();
            }

            // Verrouiller dans mots_du_jour
            $insMDJ = $conn->prepare("INSERT INTO mots_du_jour (date_jour, creneau, mot, est_partenaire) VALUES (?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE mot = VALUES(mot), est_partenaire = VALUES(est_partenaire)");
            $insMDJ->bind_param('sisi', $dateSlot, $creneauSlot, $nouveauMot, $isPartSlot);
            $insMDJ->execute();
            $message = "✅ Mot du créneau ({$dateSlot} · " . creneau_libelle($creneauSlot) . ") fixé sur « {$nouveauMot} ».";
        }
    }

    // --- Supprimer un mot ---
    if ($action === 'supprimer') {
        $motId = (int) ($_POST['mot_id'] ?? 0);
        if ($motId > 0) {
            $creneauActuel = creneau_actuel();
            $todayStr      = date('Y-m-d');

            $motASupprRow = $conn->query("SELECT UPPER(mot) FROM mots WHERE id = $motId")->fetch_row();
            $motASuppr    = $motASupprRow ? strtoupper(trim($motASupprRow[0])) : '';

            // Vérifier si c'est le mot en cours de jeu pour le créneau d'aujourd'hui
            $motEnCours = assigner_mot_creneau($conn, $todayStr, $creneauActuel);
            if ($motASuppr !== '' && $motASuppr === $motEnCours) {
                $erreur = "Impossible de supprimer « {$motASuppr} » : c'est le mot actuellement en cours de jeu.";
            } else {
                // Trouver si ce mot était prévu dans un créneau futur
                $stmtFuture = $conn->prepare("
                    SELECT id, date_jour, creneau 
                    FROM mots_du_jour 
                    WHERE UPPER(mot) = ? 
                      AND (date_jour > ? OR (date_jour = ? AND creneau > ?))
                ");
                $stmtFuture->bind_param('sssi', $motASuppr, $todayStr, $todayStr, $creneauActuel);
                $stmtFuture->execute();
                $slotsImpactes = $stmtFuture->get_result()->fetch_all(MYSQLI_ASSOC);

                // Supprimer le mot de la table générale
                $del = $conn->prepare('DELETE FROM mots WHERE id = ?');
                $del->bind_param('i', $motId);
                $del->execute();

                // Remplacer UNIQUEMENT les créneaux futurs qui avaient ce mot précis
                foreach ($slotsImpactes as $slot) {
                    $conn->query("DELETE FROM mots_du_jour WHERE id = {$slot['id']}");
                    assigner_mot_creneau($conn, $slot['date_jour'], (int)$slot['creneau']);
                }

                $message = "🗑️ « {$motASuppr} » supprimé.";
                if (!empty($slotsImpactes)) {
                    $message .= " Il a été réattribué dans les " . count($slotsImpactes) . " créneau(x) futur(s) sans modifier le reste du calendrier.";
                }
            }
        }
    }

    // --- Modifier la définition d'un mot ---
    if ($action === 'modifier_definition') {
        $motId  = (int) ($_POST['mot_id'] ?? 0);
        $newDef = trim($_POST['definition'] ?? '');
        if ($motId > 0) {
            $upd = $conn->prepare('UPDATE mots SET definition = ? WHERE id = ?');
            $newDefOrNull = $newDef !== '' ? $newDef : null;
            $upd->bind_param('si', $newDefOrNull, $motId);
            if ($upd->execute()) {
                $message = '✅ Définition mise à jour.';
            } else {
                $erreur = 'Erreur lors de la mise à jour de la définition.';
            }
        }
    }
}

// ============================================================
// DONNÉES
// ============================================================
$jourNum    = jour_numero();
$totalMots  = (int) $conn->query('SELECT COUNT(*) FROM mots WHERE ordre IS NOT NULL')->fetch_row()[0];
$recherche  = trim($_GET['q'] ?? '');

// Liste des mots (avec filtre)
if ($recherche !== '') {
    $like = '%' . $recherche . '%';
    $stmtMots = $conn->prepare('SELECT id, mot, ordre, definition FROM mots WHERE mot LIKE ? ORDER BY ordre ASC');
    $stmtMots->bind_param('s', $like);
} else {
    $stmtMots = $conn->prepare('SELECT id, mot, ordre, definition FROM mots ORDER BY ordre ASC');
}
$stmtMots->execute();
$tousLesMots = $stmtMots->get_result()->fetch_all(MYSQLI_ASSOC);

// Pré-planifier et verrouiller les mots pour les 4 prochains jours dans mots_du_jour
// Garantit une stabilité totale : supprimer un mot ne décale JAMAIS les autres créneaux !
garantir_mots_planifies($conn, 4);

$creneauActuel = creneau_actuel();
$calendrierParJour = [];
$joursFr = ['Mon'=>'Lun', 'Tue'=>'Mar', 'Wed'=>'Mer', 'Thu'=>'Jeu', 'Fri'=>'Ven', 'Sat'=>'Sam', 'Sun'=>'Dim'];

for ($d = 0; $d < 4; $d++) {
    $dateObj = new DateTime("+{$d} day");
    $date    = $dateObj->format('Y-m-d');

    if ($d === 0) {
        $jourTitre = "Aujourd'hui";
    } elseif ($d === 1) {
        $engDay    = $dateObj->format('D');
        $jourTitre = "Demain (" . ($joursFr[$engDay] ?? $engDay) . " " . $dateObj->format('d/m') . ")";
    } else {
        $engDay    = $dateObj->format('D');
        $jourTitre = ($joursFr[$engDay] ?? $engDay) . ' ' . $dateObj->format('d/m');
    }

    $creneaux = [];
    for ($c = 0; $c < CRENEAUX_PAR_JOUR; $c++) {
        $cLibelle = creneau_libelle($c);
        $mot = assigner_mot_creneau($conn, $date, $c);

        // Récupérer le statut partenaire et la définition
        $stmtStat = $conn->prepare("SELECT est_partenaire FROM mots_du_jour WHERE date_jour = ? AND creneau = ?");
        $stmtStat->bind_param('si', $date, $c);
        $stmtStat->execute();
        $stRow = $stmtStat->get_result()->fetch_assoc();
        $isPart = $stRow ? (int)($stRow['est_partenaire'] ?? 0) : 0;

        $defStmt = $conn->prepare("SELECT definition FROM mots WHERE UPPER(mot) = ?");
        $defStmt->bind_param('s', $mot);
        $defStmt->execute();
        $dRow = $defStmt->get_result()->fetch_assoc();
        $defText = $dRow['definition'] ?? '';

        $isActuel = ($d === 0 && $c === $creneauActuel);
        $isPasse  = ($d === 0 && $c < $creneauActuel);

        $creneaux[] = [
            'creneau'        => $c,
            'slot_label'     => $cLibelle,
            'mot'            => $mot,
            'definition'     => $defText,
            'actuel'         => $isActuel,
            'passe'          => $isPasse,
            'est_partenaire' => $isPart,
        ];
    }

    $calendrierParJour[] = [
        'date'     => $date,
        'titre'    => $jourTitre,
        'est_auj'  => ($d === 0),
        'creneaux' => $creneaux,
    ];
}

// Stats globales
$statsRow = $conn->query("SELECT COUNT(*) AS parties, SUM(trouve) AS victoires FROM scores")->fetch_assoc();
$nbJoueurs = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

// Liste des joueurs
$joueurs = $conn->query("
    SELECT u.id, u.username, u.email, u.is_admin,
           COUNT(s.id) AS nb_parties,
           SUM(s.trouve) AS nb_victoires,
           MAX(s.date_jour) AS derniere_partie
    FROM users u
    LEFT JOIN scores s ON s.user_id = u.id
    GROUP BY u.id
    ORDER BY u.id ASC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../assets/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/icon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – iMots CI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Paytone+One&family=Nunito:wght@400;600;700;800&display=swap');

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --orange: #F77F00;
            --vert:   #009E60;
            --fond:   #0F0C07;
            --fond2:  #181208;
            --fond3:  #221A0E;
            --texte:  #FDF8F0;
            --gris:   rgba(253,248,240,0.5);
            --rouge:  #e53e3e;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--fond);
            color: var(--texte);
            min-height: 100vh;
        }

        /* NAV */
        nav {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            background: var(--fond3);
            border-bottom: 2px solid var(--orange);
            flex-wrap: wrap;
        }
        nav .brand {
            font-family: 'Paytone One', sans-serif;
            color: var(--orange);
            font-size: 18px;
            flex: 1;
        }
        nav a {
            color: var(--texte);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1px solid rgba(253,248,240,0.15);
            transition: background 0.2s;
        }
        nav a:hover { background: rgba(247,127,0,0.15); color: var(--orange); }
        nav .admin-badge {
            background: linear-gradient(135deg, var(--orange), #e06000);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            border: none;
        }

        /* LAYOUT */
        .page { max-width: 1100px; margin: 0 auto; padding: 24px 16px; }

        h1 { font-family: 'Paytone One', sans-serif; font-size: 24px; color: var(--orange); margin-bottom: 4px; }
        .subtitle { color: var(--gris); font-size: 13px; margin-bottom: 24px; }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--fond3);
            border: 1px solid rgba(247,127,0,0.2);
            border-radius: 14px;
            padding: 16px;
            text-align: center;
        }
        .stat-card .val {
            font-family: 'Paytone One', sans-serif;
            font-size: 28px;
            color: var(--orange);
        }
        .stat-card .lbl { font-size: 12px; color: var(--gris); margin-top: 4px; }

        /* MESSAGES */
        .msg { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-weight: 700; font-size: 14px; }
        .msg.ok  { background: rgba(0,158,96,0.15); border: 1px solid var(--vert); color: #6effc3; }
        .msg.err { background: rgba(229,62,62,0.15); border: 1px solid var(--rouge); color: #fc8181; }

        /* DEUX COLONNES */
        .cols { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
        @media(max-width:860px) { .cols { grid-template-columns: 1fr; } }
        html { -webkit-text-size-adjust:100%; }
        .table-mots { max-width:100%; }
        @media(max-width:640px) { body { overflow-x:hidden; } .table-mots { display:block; overflow-x:auto; white-space:nowrap; } .modal-box, .modal-content { max-width:94vw; max-height:92dvh; overflow-y:auto; } }

        /* SECTION */
        .section {
            background: var(--fond3);
            border: 1px solid rgba(247,127,0,0.15);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: rgba(247,127,0,0.07);
            border-bottom: 1px solid rgba(247,127,0,0.15);
            flex-wrap: wrap;
            gap: 8px;
        }
        .section-head h2 { font-size: 15px; font-weight: 800; color: var(--orange); }
        .count-badge {
            background: rgba(247,127,0,0.2);
            color: var(--orange);
            font-size: 12px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* AJOUT MOT */
        .form-ajouter {
            padding: 16px 18px;
            display: flex;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            flex-wrap: wrap;
        }
        .form-ajouter input[type=text] {
            flex: 1;
            min-width: 180px;
            padding: 10px 14px;
            background: rgba(253,248,240,0.05);
            border: 2px solid rgba(247,127,0,0.25);
            border-radius: 10px;
            color: var(--texte);
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            outline: none;
        }
        .form-ajouter input[type=text]:focus { border-color: var(--orange); }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Paytone One', sans-serif;
            font-size: 13px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-vert { background: linear-gradient(135deg, var(--vert), #006B40); color: #fff; box-shadow: 0 4px 14px rgba(0,158,96,0.3); }
        .btn-vert:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,158,96,0.45); }
        .btn-rouge { background: rgba(229,62,62,0.15); border: 1px solid rgba(229,62,62,0.35); color: #fc8181; padding: 5px 10px; font-size: 12px; font-family: 'Nunito', sans-serif; font-weight: 800; border-radius: 8px; }
        .btn-rouge:hover { background: rgba(229,62,62,0.3); }

        /* FILTRE */
        .filtre-box { padding: 10px 18px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .filtre-box input {
            width: 100%;
            padding: 8px 14px;
            background: rgba(253,248,240,0.04);
            border: 1px solid rgba(253,248,240,0.1);
            border-radius: 8px;
            color: var(--texte);
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            outline: none;
        }
        .filtre-box input:focus { border-color: var(--orange); }

        /* TABLE MOTS */
        .table-mots { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table-mots thead tr { background: rgba(247,127,0,0.08); }
        .table-mots th { padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--gris); }
        .table-mots tbody tr { border-top: 1px solid rgba(255,255,255,0.04); transition: background 0.15s; }
        .table-mots tbody tr:hover { background: rgba(247,127,0,0.05); }
        .row-admin { background: rgba(247,127,0,0.06); }
        .badge-admin {
            display: inline-block;
            background: rgba(247,127,0,0.2);
            color: var(--orange);
            border: 1px solid rgba(247,127,0,0.4);
            border-radius: 4px;
            font-size: .7em;
            padding: 1px 6px;
            margin-left: 6px;
            vertical-align: middle;
            font-weight: 700;
        }
        .badge-count {
            background: rgba(247,127,0,0.15);
            color: var(--orange);
            border: 1px solid rgba(247,127,0,0.3);
            border-radius: 20px;
            font-size: .8em;
            padding: 3px 10px;
            font-weight: 700;
        }
        .btn-del-joueur {
            background: rgba(229,62,62,0.1);
            border: 1px solid rgba(229,62,62,0.35);
            color: #e53e3e;
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            font-size: .85em;
            transition: background .2s;
        }
        .btn-del-joueur:hover { background: rgba(229,62,62,0.25); }
        .table-mots td { padding: 9px 14px; }
        .mot-cell { font-weight: 800; letter-spacing: 1.5px; font-size: 15px; }
        .ordre-cell { color: var(--gris); font-size: 12px; }
        .action-cell { text-align: right; }

        /* DÉFINITION */
        .def-cell {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: rgba(253,248,240,0.55);
            font-size: 13px;
            font-style: italic;
        }
        .def-cell .no-def { color: rgba(253,248,240,0.2); font-style: normal; }
        .btn-edit-def {
            background: rgba(0,158,96,0.1);
            border: 1px solid rgba(0,158,96,0.3);
            color: #4dffa0;
            border-radius: 8px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: .9em;
            transition: background .2s;
            margin-right: 4px;
        }
        .btn-edit-def:hover { background: rgba(0,158,96,0.25); }

        /* MODAL DÉFINITION ADMIN */
        #modalDef {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 300;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }
        .modal-def-inner {
            background: var(--fond3);
            border: 1px solid rgba(247,127,0,0.3);
            border-radius: 18px;
            padding: 28px;
            max-width: 500px;
            width: 90%;
            position: relative;
            animation: defIn .3s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes defIn {
            from { opacity:0; transform: scale(.9) translateY(20px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .modal-def-inner h3 { color: var(--orange); font-family: 'Paytone One',sans-serif; margin-bottom: 16px; font-size: 18px; }
        .modal-def-inner textarea {
            width: 100%; padding: 12px 14px;
            background: rgba(253,248,240,0.05);
            border: 2px solid rgba(247,127,0,0.25);
            border-radius: 10px;
            color: var(--texte);
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            resize: vertical;
            outline: none;
            transition: border-color .2s;
            line-height: 1.6;
        }
        .modal-def-inner textarea:focus { border-color: var(--orange); }
        .modal-def-inner .modal-actions { display: flex; gap: 10px; margin-top: 14px; }
        .btn-cancel { background: rgba(253,248,240,0.08); border: 1px solid rgba(253,248,240,0.15); color: var(--texte); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-family: 'Nunito',sans-serif; font-size: 13px; font-weight: 700; transition: background .2s; }
        .btn-cancel:hover { background: rgba(253,248,240,0.15); }
        .btn-close-modal { position: absolute; top: 14px; right: 14px; background: none; border: none; color: rgba(253,248,240,0.4); font-size: 22px; cursor: pointer; line-height: 1; transition: color .2s; }
        .btn-close-modal:hover { color: var(--texte); }

        /* WRAPPER SCROLLABLE */
        .table-wrapper { max-height: 480px; overflow-y: auto; }
        .table-wrapper::-webkit-scrollbar { width: 4px; }
        .table-wrapper::-webkit-scrollbar-thumb { background: rgba(247,127,0,0.3); border-radius: 4px; }

        /* CALENDRIER SCROLLABLE ALIGNÉ AVEC LA TABLE */
        .calendrier {
            padding: 10px 12px;
            max-height: 480px;
            overflow-y: auto;
        }
        .calendrier::-webkit-scrollbar { width: 4px; }
        .calendrier::-webkit-scrollbar-thumb { background: rgba(247,127,0,0.3); border-radius: 4px; }

        .cal-group {
            margin-bottom: 12px;
        }
        .cal-group:last-child {
            margin-bottom: 0;
        }
        .cal-group-header {
            font-size: 11px;
            font-weight: 800;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 8px;
            background: rgba(247,127,0,0.08);
            border-radius: 6px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cal-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            margin-bottom: 3px;
            border: 1px solid transparent;
            transition: background 0.15s;
        }
        .cal-row.today {
            background: rgba(247,127,0,0.15);
            border-color: rgba(247,127,0,0.45);
        }
        .cal-row.past {
            opacity: 0.45;
        }
        .cal-row:hover { background: rgba(253,248,240,0.04); }
        .btn-slot-edit {
            background: rgba(253,248,240,0.07);
            border: 1px solid rgba(253,248,240,0.2);
            color: rgba(253,248,240,0.7);
            border-radius: 6px;
            padding: 3px 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.15s;
            margin-left: 4px;
        }
        .btn-slot-edit:hover {
            background: rgba(247,127,0,0.25);
            border-color: var(--orange);
            color: #fff;
        }
        .badge-partenaire {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
            color: #120e06;
            font-weight: 800;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            box-shadow: 0 2px 8px rgba(255, 140, 0, 0.4);
        }
        .btn-partenaire-header {
            background: linear-gradient(135deg, #ff8c00, #e06000);
            color: #fff;
            padding: 6px 12px;
            font-size: 12px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-partenaire-header:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(247,127,0,0.4);
        }
        .btn-prog-partenaire {
            background: rgba(247,127,0,0.12);
            border: 1px solid rgba(247,127,0,0.35);
            color: var(--orange);
            border-radius: 8px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: .85em;
            font-weight: 700;
            transition: background .2s;
            margin-right: 4px;
        }
        .btn-prog-partenaire:hover {
            background: rgba(247,127,0,0.3);
            color: #fff;
        }
        .cal-slot {
            font-size: 11px;
            color: var(--gris);
            width: 78px;
            flex-shrink: 0;
            font-weight: 600;
        }
        .cal-mot { font-weight: 800; font-size: 13px; letter-spacing: 1px; flex: 1; }
        .cal-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-confirme { background: rgba(0,158,96,0.2); color: #6effc3; }
        .badge-prevu    { background: rgba(253,248,240,0.06); color: var(--gris); }
        .badge-today    { background: var(--orange); color: #fff; }
        .badge-passe    { background: rgba(253,248,240,0.05); color: #888; }
    </style>
</head>
<body>

<nav>
    <span class="brand">🇨🇮 Admin iMots CI</span>
    <span class="admin-badge">👑 <?= $adminName ?></span>
    <a href="../index.php"><i class="fa-solid fa-gamepad"></i> Jeu</a>
    <a href="../inscription/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
</nav>

<div class="page">

    <h1>⚙️ Gestion des mots</h1>
    <p class="subtitle">Consulte, ajoute ou supprime les mots du jeu. Le calendrier est généré automatiquement.</p>

    <?php if ($message): ?><div class="msg ok"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($erreur):  ?><div class="msg err"><?= htmlspecialchars($erreur)  ?></div><?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="val"><?= $totalMots ?></div>
            <div class="lbl">Mots dans la liste</div>
        </div>
        <div class="stat-card">
            <div class="val"><?= $totalMots ?></div>
            <div class="lbl">Jours avant répétition</div>
        </div>
        <div class="stat-card">
            <div class="val"><?= $nbJoueurs ?></div>
            <div class="lbl">Joueurs inscrits</div>
        </div>
        <div class="stat-card">
            <div class="val"><?= (int)$statsRow['parties'] ?></div>
            <div class="lbl">Parties jouées</div>
        </div>
        <div class="stat-card">
            <div class="val"><?= (int)$statsRow['victoires'] ?></div>
            <div class="lbl">Victoires</div>
        </div>
        <div class="stat-card">
            <div class="val"><?= $jourNum + 1 ?></div>
            <div class="lbl">Jour actuel</div>
        </div>
    </div>

    <div class="cols">

        <!-- COLONNE GAUCHE : liste des mots -->
        <div>
            <div class="section">
                <div class="section-head">
                    <h2><i class="fa-solid fa-list"></i> Tous les mots</h2>
                    <span class="count-badge"><?= count($tousLesMots) ?> <?= $recherche ? "/ {$totalMots}" : '' ?></span>
                </div>

                <!-- Ajouter un mot -->
                <form class="form-ajouter" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="ajouter">
                    <input type="text" name="mot" placeholder="Nouveau mot…" maxlength="30"
                           autocomplete="off" spellcheck="false">
                    <button type="submit" class="btn btn-vert">
                        <i class="fa-solid fa-plus"></i> Ajouter
                    </button>
                </form>

                <!-- Filtre -->
                <form class="filtre-box" method="GET">
                    <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>"
                           placeholder="🔍 Filtrer les mots…" autocomplete="off">
                </form>

                <!-- Table -->
                <div class="table-wrapper">
                    <table class="table-mots">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Mot</th>
                                <th>Définition</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tousLesMots as $m): ?>
                            <tr>
                                <td class="ordre-cell">#<?= $m['ordre'] ?></td>
                                <td class="mot-cell"><?= htmlspecialchars($m['mot']) ?></td>
                                <td class="def-cell">
                                    <?php if (!empty($m['definition'])): ?>
                                        <?= htmlspecialchars(mb_substr($m['definition'], 0, 55)) ?><?= mb_strlen($m['definition'] ?? '') > 55 ? '…' : '' ?>
                                    <?php else: ?>
                                        <span class="no-def">— aucune</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <button type="button" class="btn-prog-partenaire"
                                            data-mot="<?= htmlspecialchars($m['mot'], ENT_QUOTES) ?>"
                                            data-def="<?= htmlspecialchars($m['definition'] ?? '', ENT_QUOTES) ?>"
                                            title="Programmer ce mot pour un créneau ou partenariat">
                                        🤝 Programmer
                                    </button>
                                    <button type="button" class="btn-edit-def"
                                            data-id="<?= $m['id'] ?>"
                                            data-mot="<?= htmlspecialchars($m['mot'], ENT_QUOTES) ?>"
                                            data-def="<?= htmlspecialchars($m['definition'] ?? '', ENT_QUOTES) ?>"
                                            title="Modifier la définition">
                                        📖 Définir
                                    </button>
                                    <form method="POST" style="display:inline"
                                          onsubmit="return confirm('Supprimer « <?= htmlspecialchars($m['mot']) ?> » ?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="mot_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-rouge" title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($tousLesMots)): ?>
                            <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gris)">Aucun mot trouvé.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : calendrier -->
        <div>
            <div class="section">
                <div class="section-head">
                    <div>
                        <h2><i class="fa-solid fa-clock"></i> Prochains mots</h2>
                        <span class="count-badge">4 jours · 16 mots</span>
                    </div>
                    <button type="button" id="btnOpenProgModal" class="btn-partenaire-header">
                        <i class="fa-solid fa-handshake"></i> Programmer un mot
                    </button>
                </div>
                <div class="calendrier">
                    <?php foreach ($calendrierParJour as $jour): ?>
                    <div class="cal-group">
                        <div class="cal-group-header">
                            <i class="fa-regular fa-calendar"></i>
                            <span><?= htmlspecialchars($jour['titre']) ?></span>
                        </div>
                        <?php foreach ($jour['creneaux'] as $c): ?>
                        <div class="cal-row <?= $c['actuel'] ? 'today' : ($c['passe'] ? 'past' : '') ?>">
                            <span class="cal-slot"><?= $c['slot_label'] ?></span>
                            <span class="cal-mot"><?= htmlspecialchars($c['mot']) ?></span>
                            <?php if ($c['est_partenaire']): ?>
                                <span class="badge-partenaire"><i class="fa-solid fa-star"></i> Sponsor</span>
                            <?php elseif ($c['actuel']): ?>
                                <span class="cal-badge badge-today">En cours</span>
                            <?php elseif ($c['passe']): ?>
                                <span class="cal-badge badge-passe">Passé</span>
                            <?php else: ?>
                                <span class="cal-badge badge-confirme">Prévu</span>
                            <?php endif; ?>
                            <?php if (!$c['passe']): ?>
                                <button type="button" class="btn-slot-edit"
                                        data-date="<?= $jour['date'] ?>"
                                        data-creneau="<?= $c['creneau'] ?>"
                                        data-creneautext="<?= htmlspecialchars($jour['titre']) ?> · <?= htmlspecialchars($c['slot_label']) ?>"
                                        data-mot="<?= htmlspecialchars($c['mot']) ?>"
                                        data-def="<?= htmlspecialchars($c['definition'] ?? '') ?>"
                                        data-part="<?= $c['est_partenaire'] ? '1' : '0' ?>"
                                        title="Modifier ou changer ce créneau">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== GESTION DES JOUEURS ===== -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fa-solid fa-users"></i> Gestion des joueurs</h2>
            <span class="badge-count"><?= count($joueurs) ?> compte<?= count($joueurs) > 1 ? 's' : '' ?></span>
        </div>

        <div class="table-wrapper" style="overflow-x:auto">
            <table class="table-mots" style="min-width:600px">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Parties</th>
                        <th>Victoires</th>
                        <th>Dernière partie</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($joueurs as $j): ?>
                    <tr class="<?= $j['is_admin'] ? 'row-admin' : '' ?>">
                        <td><?= $j['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($j['username']) ?>
                            <?php if ($j['is_admin']): ?>
                                <span class="badge-admin">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--gris);font-size:.85em"><?= htmlspecialchars($j['email']) ?></td>
                        <td><?= (int)$j['nb_parties'] ?></td>
                        <td><?= (int)$j['nb_victoires'] ?></td>
                        <td style="font-size:.85em"><?= $j['derniere_partie'] ?? '—' ?></td>
                        <td>
                        <?php if ($j['id'] !== $userId): ?>
                            <form method="POST" onsubmit="return confirmerSuppJoueur('<?= htmlspecialchars($j['username'], ENT_QUOTES) ?>')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="supprimer_joueur">
                                <input type="hidden" name="joueur_id" value="<?= $j['id'] ?>">
                                <button type="submit" class="btn-del-joueur" title="Supprimer ce joueur">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--gris);font-size:.8em">Vous</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<!-- MODAL ÉDITION DÉFINITION -->
<div id="modalDef">
    <div class="modal-def-inner">
        <button class="btn-close-modal" onclick="fermerModalDef()">&times;</button>
        <h3 id="modalDefTitre">📖 Définition</h3>
        <form method="POST" id="formDef">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="modifier_definition">
            <input type="hidden" name="mot_id" id="modalDefId">
            <textarea name="definition" id="modalDefTextarea" rows="5"
                      placeholder="Saisissez la définition du mot…"></textarea>
            <div class="modal-actions">
                <button type="submit" class="btn btn-vert" style="flex:1">✅ Enregistrer</button>
                <button type="button" class="btn-cancel" onclick="fermerModalDef()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmerSuppJoueur(pseudo) {
    return confirm('⚠️ Supprimer le joueur « ' + pseudo + ' » ?\n\nSes scores seront définitivement supprimés.');
}

// Filtre en temps réel
const filtreInput = document.querySelector('.filtre-box input');
filtreInput.addEventListener('input', function () {
    const q = this.value.toUpperCase().trim();
    document.querySelectorAll('.table-mots tbody tr').forEach(tr => {
        const mot = tr.querySelector('.mot-cell')?.textContent || '';
        tr.style.display = mot.includes(q) ? '' : 'none';
    });
});

// Modal définition
document.querySelectorAll('.btn-edit-def').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('modalDefId').value           = btn.dataset.id;
        document.getElementById('modalDefTitre').textContent  = '📖 ' + btn.dataset.mot;
        document.getElementById('modalDefTextarea').value     = btn.dataset.def;
        document.getElementById('modalDef').style.display     = 'flex';
        document.getElementById('modalDefTextarea').focus();
    });
});

function fermerModalDef() {
    document.getElementById('modalDef').style.display = 'none';
}

document.getElementById('modalDef').addEventListener('click', e => {
    if (e.target === document.getElementById('modalDef')) fermerModalDef();
});
</script>


    <!-- MODAL CHANGER MOT DU CRÉNEAU -->
    <div id="modalSlot" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:300;justify-content:center;align-items:center;backdrop-filter:blur(4px);">
        <div class="modal-def-inner">
            <button type="button" class="btn-close-modal" id="closeModalSlot">&times;</button>
            <h3 style="color:var(--orange);font-family:'Paytone One',sans-serif;margin-bottom:6px;font-size:18px;">
                <i class="fa-solid fa-clock"></i> Modifier le mot du créneau
            </h3>
            <p id="slotInfoText" style="font-size:13px;color:var(--gris);margin-bottom:14px;font-weight:700;"></p>
            
            <form method="POST" id="formSlot">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="changer_mot_creneau">
                <input type="hidden" name="date_jour" id="slotDate">
                <input type="hidden" name="creneau" id="slotCreneau">
                <input type="hidden" name="mode" id="slotMode" value="choisir">

                <label style="display:block;font-size:12px;color:var(--gris);margin-bottom:6px;font-weight:700;">Choisir ou saisir un mot :</label>
                <input type="text" name="nouveau_mot" id="slotNouveauMot" list="listeMotsDico"
                       placeholder="Tape un mot…" autocomplete="off"
                       style="width:100%;padding:10px 14px;background:rgba(253,248,240,0.05);border:2px solid rgba(247,127,0,0.25);border-radius:10px;color:var(--texte);font-family:'Nunito',sans-serif;font-size:15px;font-weight:800;text-transform:uppercase;outline:none;margin-bottom:12px;">

                <label style="display:block;font-size:12px;color:var(--gris);margin-bottom:6px;font-weight:700;">Définition ou message du sponsor (optionnel) :</label>
                <textarea name="definition" id="slotDef" rows="2" placeholder="Slogan ou définition affichée en fin de jeu..."
                          style="width:100%;padding:10px 14px;background:rgba(253,248,240,0.05);border:2px solid rgba(247,127,0,0.25);border-radius:10px;color:var(--texte);font-family:'Nunito',sans-serif;font-size:13px;outline:none;margin-bottom:12px;resize:vertical;"></textarea>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#ffd700;font-weight:700;margin-bottom:16px;cursor:pointer;">
                    <input type="checkbox" name="est_partenaire" id="slotIsPartenaire" value="1" style="width:18px;height:18px;accent-color:var(--orange);">
                    ⭐ Marquer comme mot Partenaire / Sponsorisé
                </label>

                <div class="modal-actions" style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-vert" style="flex:1;">
                        <i class="fa-solid fa-check"></i> Fixer ce mot
                    </button>
                    <button type="button" id="btnSlotRandom" class="btn" style="background:rgba(247,127,0,0.2);border:1px solid rgba(247,127,0,0.4);color:var(--orange);cursor:pointer;">
                        <i class="fa-solid fa-shuffle"></i> Hasard
                    </button>
                    <button type="button" class="btn-cancel" id="cancelModalSlot">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PROGRAMMER UN MOT / PARTENARIAT -->
    <?php $nxt = prochain_creneau(); ?>
    <div id="modalProgrammerPartenaire" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:300;justify-content:center;align-items:center;backdrop-filter:blur(4px);">
        <div class="modal-def-inner" style="max-width:540px;">
            <button type="button" class="btn-close-modal" id="closeModalProg">&times;</button>
            <h3 style="color:var(--orange);font-family:'Paytone One',sans-serif;margin-bottom:4px;font-size:19px;">
                <i class="fa-solid fa-handshake"></i> Programmer un mot (Partenariat)
            </h3>
            <p style="font-size:12.5px;color:var(--gris);margin-bottom:16px;">
                Place un mot de marque ou un mot spécial en avant, immédiatement ou à une date choisie.
            </p>

            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="programmer_partenariat">

                <label style="display:block;font-size:12px;color:var(--gris);margin-bottom:6px;font-weight:700;">Mot à mettre en avant :</label>
                <input type="text" name="mot" id="progMotInput" list="listeMotsDico" required
                       placeholder="Ex: WAVE, ORANGE, SOLIBRA…" autocomplete="off"
                       style="width:100%;padding:10px 14px;background:rgba(253,248,240,0.05);border:2px solid rgba(247,127,0,0.25);border-radius:10px;color:var(--texte);font-family:'Nunito',sans-serif;font-size:15px;font-weight:800;text-transform:uppercase;outline:none;margin-bottom:14px;">

                <label style="display:block;font-size:12px;color:var(--gris);margin-bottom:8px;font-weight:700;">Quand diffuser ce mot ?</label>
                <div style="background:rgba(253,248,240,0.03);border:1px solid rgba(247,127,0,0.2);border-radius:10px;padding:12px;margin-bottom:14px;">
                    <label style="display:flex;align-items:center;gap:10px;font-size:13.5px;font-weight:700;color:#fff;margin-bottom:10px;cursor:pointer;">
                        <input type="radio" name="cible" value="prochain" id="radioProchain" checked style="accent-color:var(--orange);width:17px;height:17px;">
                        <span>⚡ <strong>Au tout prochain créneau</strong> (<?= $nxt['libelle'] ?>)</span>
                    </label>

                    <label style="display:flex;align-items:center;gap:10px;font-size:13.5px;font-weight:700;color:#fff;margin-bottom:8px;cursor:pointer;">
                        <input type="radio" name="cible" value="specifique" id="radioSpecifique" style="accent-color:var(--orange);width:17px;height:17px;">
                        <span>📅 <strong>Choisir une date et un créneau précis</strong></span>
                    </label>

                    <div id="blocDateSpecifique" style="display:none;margin-top:10px;padding-top:10px;border-top:1px dashed rgba(247,127,0,0.2);display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <span style="font-size:11px;color:var(--gris);display:block;margin-bottom:4px;">Date :</span>
                            <input type="date" name="date_jour" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>"
                                   style="width:100%;padding:8px 10px;background:rgba(253,248,240,0.06);border:1px solid rgba(247,127,0,0.3);border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:13px;outline:none;">
                        </div>
                        <div>
                            <span style="font-size:11px;color:var(--gris);display:block;margin-bottom:4px;">Créneau horaire :</span>
                            <select name="creneau" style="width:100%;padding:8px 10px;background:#221a0e;border:1px solid rgba(247,127,0,0.3);border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:13px;outline:none;">
                                <option value="0">🕛 00h - 06h</option>
                                <option value="1">🌅 06h - 12h</option>
                                <option value="2">☀️ 12h - 18h</option>
                                <option value="3">🌙 18h - 00h</option>
                            </select>
                        </div>
                    </div>
                </div>

                <label style="display:block;font-size:12px;color:var(--gris);margin-bottom:6px;font-weight:700;">Définition ou slogan du partenaire :</label>
                <textarea name="definition" id="progDefInput" rows="2" placeholder="Ex: Wave CI – Application de transfert d'argent sans frais..."
                          style="width:100%;padding:10px 14px;background:rgba(253,248,240,0.05);border:2px solid rgba(247,127,0,0.25);border-radius:10px;color:var(--texte);font-family:'Nunito',sans-serif;font-size:13px;outline:none;margin-bottom:12px;resize:vertical;"></textarea>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#ffd700;font-weight:700;margin-bottom:18px;cursor:pointer;">
                    <input type="checkbox" name="est_partenaire" value="1" checked style="width:18px;height:18px;accent-color:var(--orange);">
                    ⭐ Marquer comme mot Partenaire / Sponsorisé
                </label>

                <div class="modal-actions" style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-vert" style="flex:1;">
                        <i class="fa-solid fa-check"></i> Enregistrer et Programmer
                    </button>
                    <button type="button" class="btn-cancel" id="cancelProgModal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Gestion du modal pour changer le mot d'un créneau
    const modalSlot      = document.getElementById('modalSlot');
    const slotDate       = document.getElementById('slotDate');
    const slotCreneau    = document.getElementById('slotCreneau');
    const slotNouveauMot = document.getElementById('slotNouveauMot');
    const slotInfoText   = document.getElementById('slotInfoText');
    const slotMode       = document.getElementById('slotMode');

    const slotDef          = document.getElementById('slotDef');
    const slotIsPartenaire = document.getElementById('slotIsPartenaire');

    document.querySelectorAll('.btn-slot-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            slotDate.value          = btn.dataset.date;
            slotCreneau.value       = btn.dataset.creneau;
            slotNouveauMot.value    = btn.dataset.mot;
            if (slotDef) slotDef.value = btn.dataset.def || '';
            if (slotIsPartenaire) slotIsPartenaire.checked = (btn.dataset.part === '1');
            slotInfoText.textContent = btn.dataset.creneautext + ' (Mot actuel : ' + btn.dataset.mot + ')';
            slotMode.value          = 'choisir';
            modalSlot.style.display = 'flex';
            slotNouveauMot.focus();
        });
    });

    // Modal Programmer un partenariat
    const modalProg          = document.getElementById('modalProgrammerPartenaire');
    const btnOpenProgModal   = document.getElementById('btnOpenProgModal');
    const closeModalProg     = document.getElementById('closeModalProg');
    const cancelProgModal    = document.getElementById('cancelProgModal');
    const progMotInput       = document.getElementById('progMotInput');
    const progDefInput       = document.getElementById('progDefInput');
    const radioProchain      = document.getElementById('radioProchain');
    const radioSpecifique    = document.getElementById('radioSpecifique');
    const blocDateSpecifique = document.getElementById('blocDateSpecifique');

    function toggleDateSpecifique() {
        if (blocDateSpecifique) {
            blocDateSpecifique.style.display = radioSpecifique.checked ? 'grid' : 'none';
        }
    }
    radioProchain?.addEventListener('change', toggleDateSpecifique);
    radioSpecifique?.addEventListener('change', toggleDateSpecifique);

    btnOpenProgModal?.addEventListener('click', () => {
        progMotInput.value = '';
        progDefInput.value = '';
        radioProchain.checked = true;
        toggleDateSpecifique();
        modalProg.style.display = 'flex';
        progMotInput.focus();
    });

    document.querySelectorAll('.btn-prog-partenaire').forEach(btn => {
        btn.addEventListener('click', () => {
            progMotInput.value = btn.dataset.mot || '';
            progDefInput.value = btn.dataset.def || '';
            radioProchain.checked = true;
            toggleDateSpecifique();
            modalProg.style.display = 'flex';
            progMotInput.focus();
        });
    });

    closeModalProg?.addEventListener('click', () => modalProg.style.display = 'none');
    cancelProgModal?.addEventListener('click', () => modalProg.style.display = 'none');
    modalProg?.addEventListener('click', (e) => {
        if (e.target === modalProg) modalProg.style.display = 'none';
    });

    document.getElementById('btnSlotRandom')?.addEventListener('click', () => {
        slotMode.value = 'random';
        document.getElementById('formSlot').submit();
    });

    document.getElementById('closeModalSlot')?.addEventListener('click', () => {
        modalSlot.style.display = 'none';
    });
    document.getElementById('cancelModalSlot')?.addEventListener('click', () => {
        modalSlot.style.display = 'none';
    });
    modalSlot?.addEventListener('click', (e) => {
        if (e.target === modalSlot) modalSlot.style.display = 'none';
    });
    </script>

</body>
</html>

