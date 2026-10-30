SET DEFINE OFF;
-- =====================================
-- creation des tables
-- =====================================

-- espece
CREATE TABLE espece (
     id_espece CHAR(4),
     nom_latin VARCHAR(50) NOT NULL,
     nom_usuel VARCHAR(50) NOT NULL,
     est_menace NUMBER(1) NOT NULL,
     CONSTRAINT pk_espece PRIMARY KEY (id_espece),
     CONSTRAINT ck_espece_menace CHECK (est_menace IN (0,1))
);

-- zoo
CREATE TABLE zoo (
     id_zoo CHAR(4),
     nom_zoo VARCHAR(50) NOT NULL,
     pays_zoo VARCHAR(50) NOT NULL,
     ville_zoo VARCHAR(50) NOT NULL,
     CONSTRAINT pk_zoo PRIMARY KEY (id_zoo)
);

-- visiteur
CREATE TABLE visiteur (
     id_visiteur CHAR(4),
     nom_visiteur VARCHAR(50) NOT NULL,
     prenom_visiteur VARCHAR(50) NOT NULL,
     CONSTRAINT pk_visiteur PRIMARY KEY (id_visiteur)
);

-- niveau_contribution
CREATE TABLE niveau_contribution (
     id_niveau CHAR(4),
     nom_niveau VARCHAR(50) NOT NULL,
     CONSTRAINT pk_niveau PRIMARY KEY (id_niveau)
);

-- prestation
CREATE TABLE prestation (
     id_prestation CHAR(4),
     nom_prestation VARCHAR(50) NOT NULL,
     CONSTRAINT pk_prestation PRIMARY KEY (id_prestation)
);

-- prestataire
CREATE TABLE prestataire (
     id_prestataire CHAR(4),
     nom_prestataire VARCHAR(50) NOT NULL,
     CONSTRAINT pk_prestataire PRIMARY KEY (id_prestataire)
);

-- zone
CREATE TABLE zone (
     id_zone CHAR(4),
     nom_zone VARCHAR(50) NOT NULL,
     CONSTRAINT pk_zone PRIMARY KEY (id_zone)
);

CREATE TABLE personnel (
     id_personnel CHAR(4),
     nom_personnel VARCHAR(50) NOT NULL,
     prenom_personnel VARCHAR(50) NOT NULL,
     mot_de_passe VARCHAR(255) NOT NULL,
     date_entree DATE NOT NULL,
     fonction VARCHAR(50) NOT NULL,
     salaire NUMBER NOT NULL,
     id_personnel_remplacant CHAR(4),
     id_zone CHAR(4),
     id_personnel_chef CHAR(4),
     CONSTRAINT pk_personnel PRIMARY KEY (id_personnel),
     CONSTRAINT fk_personnel_remplacant FOREIGN KEY (id_personnel_remplacant)
     REFERENCES personnel(id_personnel) ON DELETE SET NULL,
     CONSTRAINT fk_personnel_zone FOREIGN KEY (id_zone)
     REFERENCES zone(id_zone),
     CONSTRAINT fk_personnel_chef FOREIGN KEY (id_personnel_chef)
     REFERENCES personnel(id_personnel)
);

-- est_chef
CREATE TABLE est_chef (
     id_zone CHAR(4),
     id_personnel CHAR(4) NOT NULL,
     CONSTRAINT pk_est_chef PRIMARY KEY (id_zone),
     CONSTRAINT fk_est_chef_zone FOREIGN KEY (id_zone)
     REFERENCES zone(id_zone) ON DELETE CASCADE,
     CONSTRAINT fk_est_chef_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel) ON DELETE CASCADE
);

-- boutique
CREATE TABLE boutique (
     id_boutique CHAR(4),
     nom_boutique VARCHAR(50) NOT NULL,
     type_boutique VARCHAR(50) NOT NULL,
     id_zone CHAR(4) NOT NULL,
     id_personnel CHAR(4),
     CONSTRAINT pk_boutique PRIMARY KEY (id_boutique),
     CONSTRAINT fk_boutique_zone FOREIGN KEY (id_zone)
     REFERENCES zone(id_zone),
     CONSTRAINT fk_boutique_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel)
);

