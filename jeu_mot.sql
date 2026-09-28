-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 23 juin 2026 à 01:27
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `jeu_mot`
--

-- --------------------------------------------------------

--
-- Structure de la table `mots`
--

CREATE TABLE `mots` (
  `id` int(11) NOT NULL,
  `mot` varchar(50) NOT NULL,
  `ordre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `mots`
--

INSERT INTO `mots` (`id`, `mot`, `ordre`) VALUES
(1, 'POMME', 5),
(2, 'BANANE', 132),
(3, 'ANIMAL', 235),
(4, 'CAMION', 223),
(5, 'MAISON', 164),
(6, 'ORDINATEUR', 214),
(7, 'TABLE', 61),
(8, 'SOURIS', 189),
(9, 'VOITURE', 41),
(10, 'AVION', 109),
(11, 'BOUTEILLE', 3),
(12, 'JARDIN', 99),
(13, 'MONTAGNE', 81),
(14, 'ABOBO', 140),
(15, 'ADJAME', 220),
(16, 'YOPOUGON', 138),
(17, 'KOUMASSI', 78),
(18, 'TREICHVILLE', 39),
(19, 'COCODY', 156),
(20, 'MARCORY', 237),
(21, 'BINGERVILLE', 141),
(22, 'ANYAMA', 67),
(23, 'SONGON', 170),
(24, 'BASSAM', 167),
(25, 'BOUAKE', 82),
(26, 'DALOA', 225),
(27, 'KORHOGO', 62),
(28, 'MAN', 181),
(29, 'ABENGOUROU', 13),
(30, 'BONDOUKOU', 195),
(31, 'ODIENNE', 24),
(32, 'DIVO', 233),
(33, 'GAGNOA', 84),
(34, 'ABOISSO', 59),
(35, 'DIMBOKRO', 42),
(36, 'FERKESSEDOUGOU', 30),
(37, 'TOUBA', 9),
(38, 'SEGUELA', 175),
(39, 'ATTIEKE', 182),
(40, 'ALLOCO', 142),
(41, 'FOUTOU', 183),
(42, 'KEDJENOU', 21),
(43, 'GARBA', 8),
(44, 'PLACALI', 207),
(45, 'ALOCO', 68),
(46, 'SAUCE', 1),
(47, 'THIEBOUDIENNE', 6),
(48, 'POISSON', 28),
(49, 'MAQUIS', 113),
(50, 'GBAKA', 60),
(51, 'WORO', 209),
(52, 'BENSIMON', 126),
(53, 'NOUCHI', 31),
(54, 'DJOULA', 244),
(55, 'BAOULE', 90),
(56, 'BETE', 70),
(57, 'SENUFO', 75),
(58, 'AGNI', 205),
(59, 'DIDA', 47),
(60, 'GOURO', 111),
(61, 'GUERE', 234),
(62, 'YACOUBA', 34),
(63, 'LOBI', 200),
(64, 'MALINKE', 224),
(65, 'KROUMEN', 242),
(66, 'ABBEY', 22),
(67, 'ABIDJAN', 118),
(68, 'LAGUNE', 92),
(69, 'SAVANE', 146),
(70, 'FORET', 227),
(71, 'CACAO', 143),
(72, 'CAFE', 79),
(73, 'PALME', 35),
(74, 'CAOUTCHOUC', 120),
(75, 'ANANAS', 53),
(76, 'IGNAME', 153),
(77, 'MAIS', 148),
(78, 'MANIOC', 51),
(79, 'PAPAYE', 52),
(80, 'GOYAVE', 108),
(81, 'PLANTAIN', 192),
(82, 'CITRON', 134),
(83, 'FOOTBALL', 117),
(84, 'AMICAL', 201),
(85, 'STADE', 166),
(86, 'BALLON', 11),
(87, 'ARBITRE', 7),
(88, 'VICTOIRE', 2),
(89, 'CHAMPION', 236),
(90, 'COUPE', 159),
(91, 'TROPHEE', 168),
(92, 'ELEPHANT', 114),
(93, 'INTERNET', 95),
(94, 'TELEPHONE', 171),
(95, 'ECRAN', 63),
(96, 'CLAVIER', 66),
(97, 'RESEAU', 157),
(98, 'SERVEUR', 121),
(99, 'CODE', 139),
(100, 'PROJET', 101),
(101, 'JAVASCRIPT', 133),
(102, 'PROGRAMME', 115),
(103, 'LOGICIEL', 198),
(104, 'DONNEES', 155),
(105, 'SECURITE', 228),
(106, 'APPLICATION', 107),
(107, 'MARCHE', 186),
(108, 'BOUTIQUE', 105),
(109, 'MAGASIN', 25),
(110, 'QUARTIER', 245),
(111, 'VILLAGE', 127),
(112, 'COMMUNE', 221),
(113, 'DISTRICT', 194),
(114, 'REGION', 103),
(115, 'SOLEIL', 247),
(116, 'PLUIE', 77),
(117, 'SAISON', 14),
(118, 'CHALEUR', 10),
(119, 'HARMATTAN', 16),
(120, 'OCEAN', 36),
(121, 'FLEUVE', 151),
(122, 'RIVIERE', 211),
(123, 'BAIE', 83),
(124, 'PLAGE', 89),
(125, 'AMOUR', 215),
(126, 'FAMILLE', 38),
(127, 'ENFANT', 49),
(128, 'PARENT', 136),
(129, 'FRERE', 80),
(130, 'SOEUR', 46),
(131, 'AMI', 187),
(132, 'VOISIN', 88),
(133, 'TRAVAIL', 160),
(134, 'ECOLE', 56),
(135, 'UNIVERSITE', 55),
(136, 'ETUDIANT', 119),
(137, 'PROFESSEUR', 213),
(138, 'CLASSE', 193),
(139, 'DIPLOME', 98),
(140, 'MUSIQUE', 226),
(141, 'ZOUGLOU', 29),
(142, 'COUPEDECALE', 208),
(143, 'MAPOUKA', 20),
(144, 'GBEGBE', 150),
(145, 'GBONHI', 37),
(146, 'DJASSA', 196),
(147, 'BITCHO', 163),
(148, 'DOGO', 18),
(149, 'DIOULA', 33),
(150, 'MANGER', 104),
(151, 'FROMAGE', 26),
(152, 'PLATEAU', 19),
(153, 'BANQUE', 243),
(167, 'ATTINGON', 144),
(168, 'SOUBRE', 64),
(169, 'ISSIA', 147),
(170, 'TIASSALE', 71),
(171, 'AGBOVILLE', 174),
(172, 'ADZOPE', 172),
(173, 'BONGOUANOU', 102),
(174, 'DUEKOUE', 50),
(175, 'GUIGLO', 158),
(176, 'BLOROFLA', 188),
(177, 'YAMOUSSOUKRO', 241),
(178, 'FERKE', 72),
(179, 'GNANGNAN', 202),
(180, 'BRAISER', 58),
(181, 'KOLA', 199),
(182, 'GRAIN', 87),
(183, 'HARICOT', 128),
(184, 'GOMBO', 122),
(185, 'ATTIE', 15),
(186, 'AKYE', 135),
(187, 'ABRON', 231),
(188, 'KOULANGO', 184),
(189, 'NAFANA', 43),
(190, 'WOBE', 137),
(191, 'TAXI', 106),
(192, 'BATEAU', 152),
(193, 'FRONTIERE', 206),
(194, 'COLLINE', 73),
(195, 'VALLEE', 27),
(196, 'MANGROVE', 112),
(197, 'BROUSSE', 57),
(198, 'CAMPAGNE', 204),
(199, 'CHAMP', 100),
(200, 'CHEF', 203),
(201, 'FETE', 197),
(202, 'MARIAGE', 149),
(203, 'BAPTEME', 177),
(204, 'DEUIL', 212),
(205, 'CEREMONIE', 32),
(206, 'TRADITION', 246),
(207, 'FANION', 97),
(208, 'ATTAQUE', 96),
(209, 'DEFENSE', 229),
(210, 'GARDIEN', 45),
(211, 'PENALTY', 54),
(212, 'CORNER', 161),
(213, 'AFROBEAT', 179),
(214, 'BIKUTSI', 178),
(215, 'GRIPPE', 125),
(216, 'CONCERT', 86),
(217, 'ARTISTE', 93),
(218, 'CHANSON', 12),
(219, 'RYTHME', 191),
(220, 'ANTENNE', 4),
(221, 'SIGNAL', 131),
(222, 'BATTERIE', 239),
(223, 'CHARGEUR', 238),
(224, 'TABLETTE', 218),
(225, 'LION', 145),
(226, 'PANTHERE', 124),
(227, 'SINGE', 190),
(228, 'CROCODILE', 91),
(229, 'AIGLE', 176),
(230, 'PERROQUET', 123),
(231, 'PYTHON', 85),
(232, 'GAZELLE', 94),
(233, 'BUFFLE', 17),
(234, 'HIPPOPOTAME', 217),
(235, 'PIROGUE', 65),
(236, 'CALAO', 216),
(237, 'ABEILLE', 116),
(238, 'FOURMI', 248),
(239, 'CUISINE', 69),
(240, 'CHAMBRE', 162),
(241, 'SALON', 130),
(242, 'SCHOOL', 185),
(243, 'BUREAU', 48),
(244, 'USINE', 173),
(245, 'FERME', 23),
(246, 'HOPITAL', 40),
(247, 'EGLISE', 154),
(248, 'MOSQUE', 210),
(249, 'MOTO', 74),
(250, 'ORANGE', 44),
(251, 'MANGUE', 222),
(252, 'NOIX', 230),
(253, 'CHAISE', 219),
(254, 'LIT', 165),
(255, 'PORTE', 232),
(256, 'FENETRE', 110),
(257, 'ROUTE', 180),
(258, 'PONT', 76),
(259, 'CARREFOUR', 129),
(260, 'IMMEUBLE', 169),
(261, 'MAIRIE', 240);

-- --------------------------------------------------------

--
-- Structure de la table `mots_du_jour`
--

CREATE TABLE `mots_du_jour` (
  `id` int(11) NOT NULL,
  `date_jour` date DEFAULT NULL,
  `mot` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `mots_du_jour`
--

INSERT INTO `mots_du_jour` (`id`, `date_jour`, `mot`) VALUES
(39, '2026-05-05', 'manger'),
(40, '2026-05-06', 'fromage'),
(41, '2026-05-07', 'abobo'),
(42, '2026-05-08', 'plateau'),
(43, '2026-05-09', 'nouchi'),
(44, '2026-05-10', 'attieke'),
(45, '2026-05-11', 'gbaka'),
(46, '2026-05-12', 'maquis'),
(47, '2026-05-13', 'banque'),
(48, '2026-05-14', 'soleil'),
(49, '2026-05-15', 'cacao'),
(50, '2026-05-16', 'daloa'),
(51, '2026-05-17', 'yopougon'),
(52, '2026-05-18', 'koumassi'),
(53, '2026-05-19', 'adjame'),
(54, '2026-05-20', 'bouake'),
(55, '2026-05-21', 'football'),
(56, '2026-05-22', 'internet'),
(57, '2026-05-23', 'javascript'),
(58, '2026-05-24', 'serveur'),
(59, '2026-05-25', 'clavier'),
(60, '2026-05-26', 'souris'),
(61, '2026-05-27', 'ecran'),
(62, '2026-05-28', 'reseau'),
(63, '2026-05-29', 'code'),
(64, '2026-05-30', 'projet'),
(65, '2026-05-31', 'victoire'),
(66, '2026-06-22', 'BINGERVILLE');

-- --------------------------------------------------------

--
-- Structure de la table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_jour` date DEFAULT NULL,
  `tentatives` int(11) DEFAULT NULL,
  `trouve` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `scores`
--

INSERT INTO `scores` (`id`, `user_id`, `date_jour`, `tentatives`, `trouve`, `created_at`) VALUES
(1, 1, '2026-05-05', 8, 1, '2026-05-05 13:30:35'),
(2, 1, '2026-05-07', 3, 1, '2026-05-07 09:22:03'),
(3, 2, '2026-05-07', 4, 1, '2026-05-07 09:59:55'),
(4, 1, '2026-06-22', 2, 1, '2026-06-22 23:15:42');

-- --------------------------------------------------------

--
-- Structure de la table `tentatives`
--

CREATE TABLE `tentatives` (
  `id` int(11) NOT NULL,
  `mot` varchar(100) NOT NULL,
  `numero_essai` int(11) NOT NULL,
  `date_jour` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tentatives`
--

INSERT INTO `tentatives` (`id`, `mot`, `numero_essai`, `date_jour`) VALUES
(1, 'CHANTEUR', 21, '2026-02-17');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `is_admin`) VALUES
(1, 'David', 'daatsey24@gmail.com', '$2y$10$5XOW3HxsCabGHEd.13HayuIF3MnLHVXaLDNvaHTeuseXdpB2IaTyO', '2026-05-05 12:14:31', 1),
(2, 'Yannis', 'atseyannis@gmail.com', '$2y$10$lxBFM33gE/aWDGOhpgllue8wMObJd7P0YuQU5/5yW2Hm3C6gO7KPq', '2026-05-07 09:22:48', 0),
(3, 'Test', 'test@gmail.com', '$2y$10$IH7vhiOKiJYq6cOPRmvUFubPG.fLqyf3NzcSfHLrUYXKyDn/C3FUC', '2026-06-22 23:09:04', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `mots`
--
ALTER TABLE `mots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mot` (`mot`),
  ADD KEY `idx_mots_ordre` (`ordre`);

--
-- Index pour la table `mots_du_jour`
--
ALTER TABLE `mots_du_jour`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date_jour` (`date_jour`);

--
-- Index pour la table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scores_user_date` (`user_id`,`date_jour`);

--
-- Index pour la table `tentatives`
--
ALTER TABLE `tentatives`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `mots`
--
ALTER TABLE `mots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT pour la table `mots_du_jour`
--
ALTER TABLE `mots_du_jour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `tentatives`
--
ALTER TABLE `tentatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
