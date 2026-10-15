<?php
/**
 * lettres.php — Gestion des lettres accentuées en MAJUSCULES (É, È, Ê, Ï, Ç…).
 *
 * Règle du jeu :
 *  - Le joueur peut saisir les accents (É, È, Ê, Ë, À, Â, Î, Ï, Ô, Ù, Û, Ç…).
 *  - Les accents sont facultatifs pour deviner : « ECOLE » et « ÉCOLE » sont
 *    la même proposition (comparaison sur la lettre de base).
 *  - Les mots du jeu sont affichés avec leurs vrais accents dès qu'une lettre est
 *    trouvée (case verte) ou que le mot est découvert.
 *  - Œ et Æ sont saisis OE / AE pour garder une lettre = une case.
 *
 * Les caractères accentués sont écrits en séquences \u{....} pour que ce fichier
 * reste correct même si un éditeur ré-encode mal le source.
 */

/** Lettre accentuée => lettre de base (correspondance 1 pour 1, la longueur ne change jamais). */
const LETTRES_ACCENTS = [
    "\u{00C0}" => 'A', "\u{00C2}" => 'A', "\u{00C4}" => 'A',                       // À Â Ä
    "\u{00C9}" => 'E', "\u{00C8}" => 'E', "\u{00CA}" => 'E', "\u{00CB}" => 'E',    // É È Ê Ë
    "\u{00CE}" => 'I', "\u{00CF}" => 'I',                                          // Î Ï
    "\u{00D4}" => 'O', "\u{00D6}" => 'O',                                          // Ô Ö
    "\u{00D9}" => 'U', "\u{00DB}" => 'U', "\u{00DC}" => 'U',                       // Ù Û Ü
    "\u{00C7}" => 'C',                                                             // Ç
    "\u{0178}" => 'Y',                                                             // Ÿ
];

/** Expression régulière d'un mot jouable : 2 à 30 lettres (accentuées ou non) ou tirets. */
const MOT_REGEX = '/^[A-Z\x{00C0}\x{00C2}\x{00C4}\x{00C9}\x{00C8}\x{00CA}\x{00CB}\x{00CE}\x{00CF}\x{00D4}\x{00D6}\x{00D9}\x{00DB}\x{00DC}\x{00C7}\x{0178}\-]{2,30}$/u';

/**
 * Recompose les accents « décomposés » (lettre + diacritique séparé, fréquent sur
 * certains claviers mobiles) en caractères accentués simples, sans dépendre de l'extension intl.
 */
function _recomposer_accents(string $s): string {
    if (class_exists('Normalizer')) {
        $n = Normalizer::normalize($s, Normalizer::FORM_C);
        if ($n !== false) return $n;
    }
    static $table = null;
    if ($table === null) {
        $table = [];
        $combos = [
            "\u{0301}" => ['E' => "\u{00C9}"],                                                                    // accent aigu
            "\u{0300}" => ['A' => "\u{00C0}", 'E' => "\u{00C8}", 'U' => "\u{00D9}"],                              // accent grave
            "\u{0302}" => ['A' => "\u{00C2}", 'E' => "\u{00CA}", 'I' => "\u{00CE}", 'O' => "\u{00D4}", 'U' => "\u{00DB}"], // circonflexe
            "\u{0308}" => ['A' => "\u{00C4}", 'E' => "\u{00CB}", 'I' => "\u{00CF}", 'O' => "\u{00D6}", 'U' => "\u{00DC}", 'Y' => "\u{0178}"], // tréma
            "\u{0327}" => ['C' => "\u{00C7}"],                                                                    // cédille
        ];
        foreach ($combos as $marque => $lettres) {
            foreach ($lettres as $base => $compose) {
                $table[$base . $marque] = $compose;
            }
        }
    }
    return strtr($s, $table);
}

/**
 * Prépare une saisie ou un mot : espaces retirés, accents recomposés,
 * MAJUSCULES, ligatures éclatées (Œ => OE, Æ => AE).
 */
function normaliser_saisie(string $s): string {
    $s = mb_strtoupper(trim($s), 'UTF-8');   // majuscules d'abord : « e + accent » → « E + accent »
    $s = _recomposer_accents($s);
    return strtr($s, ["\u{0152}" => 'OE', "\u{00C6}" => 'AE']);
}

/** Lettre de base d'une chaîne : « ÉLÉPHANT » => « ELEPHANT » (même longueur). */
function lettre_base(string $s): string {
    return strtr($s, LETTRES_ACCENTS);
}

/** Vrai si $mot (déjà normalisé) est une proposition jouable. */
function mot_valide(string $mot): bool {
    return (bool) preg_match(MOT_REGEX, $mot);
}
