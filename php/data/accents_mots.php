<?php
/**
 * accents_mots.php — Orthographe correcte (avec accents) des mots du jeu.
 *
 * Format : 'MOT SANS ACCENT' => 'MOT AVEC ACCENTS'
 * Règle absolue : le mot accentué a EXACTEMENT les mêmes lettres de base que la clé
 * (1 lettre = 1 case, la longueur ne change jamais). C'est vérifié par scratch/test_accents.php.
 *
 * Utilisé une seule fois par la migration `accents_mots_20261015` (php/config.php).
 */
return [
    // ── Villes, communes, régions de Côte d'Ivoire ──
    'ADJAME' => 'ADJAMÉ', 'BOUAKE' => 'BOUAKÉ', 'ODIENNE' => 'ODIENNÉ',
    'FERKESSEDOUGOU' => 'FERKESSÉDOUGOU', 'SEGUELA' => 'SÉGUÉLA', 'TIASSALE' => 'TIASSALÉ',
    'ADZOPE' => 'ADZOPÉ', 'FERKE' => 'FERKÉ', 'TIEBISSOU' => 'TIÉBISSOU', 'BOUAFLE' => 'BOUAFLÉ',
    'DANANE' => 'DANANÉ', 'LOGOUALE' => 'LOGOUALÉ', 'OUME' => 'OUMÉ', 'DIDIEVI' => 'DIDIÉVI',
    'AZAGUIE' => 'AZAGUIÉ', 'ALEPE' => 'ALÉPÉ', 'MEAGUI' => 'MÉAGUI', 'MARAHOUE' => 'MARAHOUÉ',
    'PORT-BOUET' => 'PORT-BOUËT', 'SAN-PEDRO' => 'SAN-PÉDRO', 'COMOE' => 'COMOÉ', 'REGION' => 'RÉGION',

    // ── Peuples, culture, gastronomie, personnalités ──
    'BAOULE' => 'BAOULÉ', 'BETE' => 'BÊTE', 'GUERE' => 'GUÉRÉ', 'MALINKE' => 'MALINKÉ',
    'ATTIE' => 'ATTIÉ', 'AKYE' => 'AKYÉ', 'WOBE' => 'WOBÉ', 'MANDE' => 'MANDÉ',
    'ATTIEKE' => 'ATTIÉKÉ', 'KEDJENOU' => 'KÉDJÉNOU', 'COUPEDECALE' => 'COUPÉDÉCALÉ', 'DECALE' => 'DÉCALÉ',
    'DEGUE' => 'DÈGUÈ', 'FETICHE' => 'FÉTICHE', 'ATTECOUBE' => 'ATTÉCOUBÉ', 'DJEMBE' => 'DJEMBÉ',
    'DIABATE' => 'DIABATÉ', 'TRAORE' => 'TRAORÉ', 'KONE' => 'KONÉ', 'KOUYATE' => 'KOUYATÉ',
    'BEDIE' => 'BÉDIÉ', 'HOUPHOUET' => 'HOUPHOUËT', 'PETROLE' => 'PÉTROLE', 'PREFET' => 'PRÉFET',
    'CACAOYERE' => 'CACAOYÈRE', 'CAFEIERE' => 'CAFÉIÈRE', 'CAFEIER' => 'CAFÉIER', 'HEVEA' => 'HÉVÉA',
    'POTAMOCHERE' => 'POTAMOCHÈRE',

    // ── Nature, animaux ──
    'FORET' => 'FORÊT', 'ILE' => 'ÎLE', 'DESERT' => 'DÉSERT', 'RIVIERE' => 'RIVIÈRE', 'VALLEE' => 'VALLÉE',
    'MINERAL' => 'MINÉRAL', 'ROSEE' => 'ROSÉE', 'ETOILE' => 'ÉTOILE', 'ECLAIR' => 'ÉCLAIR', 'ETANG' => 'ÉTANG',
    'PANTHERE' => 'PANTHÈRE', 'ARAIGNEE' => 'ARAIGNÉE', 'MEDUSE' => 'MÉDUSE', 'ANE' => 'ÂNE', 'HYENE' => 'HYÈNE',
    'LEOPARD' => 'LÉOPARD', 'GUEPARD' => 'GUÉPARD', 'CHIMPANZE' => 'CHIMPANZÉ', 'PELICAN' => 'PÉLICAN',
    'HERISSON' => 'HÉRISSON', 'ECUREUIL' => 'ÉCUREUIL', 'ZEBRE' => 'ZÈBRE', 'RHINOCEROS' => 'RHINOCÉROS',
    'TROPHEE' => 'TROPHÉE', 'CHEVRE' => 'CHÈVRE', 'HUITRE' => 'HUÎTRE', 'CANOE' => 'CANOË',

    // ── Nourriture ──
    'CAFE' => 'CAFÉ', 'MAIS' => 'MAÏS', 'THE' => 'THÉ', 'BIERE' => 'BIÈRE', 'FEVE' => 'FÈVE',
    'EPINARD' => 'ÉPINARD', 'CELERI' => 'CÉLERI', 'PECHE' => 'PÊCHE', 'MURE' => 'MÛRE', 'CREME' => 'CRÈME',
    'CREPE' => 'CRÊPE', 'GATEAU' => 'GÂTEAU', 'RAGOUT' => 'RAGOÛT', 'COMPRIME' => 'COMPRIMÉ',

    // ── Corps, maison, objets ──
    'TETE' => 'TÊTE', 'EPAULE' => 'ÉPAULE', 'ARTERE' => 'ARTÈRE', 'LEVRE' => 'LÈVRE', 'ETAGERE' => 'ÉTAGÈRE',
    'CHEMINEE' => 'CHEMINÉE', 'CANAPE' => 'CANAPÉ', 'TELEVISION' => 'TÉLÉVISION', 'POELE' => 'POÊLE',
    'ECHELLE' => 'ÉCHELLE', 'ECHARPE' => 'ÉCHARPE', 'CHAINE' => 'CHAÎNE', 'BOITE' => 'BOÎTE', 'POIGNEE' => 'POIGNÉE',
    'CUILLERE' => 'CUILLÈRE', 'SERPILLIERE' => 'SERPILLIÈRE', 'GOUTTIERE' => 'GOUTTIÈRE', 'RESERVOIR' => 'RÉSERVOIR',
    'GENERATEUR' => 'GÉNÉRATEUR', 'BETON' => 'BÉTON', 'CABLE' => 'CÂBLE', 'IMPERMEABLES' => 'IMPERMÉABLES',
    'FENETRE' => 'FENÊTRE', 'ALLEE' => 'ALLÉE', 'CARRE' => 'CARRÉ', 'SPHERE' => 'SPHÈRE', 'CONE' => 'CÔNE',
    'CHEMISE' => 'CHEMISE',

    // ── Lieux, transports ──
    'HOPITAL' => 'HÔPITAL', 'EGLISE' => 'ÉGLISE', 'MOSQUEE' => 'MOSQUÉE', 'HOTEL' => 'HÔTEL',
    'BIBLIOTHEQUE' => 'BIBLIOTHÈQUE', 'MUSEE' => 'MUSÉE', 'THEATRE' => 'THÉÂTRE', 'CINEMA' => 'CINÉMA',
    'EPICERIE' => 'ÉPICERIE', 'SUPERMARCHE' => 'SUPERMARCHÉ', 'AEROPORT' => 'AÉROPORT', 'MARCHE' => 'MARCHÉ',
    'FRONTIERE' => 'FRONTIÈRE', 'METRO' => 'MÉTRO', 'HELICOPTERE' => 'HÉLICOPTÈRE', 'FUSEE' => 'FUSÉE',
    'VELO' => 'VÉLO', 'DEPARTEMENT' => 'DÉPARTEMENT',

    // ── Personnes, métiers, école ──
    'PERE' => 'PÈRE', 'MERE' => 'MÈRE', 'FRERE' => 'FRÈRE', 'BEBE' => 'BÉBÉ', 'GARCON' => 'GARÇON',
    'COLLEGUE' => 'COLLÈGUE', 'EMPLOYE' => 'EMPLOYÉ', 'MEDECIN' => 'MÉDECIN', 'ELEVE' => 'ÉLÈVE',
    'MECANICIEN' => 'MÉCANICIEN', 'ELECTRICIEN' => 'ÉLECTRICIEN', 'ECRIVAIN' => 'ÉCRIVAIN', 'POETE' => 'POÈTE',
    'PECHEUR' => 'PÊCHEUR', 'INGENIEUR' => 'INGÉNIEUR', 'MACON' => 'MAÇON', 'METIER' => 'MÉTIER',
    'REUNION' => 'RÉUNION', 'LECON' => 'LEÇON', 'REGLE' => 'RÈGLE', 'ECOLE' => 'ÉCOLE',
    'UNIVERSITE' => 'UNIVERSITÉ', 'ETUDIANT' => 'ÉTUDIANT', 'DIPLOME' => 'DIPLÔME',

    // ── Santé, sport, loisirs ──
    'MEDICAMENT' => 'MÉDICAMENT', 'THERMOMETRE' => 'THERMOMÈTRE', 'OPERATION' => 'OPÉRATION',
    'GUERISON' => 'GUÉRISON', 'SANTE' => 'SANTÉ', 'FIEVRE' => 'FIÈVRE', 'DIABETE' => 'DIABÈTE',
    'EQUIPE' => 'ÉQUIPE', 'DEFAITE' => 'DÉFAITE', 'DEFENSEUR' => 'DÉFENSEUR', 'DEFENSE' => 'DÉFENSE',
    'ECRITURE' => 'ÉCRITURE', 'POESIE' => 'POÉSIE', 'ECHECS' => 'ÉCHECS', 'PLONGEE' => 'PLONGÉE',
    'RANDONNEE' => 'RANDONNÉE', 'CEREMONIE' => 'CÉRÉMONIE', 'BAPTEME' => 'BAPTÊME', 'PRIERE' => 'PRIÈRE',

    // ── Temps ──
    'EPOQUE' => 'ÉPOQUE', 'SIECLE' => 'SIÈCLE', 'FEVRIER' => 'FÉVRIER', 'AOUT' => 'AOÛT',
    'DECEMBRE' => 'DÉCEMBRE', 'DEBUT' => 'DÉBUT', 'ANNEE' => 'ANNÉE', 'BIENTOT' => 'BIENTÔT', 'PASSE' => 'PASSÉ',

    // ── Sciences, technologie ──
    'ECRAN' => 'ÉCRAN', 'RESEAU' => 'RÉSEAU', 'TELEPHONE' => 'TÉLÉPHONE', 'ELEPHANT' => 'ÉLÉPHANT',
    'DONNEES' => 'DONNÉES', 'DONNEE' => 'DONNÉE', 'SECURITE' => 'SÉCURITÉ', 'MEMOIRE' => 'MÉMOIRE',
    'GEOGRAPHIE' => 'GÉOGRAPHIE', 'ECONOMIE' => 'ÉCONOMIE', 'EXPERIENCE' => 'EXPÉRIENCE', 'TELESCOPE' => 'TÉLESCOPE',
    'EQUATION' => 'ÉQUATION', 'GEOMETRIE' => 'GÉOMÉTRIE', 'ALGEBRE' => 'ALGÈBRE', 'ACCELERATION' => 'ACCÉLÉRATION',
    'TEMPERATURE' => 'TEMPÉRATURE', 'ELECTRICITE' => 'ÉLECTRICITÉ', 'GRAVITE' => 'GRAVITÉ', 'ELEMENT' => 'ÉLÉMENT',
    'REACTION' => 'RÉACTION', 'METAL' => 'MÉTAL', 'MAGNETISME' => 'MAGNÉTISME', 'MOLECULE' => 'MOLÉCULE',

    // ── Sentiments, idées, société ──
    'COLERE' => 'COLÈRE', 'HAINE' => 'HAÏNE', 'FIERTE' => 'FIERTÉ', 'HONNETETE' => 'HONNÊTETÉ',
    'LOYAUTE' => 'LOYAUTÉ', 'DESESPOIR' => 'DÉSESPOIR', 'DIGNITE' => 'DIGNITÉ', 'LIBERTE' => 'LIBERTÉ',
    'EGALITE' => 'ÉGALITÉ', 'AMITIE' => 'AMITIÉ', 'PAUVRETE' => 'PAUVRETÉ', 'CREDIT' => 'CRÉDIT',
    'IMPOT' => 'IMPÔT', 'PROBLEME' => 'PROBLÈME', 'REPONSE' => 'RÉPONSE', 'IDEE' => 'IDÉE',
    'RESULTAT' => 'RÉSULTAT', 'SUCCES' => 'SUCCÈS', 'REVE' => 'RÊVE', 'CREATIVITE' => 'CRÉATIVITÉ',
    'LUMIERE' => 'LUMIÈRE', 'ECOLOGIE' => 'ÉCOLOGIE', 'ENERGIE' => 'ÉNERGIE', 'ELECTION' => 'ÉLECTION',
    'SOLIDARITE' => 'SOLIDARITÉ', 'PROGRES' => 'PROGRÈS', 'DEVELOPPEMENT' => 'DÉVELOPPEMENT',
    'MODERNITE' => 'MODERNITÉ', 'HERITAGE' => 'HÉRITAGE', 'SOCIETE' => 'SOCIÉTÉ', 'COMMUNAUTE' => 'COMMUNAUTÉ',
    'MINORITE' => 'MINORITÉ', 'MAJORITE' => 'MAJORITÉ', 'ETAPE' => 'ÉTAPE',

    // ── Adjectifs et verbes ──
    'ARGENTE' => 'ARGENTÉ', 'DORE' => 'DORÉ', 'LEGER' => 'LÉGER', 'EPAIS' => 'ÉPAIS', 'ETROIT' => 'ÉTROIT',
    'ECOUTER' => 'ÉCOUTER', 'ECRIRE' => 'ÉCRIRE', 'DETESTER' => 'DÉTESTER', 'DEMOLIR' => 'DÉMOLIR',
    'REPARER' => 'RÉPARER', 'PREPARER' => 'PRÉPARER', 'ECHANGER' => 'ÉCHANGER', 'REUSSIR' => 'RÉUSSIR',
    'ECHOUER' => 'ÉCHOUER', 'PROTEGER' => 'PROTÉGER', 'DEFENDRE' => 'DÉFENDRE', 'ARRETER' => 'ARRÊTER',
];
