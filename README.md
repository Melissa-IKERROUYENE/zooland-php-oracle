# Zoo’land — Gestion d’un zoo

Application web PHP reliée à une base Oracle pour centraliser les informations d’un zoo : animaux, soins, personnel, enclos, boutiques et parrainages. Les fonctionnalités accessibles dépendent du rôle de l’utilisateur.

Projet universitaire de bases de données réalisé à l’Université de Picardie Jules Verne, associant modélisation conceptuelle, conception relationnelle, requêtes SQL et développement d’une interface web.

## Fonctionnalités

- **Animaux et espèces** : consultation des fiches, recherche, gestion des caractéristiques, filiations et compatibilités de cohabitation.
- **Soins et alimentation** : suivi des soins et consultation des distributions de nourriture.
- **Organisation du zoo** : zones, enclos, personnel, responsables, remplacements et réparations.
- **Boutiques** : historique des emplois, chiffres d’affaires et synthèses mensuelles ou annuelles.
- **Parrainages** : visiteurs, niveaux de contribution, prestations et accès associés.
- **Recherches avancées** : animaux sans soin, poids moyen par zone, enclos sans réparation, historique des emplois et autres consultations croisées.
- **Comptes utilisateurs** : authentification, changement de mot de passe, modification du profil et navigation adaptée aux droits.

Les écrans de liste, d’ajout, de modification et de suppression reposent sur une configuration commune des tables. Les opérations autorisées sont définies dans `public_html/includes/tables_config.php` ; certaines données, comme les distributions de nourriture, sont proposées en consultation uniquement.

## Technologies et conception

**PHP · Oracle SQL · OCI8 · HTML · CSS · JavaScript**

La base comprend **21 tables**, avec clés primaires simples ou composées et contraintes de référence. L’application utilise des requêtes paramétrées, des sessions PHP, des mots de passe hachés avec bcrypt et des jetons de protection des formulaires POST.

Le rapport présente les MCD, leurs évolutions, les choix de conception et les travaux SQL. Le script `sql/installation.sql` décrit le schéma effectivement utilisé par l’application.

## Contenu du dépôt

| Élément | Contenu |
| --- | --- |
| `public_html/` | Application PHP, feuilles de style et JavaScript |
| `config.example.php` | Modèle de configuration Oracle, sans identifiants privés |
| `sql/installation.sql` | Création des tables et chargement des données de démonstration |
| `sql/requetes.sql` | Requêtes d’analyse réalisées dans le cadre du projet |
| `sql/suppression.sql` | Suppression des tables, pour une réinitialisation volontaire |
| `docs/Rapport-base-de-donnees.pdf` | Rapport universitaire de conception et de réalisation |

## Installation

### 1. Préparer l’environnement

- PHP **8.1 ou supérieur**, avec l’extension **OCI8** et un client Oracle compatible.
- Une base Oracle accessible et un utilisateur autorisé à créer ses tables.
- SQL*Plus ou Oracle SQL Developer pour exécuter les scripts SQL.

### 2. Initialiser la base

Sur un **schéma vide dédié au projet**, exécuter `sql/installation.sql`. Avec SQL*Plus, depuis la racine du dépôt :

```sql
@sql/installation.sql
```

Le script crée les tables et insère les données de démonstration. Pour réutiliser une base du projet déjà installée, conserver ses données et passer directement à la configuration.

`sql/suppression.sql` efface les tables : il sert uniquement à une réinitialisation choisie. Les requêtes de `sql/requetes.sql` s’exécutent séparément dans un outil Oracle.

### 3. Configurer la connexion

Copier `config.example.php` sous le nom **`config.local.php`**, à la racine du projet, puis renseigner les paramètres de sa base :

```php
<?php
return [
    'host' => 'localhost:1521/FREEPDB1',
    'user' => 'VOTRE_UTILISATEUR',
    'password' => 'VOTRE_MOT_DE_PASSE',
];
```

L’adresse ci-dessus est un exemple : utiliser le nom de service ou l’alias Oracle fourni pour son environnement. La connexion passe par `oci_connect` ; aucune migration vers un autre moteur de base de données n’est nécessaire.

Les variables d’environnement `ORACLE_HOST`, `ORACLE_USER` et `ORACLE_PASSWORD` peuvent également être utilisées et prennent priorité sur le fichier. L’ancien fichier `public_html/myparam.inc.php`, avec les constantes `MYHOST`, `MYUSER` et `MYPASS`, reste compatible si `config.local.php` est absent.

La configuration privée est exclue du suivi Git par `.gitignore`. Ne pas l’envoyer manuellement sur GitHub.

### 4. Lancer l’application

Depuis la racine du dépôt :

```bash
php -S localhost:8000 -t public_html
```

Ouvrir **http://localhost:8000**. Pour un hébergement Apache ou Nginx, utiliser `public_html` comme dossier public et conserver `config.local.php` en dehors de ce dossier.

## Comptes de démonstration

Les données initiales fournissent les comptes suivants :

| Identifiant | Rôle |
| --- | --- |
| `P001` | Soigneur |
| `P013` | Vétérinaire |
| `P015` | Personnel technique |
| `P017` | Entretien |
| `P019` | Personnel de boutique |
| `P022` | Comptable |
| `P023` | Gestionnaire |
| `P024` | Dirigeant |

Mot de passe initial : **`zoo26`**. À la première connexion, l’application invite à le modifier dans le profil. Ces comptes sont destinés à une démonstration ; modifier leurs mots de passe avant toute mise en ligne accessible au public.

Le gestionnaire permet de parcourir les principales opérations de gestion. Le dirigeant et les autres métiers disposent de droits distincts : l’absence d’un bouton peut correspondre à une restriction prévue pour ce rôle.

Les dates des formulaires se saisissent au format **JJ/MM/AAAA**. Les champs facultatifs peuvent rester vides.

## Auteures

**Melissa IKERROUYENE · Heba NAKSHO**  
Université de Picardie Jules Verne — Projet universitaire de bases de données.
