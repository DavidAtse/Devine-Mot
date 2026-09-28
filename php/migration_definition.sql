-- ============================================================
-- Migration : ajouter la colonne 'definition' à la table 'mots'
-- À exécuter UNE SEULE FOIS via phpMyAdmin ou la CLI MySQL
-- ============================================================
ALTER TABLE `mots`
    ADD COLUMN `definition` TEXT NULL DEFAULT NULL AFTER `ordre`;