-- enclos
CREATE TABLE enclos (
     id_enclos CHAR(4),
     latitude NUMBER NOT NULL,
     longitude NUMBER NOT NULL,
     surface NUMBER NOT NULL,
     particularite VARCHAR(50),
     id_zone CHAR(4) NOT NULL,
     CONSTRAINT pk_enclos PRIMARY KEY (id_enclos),
     CONSTRAINT fk_enclos_zone FOREIGN KEY (id_zone)
     REFERENCES zone(id_zone)
);

-- reparation
CREATE TABLE reparation (
     id_reparation CHAR(4),
     nature_reparation VARCHAR(100) NOT NULL,
     date_reparation DATE NOT NULL,
     id_personnel CHAR(4),
     id_prestataire CHAR(4),
     id_enclos CHAR(4) NOT NULL,
     CONSTRAINT pk_reparation PRIMARY KEY (id_reparation),
     CONSTRAINT fk_reparation_prestataire FOREIGN KEY (id_prestataire)
     REFERENCES prestataire(id_prestataire) ON DELETE SET NULL,
     CONSTRAINT fk_reparation_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel) ON DELETE SET NULL,
     CONSTRAINT fk_reparation_enclos FOREIGN KEY (id_enclos)
     REFERENCES enclos(id_enclos)
);

-- animal
CREATE TABLE animal (
     id_rfid CHAR(10),
     nom_animal VARCHAR(50) NOT NULL,
     date_naissance DATE,
     poids NUMBER NOT NULL,
     regime_alimentaire VARCHAR(50) NOT NULL,
     id_espece CHAR(4) NOT NULL,
     id_zoo CHAR(4) NOT NULL,
     id_enclos CHAR(4) NOT NULL,
     CONSTRAINT pk_animal PRIMARY KEY (id_rfid),
     CONSTRAINT fk_animal_espece FOREIGN KEY (id_espece)
     REFERENCES espece(id_espece),
     CONSTRAINT fk_animal_zoo FOREIGN KEY (id_zoo)
     REFERENCES zoo(id_zoo),
     CONSTRAINT fk_animal_enclos FOREIGN KEY (id_enclos)
     REFERENCES enclos(id_enclos)
);

-- nourriture
CREATE TABLE nourriture (
     id_rfid CHAR(10),
     id_personnel CHAR(4),
     date_nourriture DATE NOT NULL,
     dose_nourriture NUMBER NOT NULL,
     CONSTRAINT pk_nourriture PRIMARY KEY (id_rfid, id_personnel, date_nourriture),
     CONSTRAINT fk_nourriture_animal FOREIGN KEY (id_rfid)
     REFERENCES animal(id_rfid) ON DELETE CASCADE,
     CONSTRAINT fk_nourriture_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel) ON DELETE CASCADE
);

-- soin
CREATE TABLE soin (
     id_soin CHAR(4),
     dose_soin NUMBER NOT NULL,
     date_soin DATE NOT NULL,
     id_personnel CHAR(4) NOT NULL,
     id_rfid CHAR(10) NOT NULL,
     CONSTRAINT pk_soin PRIMARY KEY (id_soin),
     CONSTRAINT fk_soin_animal FOREIGN KEY (id_rfid)
     REFERENCES animal(id_rfid) ON DELETE CASCADE,
     CONSTRAINT fk_soin_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel) ON DELETE CASCADE
);

-- cohabiter
CREATE TABLE cohabiter (
     id_espece_1 CHAR(4),
     id_espece_2 CHAR(4),
     CONSTRAINT pk_cohabiter PRIMARY KEY (id_espece_1, id_espece_2),
     CONSTRAINT fk_cohabiter_espece1 FOREIGN KEY (id_espece_1)
     REFERENCES espece(id_espece) ON DELETE CASCADE,
     CONSTRAINT fk_cohabiter_espece2 FOREIGN KEY (id_espece_2)
     REFERENCES espece(id_espece) ON DELETE CASCADE
);

-- parent
CREATE TABLE parent (
     id_rfid_enfant CHAR(10),
     id_rfid_parent CHAR(10),
     CONSTRAINT pk_parent PRIMARY KEY (id_rfid_enfant, id_rfid_parent),
     CONSTRAINT fk_parent_enfant FOREIGN KEY (id_rfid_enfant)
     REFERENCES animal(id_rfid) ON DELETE CASCADE,
     CONSTRAINT fk_parent_parent FOREIGN KEY (id_rfid_parent)
     REFERENCES animal(id_rfid) ON DELETE CASCADE
);

