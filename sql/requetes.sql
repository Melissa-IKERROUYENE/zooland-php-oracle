SET DEFINE OFF;
-- =====================================
-- REQUETES SQL COMPATIBLES ORACLE
-- =====================================

----------------------------------------------------------
-- 1. Noms des personnels recrutés le 01/01/2024 ou travaillant dans une boutique de type snack
----------------------------------------------------------
SELECT p.nom_personnel
FROM personnel p, travailler t
WHERE p.id_personnel = t.id_personnel
AND t.date_embauche = TO_DATE('01/01/2024','DD/MM/YYYY')
UNION
SELECT p.nom_personnel
FROM personnel p, travailler t, boutique b
WHERE p.id_personnel = t.id_personnel
AND t.id_boutique = b.id_boutique
AND b.type_boutique = 'snack';

----------------------------------------------------------
-- 2. Noms et prénoms des visiteurs ayant parrainé Simba et Koko
----------------------------------------------------------
SELECT v.nom_visiteur, v.prenom_visiteur
FROM visiteur v, parrainer p, animal a
WHERE v.id_visiteur = p.id_visiteur
AND p.id_rfid = a.id_rfid
AND a.nom_animal = 'Simba'
INTERSECT
SELECT v.nom_visiteur, v.prenom_visiteur
FROM visiteur v, parrainer p, animal a
WHERE v.id_visiteur = p.id_visiteur
AND p.id_rfid = a.id_rfid
AND a.nom_animal = 'Koko';

----------------------------------------------------------
-- 3. Identifiants des enclos sans réparation après le 05/03/2026
----------------------------------------------------------
SELECT id_enclos
FROM enclos
MINUS
SELECT id_enclos
FROM reparation
WHERE date_reparation > TO_DATE('05/03/2026','DD/MM/YYYY');

----------------------------------------------------------
-- 4. Noms des animaux nés avant 2018 ou pesant plus de 200 kg
----------------------------------------------------------
SELECT nom_animal
FROM animal
WHERE date_naissance < TO_DATE('01/01/2018','DD/MM/YYYY')
OR poids > 200;

----------------------------------------------------------
-- 5. Noms des animaux ayant reçu un soin de dose supérieure ou égale à 2 après le 01/01/2025
----------------------------------------------------------
SELECT DISTINCT a.nom_animal
FROM animal a, soin s
WHERE a.id_rfid = s.id_rfid
AND s.dose_soin >= 2
AND s.date_soin > TO_DATE('01/01/2025','DD/MM/YYYY')
ORDER BY a.nom_animal;

----------------------------------------------------------
-- 6. Chiffre d'affaires mensuel par boutique
----------------------------------------------------------
SELECT id_boutique,
       TO_CHAR(date_ca, 'MM/YYYY') AS mois,
       SUM(chiffre_d_affaire) AS ca_mensuel
FROM ca
GROUP BY id_boutique, TO_CHAR(date_ca, 'MM/YYYY')
ORDER BY id_boutique, mois;

----------------------------------------------------------
-- 7. Chiffre d'affaires annuel par boutique
----------------------------------------------------------
SELECT id_boutique,
       TO_CHAR(date_ca, 'YYYY') AS annee,
       SUM(chiffre_d_affaire) AS ca_annuel
FROM ca
GROUP BY id_boutique, TO_CHAR(date_ca, 'YYYY')
ORDER BY id_boutique, annee;

----------------------------------------------------------
-- 8. Identifiants des enclos contenant au moins 2 animaux
----------------------------------------------------------
SELECT id_enclos
FROM animal 
GROUP BY id_enclos
HAVING COUNT(id_rfid) >= 2;

----------------------------------------------------------
-- 9. Noms et prénoms des visiteurs ayant parrainé au moins 2 animaux
----------------------------------------------------------
SELECT v.nom_visiteur, v.prenom_visiteur
FROM visiteur v, parrainer p
WHERE v.id_visiteur = p.id_visiteur
GROUP BY v.nom_visiteur, v.prenom_visiteur
HAVING COUNT(p.id_rfid) >= 2
ORDER BY v.nom_visiteur;

----------------------------------------------------------
-- 10. Pour chaque zone, poids moyen des animaux qui s'y trouvent, trié par poids moyen croissant
----------------------------------------------------------
SELECT e.id_zone, AVG(a.poids) AS poids_moyen
FROM animal a, enclos e
WHERE a.id_enclos = e.id_enclos
GROUP BY e.id_zone
ORDER BY poids_moyen ASC;

----------------------------------------------------------
-- 11. Historique des emplois du personnel en boutique
----------------------------------------------------------
SELECT p.nom_personnel, p.prenom_personnel,
       b.nom_boutique,
       t.date_embauche,
       t.date_fin_embauche
FROM personnel p, travailler t, boutique b
WHERE p.id_personnel = t.id_personnel
AND t.id_boutique = b.id_boutique
ORDER BY p.nom_personnel, t.date_embauche;

----------------------------------------------------------
-- 12. Identifiants des personnels ayant travaillé plus de 200 jours
----------------------------------------------------------
SELECT id_personnel
FROM travailler
GROUP BY id_personnel
HAVING SUM(date_fin_embauche - date_embauche) > 200;

----------------------------------------------------------
-- 13. Pour chaque enclos, identifiants des animaux qui s’y trouvent, y compris les enclos sans animaux
----------------------------------------------------------
SELECT e.id_enclos, a.id_rfid
FROM enclos e, animal a
WHERE e.id_enclos = a.id_enclos(+);

----------------------------------------------------------
-- 14. Espèces ne cohabitant avec aucune autre
----------------------------------------------------------

-- Version avec MINUS
SELECT e.id_espece, e.nom_usuel
FROM espece e
MINUS
SELECT e.id_espece, e.nom_usuel
FROM espece e, cohabiter c
WHERE e.id_espece = c.id_espece_1
   OR e.id_espece = c.id_espece_2;

-- Version avec NOT IN
SELECT e.id_espece, e.nom_usuel
FROM espece e
WHERE e.id_espece NOT IN (
    SELECT id_espece_1 FROM cohabiter
    UNION
    SELECT id_espece_2 FROM cohabiter
);

----------------------------------------------------------
-- 15. Identifiants des prestataires ayant effectué une réparation dans tous les enclos
----------------------------------------------------------
SELECT r.id_prestataire
FROM reparation r
GROUP BY r.id_prestataire
HAVING COUNT(DISTINCT r.id_enclos) = (
    SELECT COUNT(*)
    FROM enclos
);

----------------------------------------------------------
-- 16. Pour chaque animal, identifiants des soins reçus, y compris les animaux sans soin
----------------------------------------------------------
SELECT a.id_rfid, s.id_soin
FROM animal a, soin s
WHERE a.id_rfid = s.id_rfid(+);

----------------------------------------------------------
-- 17. Vue : visiteurs et animaux parrainés
----------------------------------------------------------
CREATE VIEW VUE_PARRAINAGE AS
SELECT v.nom_visiteur, a.nom_animal
FROM visiteur v, parrainer p, animal a
WHERE v.id_visiteur = p.id_visiteur
AND p.id_rfid = a.id_rfid;