-- travailler
CREATE TABLE travailler (
     id_boutique CHAR(4),
     id_personnel CHAR(4),
     date_embauche DATE NOT NULL,
     date_fin_embauche DATE,
     CONSTRAINT pk_travailler PRIMARY KEY (id_boutique, id_personnel),
     CONSTRAINT fk_travailler_boutique FOREIGN KEY (id_boutique)
     REFERENCES boutique(id_boutique) ON DELETE CASCADE,
     CONSTRAINT fk_travailler_personnel FOREIGN KEY (id_personnel)
     REFERENCES personnel(id_personnel) ON DELETE CASCADE
);

-- ca
CREATE TABLE ca (
     id_boutique CHAR(4),
     date_ca DATE,
     chiffre_d_affaire NUMBER NOT NULL,
     CONSTRAINT pk_ca PRIMARY KEY (id_boutique, date_ca),
     CONSTRAINT fk_ca_boutique FOREIGN KEY (id_boutique)
     REFERENCES boutique(id_boutique) ON DELETE CASCADE
);

-- donne_acces
CREATE TABLE donne_acces (
     id_niveau CHAR(4),
     id_prestation CHAR(4),
     CONSTRAINT pk_donne_acces PRIMARY KEY (id_niveau, id_prestation),
     CONSTRAINT fk_donne_acces_niveau FOREIGN KEY (id_niveau)
     REFERENCES niveau_contribution(id_niveau) ON DELETE CASCADE,
     CONSTRAINT fk_donne_acces_prestation FOREIGN KEY (id_prestation)
     REFERENCES prestation(id_prestation) ON DELETE CASCADE
);

-- parrainer
CREATE TABLE parrainer (
     id_rfid CHAR(10),
     id_visiteur CHAR(4),
     id_niveau CHAR(4),
     CONSTRAINT pk_parrainer PRIMARY KEY (id_rfid, id_visiteur, id_niveau),
     CONSTRAINT fk_parrainer_animal FOREIGN KEY (id_rfid)
     REFERENCES animal(id_rfid) ON DELETE CASCADE,
     CONSTRAINT fk_parrainer_visiteur FOREIGN KEY (id_visiteur)
     REFERENCES visiteur(id_visiteur) ON DELETE CASCADE,
     CONSTRAINT fk_parrainer_niveau FOREIGN KEY (id_niveau)
     REFERENCES niveau_contribution(id_niveau) ON DELETE CASCADE
);


-- =====================================
-- insertions
-- =====================================

-- table espece
INSERT INTO espece VALUES ('E001', 'Panthera leo', 'Lion', 1);
INSERT INTO espece VALUES ('E002', 'Panthera tigris', 'Tigre', 1);
INSERT INTO espece VALUES ('E003', 'Ursus arctos', 'Ours brun', 0);
INSERT INTO espece VALUES ('E004', 'Aquila chrysaetos', 'Aigle royal', 0);
INSERT INTO espece VALUES ('E005', 'Elephas maximus', 'Elephant d''Asie', 1);
INSERT INTO espece VALUES ('E006', 'Gorilla gorilla', 'Gorille', 1);
INSERT INTO espece VALUES ('E007', 'Crocodylus niloticus', 'Crocodile du Nil', 1);
INSERT INTO espece VALUES ('E008', 'Giraffa camelopardalis', 'Girafe', 0);

-- table zoo
INSERT INTO zoo VALUES ('Z001', 'ZooLand Babentruk', 'France', 'Babentruk');
INSERT INTO zoo VALUES ('Z002', 'Safari Atlas', 'Maroc', 'Rabat');

-- table visiteur
INSERT INTO visiteur VALUES ('V001', 'Dupont', 'Lea');
INSERT INTO visiteur VALUES ('V002', 'Martin', 'Yanis');
INSERT INTO visiteur VALUES ('V003', 'Bernard', 'Sofia');
INSERT INTO visiteur VALUES ('V004', 'Petit', 'Nora');
INSERT INTO visiteur VALUES ('V005', 'Robert', 'Lina');
INSERT INTO visiteur VALUES ('V006', 'Garcia', 'Amel');

-- table niveau_contribution
INSERT INTO niveau_contribution VALUES ('N001', 'Bronze');
INSERT INTO niveau_contribution VALUES ('N002', 'Argent');
INSERT INTO niveau_contribution VALUES ('N003', 'Or');

-- table prestation
INSERT INTO prestation VALUES ('P001', 'Photo du filleul');
INSERT INTO prestation VALUES ('P002', 'Fond d''ecran');
INSERT INTO prestation VALUES ('P003', 'Visite gratuite');
INSERT INTO prestation VALUES ('P004', 'Badge souvenir');

-- table prestataire
INSERT INTO prestataire VALUES ('T001', 'TechnoCloture');
INSERT INTO prestataire VALUES ('T002', 'Batizoo Services');
INSERT INTO prestataire VALUES ('T003', 'ZooMaintenance');

-- table zone
INSERT INTO zone VALUES ('ZO01', 'Felins');
INSERT INTO zone VALUES ('ZO02', 'Ours');
INSERT INTO zone VALUES ('ZO03', 'Rapaces');
INSERT INTO zone VALUES ('ZO04', 'Savane');
INSERT INTO zone VALUES ('ZO05', 'Primates');

-- table personnel
-- soigneurs
-- chefs 
INSERT INTO personnel VALUES ('P001', 'Karim', 'Amine', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('10/01/2021','DD/MM/YYYY'), 'soigneur', 2200, NULL, 'ZO01', NULL); 
INSERT INTO personnel VALUES ('P002', 'Lina', 'Sara', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('05/09/2020','DD/MM/YYYY'), 'soigneur', 2250, NULL, 'ZO02', NULL); 
INSERT INTO personnel VALUES ('P003', 'Hugo', 'Nassim', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('11/03/2022','DD/MM/YYYY'), 'soigneur', 2300, NULL, 'ZO03', NULL); 
INSERT INTO personnel VALUES ('P004', 'Nina', 'Loubna', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('01/10/2022','DD/MM/YYYY'), 'soigneur', 2280, NULL, 'ZO04', NULL); 
INSERT INTO personnel VALUES ('P005', 'Sabrina', 'Ilhem', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('01/02/2024','DD/MM/YYYY'), 'soigneur', 2320, NULL, 'ZO05', NULL); 
-- soigneurs 
INSERT INTO personnel VALUES ('P006', 'Ali', 'Yacine', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('15/01/2022','DD/MM/YYYY'), 'soigneur', 1650, NULL, 'ZO01', 'P001'); 
INSERT INTO personnel VALUES ('P007', 'Nora', 'Ines', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('20/02/2023','DD/MM/YYYY'), 'soigneur', 1600, 'P006', 'ZO01', 'P001'); 
INSERT INTO personnel VALUES ('P008', 'Samir', 'Ilyes', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('12/05/2021','DD/MM/YYYY'), 'soigneur', 1680, 'P006', 'ZO02', 'P002'); 
INSERT INTO personnel VALUES ('P009', 'Rania', 'Meriem', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('10/01/2024','DD/MM/YYYY'), 'soigneur', 1620, NULL, 'ZO03', 'P003'); 
INSERT INTO personnel VALUES ('P010', 'Lea', 'Mouna', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('10/09/2024','DD/MM/YYYY'), 'soigneur', 1610, NULL, 'ZO04', 'P004'); 
INSERT INTO personnel VALUES ('P011', 'Amine', 'Riad', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('15/01/2025','DD/MM/YYYY'), 'soigneur', 1630, 'P010', 'ZO04', 'P004'); 
INSERT INTO personnel VALUES ('P012', 'Ilyas', 'Nassim','$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('11/11/2023','DD/MM/YYYY'), 'soigneur', 1660, 'P011', 'ZO05', 'P005'); 
-- vétérinaires 
INSERT INTO personnel VALUES ('P013', 'Maya', 'Sana', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('18/11/2020','DD/MM/YYYY'), 'veterinaire', 2500, NULL, 'ZO03', NULL); 
INSERT INTO personnel VALUES ('P014', 'Yasmine', 'Lila', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('20/02/2025','DD/MM/YYYY'), 'veterinaire', 2550, NULL, 'ZO05', NULL); 
-- techniques 
INSERT INTO personnel VALUES ('P015', 'Riad', 'Anis', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('03/04/2019','DD/MM/YYYY'), 'personnel_technique', 1850, NULL, 'ZO01', NULL); 
INSERT INTO personnel VALUES ('P016', 'Karima', 'Ines', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('05/06/2024','DD/MM/YYYY'), 'personnel_technique', 1810, NULL, 'ZO04', NULL); 
-- entretien 
INSERT INTO personnel VALUES ('P017', 'Leila', 'Aya', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('01/06/2023','DD/MM/YYYY'), 'entretien', 1500, NULL, 'ZO02', NULL); 
INSERT INTO personnel VALUES ('P018', 'Hani', 'Ziad', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('30/08/2023','DD/MM/YYYY'), 'entretien', 1490, NULL, 'ZO05', NULL);
-- boutique 
 INSERT INTO personnel VALUES ('P019', 'Adam', 'Rayane', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('21/07/2022','DD/MM/YYYY'), 'boutique', 1550, NULL, 'ZO01', NULL); 
 INSERT INTO personnel VALUES ('P020', 'Meriem', 'Camelia', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('09/08/2021','DD/MM/YYYY'), 'boutique', 1580, NULL, 'ZO03', NULL); 
 INSERT INTO personnel VALUES ('P021', 'Aya', 'Nesrine', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('08/01/2025','DD/MM/YYYY'), 'boutique', 1520, NULL, 'ZO05', NULL); 
 -- autres 
 INSERT INTO personnel VALUES ('P022', 'Omar', 'Hakim', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('14/02/2022','DD/MM/YYYY'), 'comptable', 2100, NULL, NULL, NULL); 
 INSERT INTO personnel VALUES ('P023', 'Siham', 'Imane', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('19/03/2021','DD/MM/YYYY'), 'gestionnaire', 2400, NULL, NULL, NULL); 
 INSERT INTO personnel VALUES ('P024', 'Nabil', 'Farid', '$2y$10$W5OQidCsDMEphbGz784HM.zxtd8uOJEZwvrZfCvPhuyOjw1S9e8VO', TO_DATE('01/01/2018','DD/MM/YYYY'), 'dirigeant', 3200, NULL, NULL, NULL);


-- table est_chef
INSERT INTO est_chef VALUES ('ZO01', 'P001');
INSERT INTO est_chef VALUES ('ZO02', 'P002');
INSERT INTO est_chef VALUES ('ZO03', 'P003');
INSERT INTO est_chef VALUES ('ZO04', 'P004');
INSERT INTO est_chef VALUES ('ZO05', 'P005');

-- table boutique
INSERT INTO boutique VALUES ('B001', 'Savane Shop', 'souvenir', 'ZO01', 'P019');
INSERT INTO boutique VALUES ('B002', 'Snack Aigle', 'snack', 'ZO03', 'P020');
INSERT INTO boutique VALUES ('B003', 'Primate Shop', 'souvenir', 'ZO05', 'P021');

-- table enclos
INSERT INTO enclos VALUES ('C001', 46.120001, 2.220001, 500, 'rochers', 'ZO01');
INSERT INTO enclos VALUES ('C002', 46.120500, 2.220500, 650, 'bassin d''eau douce', 'ZO02');
INSERT INTO enclos VALUES ('C003', 46.121000, 2.221000, 300, 'voliere', 'ZO03');
INSERT INTO enclos VALUES ('C004', 46.121500, 2.221500, 900, 'plaine', 'ZO04');
INSERT INTO enclos VALUES ('C005', 46.122000, 2.222000, 750, 'arbres et cordes', 'ZO05');
INSERT INTO enclos VALUES ('C006', 46.122500, 2.222500, 1400, 'zone ombragee', 'ZO04');
INSERT INTO enclos VALUES ('C007', 46.123000, 2.223000, 1200, 'zone humide', 'ZO05');

-- table reparation
INSERT INTO reparation VALUES ('R001', 'Cloture abimee', TO_DATE('01/03/2026','DD/MM/YYYY'), 'P015', NULL, 'C001');
INSERT INTO reparation VALUES ('R002', 'Porte securisee', TO_DATE('03/03/2026','DD/MM/YYYY'), NULL, 'T001', 'C002');
INSERT INTO reparation VALUES ('R003', 'Reparation voliere', TO_DATE('05/03/2026','DD/MM/YYYY'), NULL, 'T002', 'C003');
INSERT INTO reparation VALUES ('R004', 'Nettoyage technique bassin', TO_DATE('06/03/2026','DD/MM/YYYY'), 'P015', NULL, 'C002');
INSERT INTO reparation VALUES ('R005', 'Reparation barriere', TO_DATE('09/03/2026','DD/MM/YYYY'), 'P016', NULL, 'C004');
INSERT INTO reparation VALUES ('R006', 'Renforcement vitre', TO_DATE('10/03/2026','DD/MM/YYYY'), 'P016', NULL, 'C005');
INSERT INTO reparation VALUES ('R007', 'Pompe du bassin', TO_DATE('11/03/2026','DD/MM/YYYY'), NULL, 'T003', 'C006');
INSERT INTO reparation VALUES ('R008', 'Portail de securite', TO_DATE('12/03/2026','DD/MM/YYYY'), NULL, 'T002', 'C007');

-- table animal
INSERT INTO animal VALUES ('RF00000001', 'Simba', TO_DATE('10/05/2018','DD/MM/YYYY'), 190, 'carnivore', 'E001', 'Z001', 'C001');
INSERT INTO animal VALUES ('RF00000002', 'Nala', TO_DATE('12/06/2019','DD/MM/YYYY'), 170, 'carnivore', 'E001', 'Z001', 'C001');
INSERT INTO animal VALUES ('RF00000003', 'Rex', TO_DATE('08/04/2020','DD/MM/YYYY'), 210, 'carnivore', 'E002', 'Z001', 'C001');
INSERT INTO animal VALUES ('RF00000004', 'Milo', TO_DATE('15/01/2022','DD/MM/YYYY'), 160, 'carnivore', 'E002', 'Z001', 'C001');
INSERT INTO animal VALUES ('RF00000005', 'Baloo', TO_DATE('03/03/2016','DD/MM/YYYY'), 320, 'omnivore', 'E003', 'Z001', 'C002');
INSERT INTO animal VALUES ('RF00000006', 'Atlas', TO_DATE('01/09/2017','DD/MM/YYYY'), 6, 'carnivore', 'E004', 'Z001', 'C003');
INSERT INTO animal VALUES ('RF00000007', 'Dumbo', TO_DATE('15/01/2015','DD/MM/YYYY'), 540, 'herbivore', 'E005', 'Z001', 'C004');
INSERT INTO animal VALUES ('RF00000008', 'LongCou', TO_DATE('02/11/2019','DD/MM/YYYY'), 720, 'herbivore', 'E006', 'Z001', 'C006');
INSERT INTO animal VALUES ('RF00000009', 'Koko', TO_DATE('07/05/2017','DD/MM/YYYY'), 180, 'omnivore', 'E007', 'Z001', 'C005');
INSERT INTO animal VALUES ('RF00000010', 'Momo', TO_DATE('21/09/2020','DD/MM/YYYY'), 155, 'omnivore', 'E007', 'Z001', 'C005');
INSERT INTO animal VALUES ('RF00000011', 'Splash', TO_DATE('10/04/2014','DD/MM/YYYY'), 1500, 'herbivore', 'E008', 'Z001', 'C007');
INSERT INTO animal VALUES ('RF00000012', 'Snap', TO_DATE('12/07/2016','DD/MM/YYYY'), 420, 'carnivore', 'E008', 'Z001', 'C007');
INSERT INTO animal VALUES ('RF00000013', 'Luna', TO_DATE('10/10/2021','DD/MM/YYYY'), 68, 'carnivore', 'E004', 'Z002', 'C003');
INSERT INTO animal VALUES ('RF00000014', 'Tania', TO_DATE('20/03/2021','DD/MM/YYYY'), 175, 'carnivore', 'E002', 'Z001', 'C001');
INSERT INTO animal VALUES ('RF00000015', 'Atlasia', TO_DATE('04/04/2015','DD/MM/YYYY'), 200, 'carnivore', 'E001', 'Z002', 'C001');
INSERT INTO animal VALUES ('RF00000016', 'Khan', TO_DATE('16/08/2014','DD/MM/YYYY'), 230, 'carnivore', 'E002', 'Z002', 'C001');

-- table nourriture
INSERT INTO nourriture VALUES ('RF00000001', 'P007', TO_DATE('20/03/2026','DD/MM/YYYY'), 5);
INSERT INTO nourriture VALUES ('RF00000002', 'P006', TO_DATE('20/03/2026','DD/MM/YYYY'), 4);
INSERT INTO nourriture VALUES ('RF00000003', 'P007', TO_DATE('20/03/2026','DD/MM/YYYY'), 6);
INSERT INTO nourriture VALUES ('RF00000004', 'P006', TO_DATE('21/03/2026','DD/MM/YYYY'), 5);
INSERT INTO nourriture VALUES ('RF00000005', 'P008', TO_DATE('20/03/2026','DD/MM/YYYY'), 8);
INSERT INTO nourriture VALUES ('RF00000006', 'P009', TO_DATE('20/03/2026','DD/MM/YYYY'), 2);
INSERT INTO nourriture VALUES ('RF00000007', 'P008', TO_DATE('20/03/2026','DD/MM/YYYY'), 12);
INSERT INTO nourriture VALUES ('RF00000008', 'P010', TO_DATE('21/03/2026','DD/MM/YYYY'), 14);
INSERT INTO nourriture VALUES ('RF00000009', 'P011', TO_DATE('22/03/2026','DD/MM/YYYY'), 6);
INSERT INTO nourriture VALUES ('RF00000010', 'P012', TO_DATE('22/03/2026','DD/MM/YYYY'), 5);
INSERT INTO nourriture VALUES ('RF00000011', 'P012', TO_DATE('22/03/2026','DD/MM/YYYY'), 20);
INSERT INTO nourriture VALUES ('RF00000012', 'P012', TO_DATE('22/03/2026','DD/MM/YYYY'), 11);
INSERT INTO nourriture VALUES ('RF00000013', 'P009', TO_DATE('23/03/2026','DD/MM/YYYY'), 3);
INSERT INTO nourriture VALUES ('RF00000014', 'P007', TO_DATE('23/03/2026','DD/MM/YYYY'), 5);

-- table soin
INSERT INTO soin VALUES ('S001', 2, TO_DATE('10/03/2026','DD/MM/YYYY'), 'P007', 'RF00000001');
INSERT INTO soin VALUES ('S002', 1, TO_DATE('11/03/2026','DD/MM/YYYY'), 'P013', 'RF00000005');
INSERT INTO soin VALUES ('S003', 3, TO_DATE('12/03/2026','DD/MM/YYYY'), 'P006', 'RF00000002');
INSERT INTO soin VALUES ('S004', 2, TO_DATE('13/03/2026','DD/MM/YYYY'), 'P013', 'RF00000006');
INSERT INTO soin VALUES ('S005', 1, TO_DATE('14/03/2026','DD/MM/YYYY'), 'P008', 'RF00000007');
INSERT INTO soin VALUES ('S006', 2, TO_DATE('15/03/2026','DD/MM/YYYY'), 'P007', 'RF00000003');
INSERT INTO soin VALUES ('S007', 1, TO_DATE('16/03/2026','DD/MM/YYYY'), 'P009', 'RF00000013');
INSERT INTO soin VALUES ('S008', 2, TO_DATE('17/03/2026','DD/MM/YYYY'), 'P014', 'RF00000011');
INSERT INTO soin VALUES ('S009', 1, TO_DATE('18/03/2026','DD/MM/YYYY'), 'P010', 'RF00000009');
INSERT INTO soin VALUES ('S010', 2, TO_DATE('19/03/2026','DD/MM/YYYY'), 'P014', 'RF00000012');
INSERT INTO soin VALUES ('S011', 1, TO_DATE('20/03/2026','DD/MM/YYYY'), 'P011', 'RF00000010');
INSERT INTO soin VALUES ('S012', 2, TO_DATE('21/03/2026','DD/MM/YYYY'), 'P007', 'RF00000014');

-- table cohabiter
INSERT INTO cohabiter VALUES ('E001', 'E002');
INSERT INTO cohabiter VALUES ('E003', 'E005');
INSERT INTO cohabiter VALUES ('E004', 'E005');
INSERT INTO cohabiter VALUES ('E006', 'E005');
INSERT INTO cohabiter VALUES ('E007', 'E006');
INSERT INTO cohabiter VALUES ('E008', 'E005');

-- table parent
INSERT INTO parent VALUES ('RF00000002', 'RF00000001');
INSERT INTO parent VALUES ('RF00000004', 'RF00000003');
INSERT INTO parent VALUES ('RF00000010', 'RF00000009');
INSERT INTO parent VALUES ('RF00000014', 'RF00000003');
INSERT INTO parent VALUES ('RF00000002', 'RF00000015');
INSERT INTO parent VALUES ('RF00000004', 'RF00000016');

-- table travailler
INSERT INTO travailler VALUES ('B001', 'P019', TO_DATE('01/01/2024','DD/MM/YYYY'), NULL);
INSERT INTO travailler VALUES ('B001', 'P020', TO_DATE('01/02/2025','DD/MM/YYYY'), TO_DATE('31/08/2025','DD/MM/YYYY'));
INSERT INTO travailler VALUES ('B002', 'P020', TO_DATE('01/06/2024','DD/MM/YYYY'), NULL);
INSERT INTO travailler VALUES ('B003', 'P021', TO_DATE('01/02/2025','DD/MM/YYYY'), NULL);
INSERT INTO travailler VALUES ('B002', 'P019', TO_DATE('01/09/2025','DD/MM/YYYY'), TO_DATE('15/01/2026','DD/MM/YYYY'));
INSERT INTO travailler VALUES ('B001', 'P021', TO_DATE('01/03/2024','DD/MM/YYYY'), TO_DATE('31/12/2024','DD/MM/YYYY'));

-- table ca
INSERT INTO ca VALUES ('B001', TO_DATE('01/03/2026','DD/MM/YYYY'), 450);
INSERT INTO ca VALUES ('B001', TO_DATE('02/03/2026','DD/MM/YYYY'), 520);
INSERT INTO ca VALUES ('B001', TO_DATE('03/03/2026','DD/MM/YYYY'), 610);
INSERT INTO ca VALUES ('B001', TO_DATE('04/03/2026','DD/MM/YYYY'), 490);
INSERT INTO ca VALUES ('B001', TO_DATE('05/03/2026','DD/MM/YYYY'), 530);
INSERT INTO ca VALUES ('B001', TO_DATE('01/04/2026','DD/MM/YYYY'), 700);
INSERT INTO ca VALUES ('B002', TO_DATE('01/03/2026','DD/MM/YYYY'), 300);
INSERT INTO ca VALUES ('B002', TO_DATE('02/03/2026','DD/MM/YYYY'), 280);
INSERT INTO ca VALUES ('B002', TO_DATE('03/03/2026','DD/MM/YYYY'), 350);
INSERT INTO ca VALUES ('B002', TO_DATE('04/03/2026','DD/MM/YYYY'), 390);
INSERT INTO ca VALUES ('B002', TO_DATE('01/04/2026','DD/MM/YYYY'), 410);
INSERT INTO ca VALUES ('B003', TO_DATE('01/03/2026','DD/MM/YYYY'), 260);
INSERT INTO ca VALUES ('B003', TO_DATE('02/03/2026','DD/MM/YYYY'), 310);
INSERT INTO ca VALUES ('B003', TO_DATE('01/04/2026','DD/MM/YYYY'), 480);

-- table donne_acces
INSERT INTO donne_acces VALUES ('N001', 'P001');
INSERT INTO donne_acces VALUES ('N001', 'P002');
INSERT INTO donne_acces VALUES ('N002', 'P001');
INSERT INTO donne_acces VALUES ('N002', 'P002');
INSERT INTO donne_acces VALUES ('N002', 'P003');
INSERT INTO donne_acces VALUES ('N003', 'P001');
INSERT INTO donne_acces VALUES ('N003', 'P002');
INSERT INTO donne_acces VALUES ('N003', 'P003');
INSERT INTO donne_acces VALUES ('N003', 'P004');

-- table parrainer
INSERT INTO parrainer VALUES ('RF00000001', 'V001', 'N003');
INSERT INTO parrainer VALUES ('RF00000002', 'V002', 'N001');
INSERT INTO parrainer VALUES ('RF00000005', 'V003', 'N002');
INSERT INTO parrainer VALUES ('RF00000006', 'V004', 'N003');
INSERT INTO parrainer VALUES ('RF00000007', 'V005', 'N001');
INSERT INTO parrainer VALUES ('RF00000008', 'V006', 'N002');
INSERT INTO parrainer VALUES ('RF00000009', 'V001', 'N003');
INSERT INTO parrainer VALUES ('RF00000010', 'V002', 'N001');
INSERT INTO parrainer VALUES ('RF00000011', 'V003', 'N002');
INSERT INTO parrainer VALUES ('RF00000012', 'V004', 'N003');
INSERT INTO parrainer VALUES ('RF00000013', 'V005', 'N001');
INSERT INTO parrainer VALUES ('RF00000014', 'V006', 'N002');

COMMIT;