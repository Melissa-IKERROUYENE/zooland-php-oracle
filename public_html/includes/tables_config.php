<?php
// ================================================
// includes/tables_config.php
// Configuration de toutes les tables de la BD
// Utilisé par liste.php, ajouter.php, modifier.php,
// supprimer.php et detail.php
//
// Les constantes ROLES_* sont définies dans session.php
// ================================================

$TABLES_CONFIG = [

    // ==================== ANIMAL ====================
    'animal' => [
        'table'        => 'animal',
        'label'        => 'Animaux',
        'label_single' => 'Animal',
        'pk'           => 'id_rfid',
        'roles_voir'   => ROLES_ANIMAUX,
	'roles_edit'   => ['gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_rfid'            => ['label'=>'ID RFID',        'type'=>'char',   'required'=>true,  'max'=>10,  'pk'=>true],
            'nom_animal'         => ['label'=>'Nom',            'type'=>'text',   'required'=>true,  'max'=>50],
            'date_naissance'     => ['label'=>'Date naissance', 'type'=>'date',   'required'=>false],
            'poids'              => ['label'=>'Poids (kg)',      'type'=>'number', 'required'=>true],
            'regime_alimentaire' => ['label'=>'Régime',         'type'=>'select', 'required'=>true,
                                     'options'=>['carnivore','herbivore','omnivore']],
            'id_espece'          => ['label'=>'Espèce',         'type'=>'fk',     'required'=>true,
                                     'fk_table'=>'espece','fk_pk'=>'id_espece','fk_label'=>'nom_usuel'],
            'id_zoo'             => ['label'=>'Zoo',            'type'=>'fk',     'required'=>true,
                                     'fk_table'=>'zoo','fk_pk'=>'id_zoo','fk_label'=>'nom_zoo'],
            'id_enclos'          => ['label'=>'Enclos',         'type'=>'fk',     'required'=>true,
                                     'fk_table'=>'enclos','fk_pk'=>'id_enclos','fk_label'=>'id_enclos'],
        ],
        'liste_colonnes' => ['nom_animal','id_espece','poids','regime_alimentaire','id_enclos'],
        'searchable'     => ['nom_animal','regime_alimentaire'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Soins reçus', 'table'=>'soin',      'fk'=>'id_rfid',        'roles'=>ROLES_SOINS],
            ['label'=>'Nourriture',  'table'=>'nourriture', 'fk'=>'id_rfid',        'roles'=>ROLES_SOINS],
            ['label'=>'Parents',     'table'=>'parent',     'fk'=>'id_rfid_enfant', 'roles'=>ROLES_ANIMAUX],
            ['label'=>'Enfants',     'table'=>'parent',     'fk'=>'id_rfid_parent', 'roles'=>ROLES_ANIMAUX],
            ['label'=>'Parrains',    'table'=>'parrainer',  'fk'=>'id_rfid',        'roles'=>ROLES_ANIMAUX],
        ],
    ],

    // ==================== ESPECE ====================
    'espece' => [
        'table'        => 'espece',
        'label'        => 'Espèces',
        'label_single' => 'Espèce',
        'pk'           => 'id_espece',
        'roles_voir'   => ROLES_ANIMAUX,
	'roles_edit'   => ['gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_espece'  => ['label'=>'ID',        'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_latin'  => ['label'=>'Nom latin', 'type'=>'text', 'required'=>true, 'max'=>50],
            'nom_usuel'  => ['label'=>'Nom usuel', 'type'=>'text', 'required'=>true, 'max'=>50],
            'est_menace' => ['label'=>'Menacé',    'type'=>'bool', 'required'=>true],
        ],
        'liste_colonnes' => ['id_espece','nom_latin','nom_usuel','est_menace'],
        'searchable'     => ['nom_latin','nom_usuel'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Animaux de cette espèce', 'table'=>'animal',    'fk'=>'id_espece',   'roles'=>ROLES_ANIMAUX],
            ['label'=>'Cohabitations',            'table'=>'cohabiter', 'fk'=>'id_espece_1', 'roles'=>ROLES_ANIMAUX],
        ],
    ],

    // ==================== ZONE ====================
    'zone' => [
        'table'        => 'zone',
        'label'        => 'Zones',
        'label_single' => 'Zone',
        'pk'           => 'id_zone',
        'roles_voir'   => ROLES_ALL,
	'roles_edit'   => ['gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_zone'  => ['label'=>'ID',  'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_zone' => ['label'=>'Nom', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_zone','nom_zone'],
        'searchable'     => ['nom_zone'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Personnel de la zone', 'table'=>'personnel', 'fk'=>'id_zone', 'roles'=>ROLES_PERSONNEL],
            ['label'=>'Enclos de la zone',    'table'=>'enclos',    'fk'=>'id_zone', 'roles'=>ROLES_ENCLOS],
            ['label'=>'Boutiques de la zone', 'table'=>'boutique',  'fk'=>'id_zone', 'roles'=>ROLES_BOUTIQUES],
            ['label'=>'Chef de zone',         'table'=>'est_chef',  'fk'=>'id_zone', 'roles'=>['gestionnaire','dirigeant']],
        ],
    ],

    // ==================== ENCLOS ====================
    'enclos' => [
        'table'        => 'enclos',
        'label'        => 'Enclos',
        'label_single' => 'Enclos',
        'pk'           => 'id_enclos',
        'roles_voir'   => ROLES_ENCLOS,
	'roles_edit'   => ['personnel_technique','gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_enclos'     => ['label'=>'ID',            'type'=>'char',   'required'=>true,  'max'=>4, 'pk'=>true],
            'latitude'      => ['label'=>'Latitude',      'type'=>'number', 'required'=>true],
            'longitude'     => ['label'=>'Longitude',     'type'=>'number', 'required'=>true],
            'surface'       => ['label'=>'Surface (m²)',  'type'=>'number', 'required'=>true],
            'particularite' => ['label'=>'Particularité', 'type'=>'text',   'required'=>false, 'max'=>50],
            'id_zone'       => ['label'=>'Zone',          'type'=>'fk',     'required'=>true,
                                'fk_table'=>'zone','fk_pk'=>'id_zone','fk_label'=>'nom_zone'],
        ],
        'liste_colonnes' => ['id_enclos','surface','particularite','id_zone'],
        'searchable'     => ['particularite'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Animaux dans cet enclos',   'table'=>'animal',     'fk'=>'id_enclos', 'roles'=>ROLES_ANIMAUX],
            ['label'=>'Réparations de cet enclos', 'table'=>'reparation', 'fk'=>'id_enclos', 'roles'=>ROLES_REPARATIONS],
        ],
    ],

    // ==================== REPARATION ====================
    'reparation' => [
        'table'        => 'reparation',
        'label'        => 'Réparations',
        'label_single' => 'Réparation',
        'pk'           => 'id_reparation',
        'roles_voir'   => ROLES_REPARATIONS,
	'roles_edit'   => ['personnel_technique','gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_reparation'     => ['label'=>'ID',          'type'=>'char', 'required'=>true,  'max'=>4, 'pk'=>true],
            'nature_reparation' => ['label'=>'Nature',      'type'=>'text', 'required'=>true,  'max'=>100],
            'date_reparation'   => ['label'=>'Date',        'type'=>'date', 'required'=>true],
            'id_personnel'      => ['label'=>'Personnel',   'type'=>'fk',   'required'=>false,
                                    'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
            'id_prestataire'    => ['label'=>'Prestataire', 'type'=>'fk',   'required'=>false,
                                    'fk_table'=>'prestataire','fk_pk'=>'id_prestataire','fk_label'=>'nom_prestataire'],
            'id_enclos'         => ['label'=>'Enclos',      'type'=>'fk',   'required'=>true,
                                    'fk_table'=>'enclos','fk_pk'=>'id_enclos','fk_label'=>'id_enclos'],
        ],
        'liste_colonnes' => ['id_reparation','nature_reparation','date_reparation','id_enclos'],
        'searchable'     => ['nature_reparation'],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== SOIN ====================
    'soin' => [
        'table'        => 'soin',
        'label'        => 'Soins',
        'label_single' => 'Soin',
        'pk'           => 'id_soin',
        'roles_voir'   => ROLES_SOINS,
'roles_edit' => ROLES_SOINS,
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_soin'      => ['label'=>'ID',      'type'=>'char',   'required'=>true, 'max'=>4, 'pk'=>true],
            'dose_soin'    => ['label'=>'Dose',     'type'=>'number', 'required'=>true],
            'date_soin'    => ['label'=>'Date',     'type'=>'date',   'required'=>true],
            'id_personnel' => ['label'=>'Soigneur', 'type'=>'fk',     'required'=>true,
                               'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
            'id_rfid'      => ['label'=>'Animal',   'type'=>'fk',     'required'=>true,
                               'fk_table'=>'animal','fk_pk'=>'id_rfid','fk_label'=>'nom_animal'],
        ],
        'liste_colonnes' => ['id_soin','date_soin','dose_soin','id_rfid','id_personnel'],
        'searchable'     => ['id_rfid','id_personnel'],
        'detail'         => true,
        'relations'      => [],
    ],

    // ==================== NOURRITURE ====================
    'nourriture' => [
        'table'        => 'nourriture',
        'label'        => 'Nourriture',
        'label_single' => 'Nourriture',
        'pk_multiple'  => ['id_rfid','id_personnel','date_nourriture'],
        'roles_voir'   => ROLES_SOINS,
'roles_edit' => [],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_rfid'         => ['label'=>'Animal',    'type'=>'fk',     'required'=>true,
                                   'fk_table'=>'animal','fk_pk'=>'id_rfid','fk_label'=>'nom_animal'],
            'id_personnel'    => ['label'=>'Soigneur',  'type'=>'fk',     'required'=>true,
                                   'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
            'date_nourriture' => ['label'=>'Date',      'type'=>'date',   'required'=>true],
            'dose_nourriture' => ['label'=>'Dose (kg)', 'type'=>'number', 'required'=>true],
        ],
        'liste_colonnes' => ['id_rfid','id_personnel','date_nourriture','dose_nourriture'],
        'searchable'     => ['id_rfid'],
        'detail'         => true,
        'relations'      => [],
        'no_edit'        => true,
    ],

    // ==================== PERSONNEL ====================
    'personnel' => [
        'table'        => 'personnel',
        'label'        => 'Personnel',
        'label_single' => 'Personnel',
        'pk'           => 'id_personnel',
        'roles_voir'   => ROLES_PERSONNEL,
	'roles_edit'   => ['gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_personnel'            => ['label'=>'ID',           'type'=>'char',     'required'=>true,  'max'=>4,   'pk'=>true],
            'nom_personnel'           => ['label'=>'Nom',          'type'=>'text',     'required'=>true,  'max'=>50],
            'prenom_personnel'        => ['label'=>'Prénom',       'type'=>'text',     'required'=>true,  'max'=>50],
            'mot_de_passe'            => ['label'=>'Mot de passe', 'type'=>'password', 'required'=>true,  'max'=>255],
            'date_entree'             => ['label'=>'Date entrée',  'type'=>'date',     'required'=>true],
            'fonction'                => ['label'=>'Fonction',     'type'=>'select',   'required'=>true,
                                          'options'=>['soigneur','veterinaire','personnel_technique',
                                                      'entretien','boutique','comptable','gestionnaire','dirigeant']],
            'salaire'                 => ['label'=>'Salaire (€)',  'type'=>'number',   'required'=>true],
            'id_personnel_remplacant' => ['label'=>'Remplaçant',   'type'=>'fk',       'required'=>false,
                                          'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
            'id_zone'                 => ['label'=>'Zone',         'type'=>'fk',       'required'=>false,
                                          'fk_table'=>'zone','fk_pk'=>'id_zone','fk_label'=>'nom_zone'],
            'id_personnel_chef'       => ['label'=>'Chef',         'type'=>'fk',       'required'=>false,
                                          'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
        ],
        'liste_colonnes' => ['id_personnel','nom_personnel','prenom_personnel','fonction','salaire'],
        'searchable'     => ['nom_personnel','prenom_personnel','fonction'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Soins effectués',      'table'=>'soin',       'fk'=>'id_personnel', 'roles'=>ROLES_SOINS],
            ['label'=>'Nourriture donnée',    'table'=>'nourriture', 'fk'=>'id_personnel', 'roles'=>ROLES_SOINS],
            ['label'=>'Réparations',          'table'=>'reparation', 'fk'=>'id_personnel', 'roles'=>ROLES_REPARATIONS],
            ['label'=>'Historique boutiques', 'table'=>'travailler', 'fk'=>'id_personnel', 'roles'=>ROLES_BOUTIQUES],
        ],
    ],

    // ==================== BOUTIQUE ====================
    'boutique' => [
        'table'        => 'boutique',
        'label'        => 'Boutiques',
        'label_single' => 'Boutique',
        'pk'           => 'id_boutique',
        'roles_voir'   => ROLES_BOUTIQUES,
	'roles_edit'   => ['gestionnaire'],
	'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_boutique'   => ['label'=>'ID',          'type'=>'char',   'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_boutique'  => ['label'=>'Nom',         'type'=>'text',   'required'=>true, 'max'=>50],
            'type_boutique' => ['label'=>'Type',        'type'=>'select', 'required'=>true,
                                'options'=>['souvenir','snack']],
            'id_zone'       => ['label'=>'Zone',        'type'=>'fk',     'required'=>true,
                                'fk_table'=>'zone','fk_pk'=>'id_zone','fk_label'=>'nom_zone'],
            'id_personnel'  => ['label'=>'Responsable', 'type'=>'fk',    'required'=>false,
                                'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
        ],
        'liste_colonnes' => ['id_boutique','nom_boutique','type_boutique','id_zone'],
        'searchable'     => ['nom_boutique','type_boutique'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Chiffre d\'affaires', 'table'=>'ca',         'fk'=>'id_boutique', 'roles'=>ROLES_CA],
            ['label'=>'Historique emplois',  'table'=>'travailler', 'fk'=>'id_boutique', 'roles'=>ROLES_BOUTIQUES],
        ],
    ],

    // ==================== TRAVAILLER ====================
    'travailler' => [
        'table'        => 'travailler',
        'label'        => 'Historique emplois',
        'label_single' => 'Emploi',
        'pk_multiple'  => ['id_boutique','id_personnel'],
        'roles_voir'   => ROLES_BOUTIQUES,
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_boutique'       => ['label'=>'Boutique',      'type'=>'fk',  'required'=>true,
                                    'fk_table'=>'boutique','fk_pk'=>'id_boutique','fk_label'=>'nom_boutique'],
            'id_personnel'      => ['label'=>'Personnel',     'type'=>'fk',  'required'=>true,
                                    'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
            'date_embauche'     => ['label'=>'Date embauche', 'type'=>'date','required'=>true],
            'date_fin_embauche' => ['label'=>'Date fin',      'type'=>'date','required'=>false],
        ],
        'liste_colonnes' => ['id_boutique','id_personnel','date_embauche','date_fin_embauche'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== CA ====================
    'ca' => [
        'table'        => 'ca',
        'label'        => 'Chiffres d\'affaires',
        'label_single' => 'CA',
        'pk_multiple'  => ['id_boutique','date_ca'],
        'roles_voir'   => ROLES_CA,
'roles_edit'   => ['boutique','gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_boutique'       => ['label'=>'Boutique', 'type'=>'fk',     'required'=>true,
                                    'fk_table'=>'boutique','fk_pk'=>'id_boutique','fk_label'=>'nom_boutique'],
            'date_ca'           => ['label'=>'Date',     'type'=>'date',   'required'=>true],
            'chiffre_d_affaire' => ['label'=>'CA (€)',   'type'=>'number', 'required'=>true],
        ],
        'liste_colonnes' => ['id_boutique','date_ca','chiffre_d_affaire'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== VISITEUR ====================
    'visiteur' => [
        'table'        => 'visiteur',
        'label'        => 'Visiteurs',
        'label_single' => 'Visiteur',
        'pk'           => 'id_visiteur',
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_visiteur'     => ['label'=>'ID',     'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_visiteur'    => ['label'=>'Nom',    'type'=>'text', 'required'=>true, 'max'=>50],
            'prenom_visiteur' => ['label'=>'Prénom', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_visiteur','nom_visiteur','prenom_visiteur'],
        'searchable'     => ['nom_visiteur','prenom_visiteur'],
        'detail'         => true,
        'relations' => [
            ['label'=>'Animaux parrainés', 'table'=>'parrainer', 'fk'=>'id_visiteur', 'roles'=>['gestionnaire','dirigeant']],
        ],
    ],

    // ==================== NIVEAU_CONTRIBUTION ====================
    'niveau_contribution' => [
        'table'        => 'niveau_contribution',
        'label'        => 'Niveaux de contribution',
        'label_single' => 'Niveau',
        'pk'           => 'id_niveau',
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_niveau'  => ['label'=>'ID',  'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_niveau' => ['label'=>'Nom', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_niveau','nom_niveau'],
        'searchable'     => ['nom_niveau'],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== PRESTATION ====================
    'prestation' => [
        'table'        => 'prestation',
        'label'        => 'Prestations',
        'label_single' => 'Prestation',
        'pk'           => 'id_prestation',
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit' => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_prestation'  => ['label'=>'ID',  'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_prestation' => ['label'=>'Nom', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_prestation','nom_prestation'],
        'searchable'     => ['nom_prestation'],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== PRESTATAIRE ====================
    'prestataire' => [
        'table'        => 'prestataire',
        'label'        => 'Prestataires',
        'label_single' => 'Prestataire',
        'pk'           => 'id_prestataire',
        'roles_voir'   => ['personnel_technique','gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_prestataire'  => ['label'=>'ID',  'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_prestataire' => ['label'=>'Nom', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_prestataire','nom_prestataire'],
        'searchable'     => ['nom_prestataire'],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== ZOO ====================
    'zoo' => [
        'table'        => 'zoo',
        'label'        => 'Zoos',
        'label_single' => 'Zoo',
        'pk'           => 'id_zoo',
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_zoo'    => ['label'=>'ID',    'type'=>'char', 'required'=>true, 'max'=>4, 'pk'=>true],
            'nom_zoo'   => ['label'=>'Nom',   'type'=>'text', 'required'=>true, 'max'=>50],
            'pays_zoo'  => ['label'=>'Pays',  'type'=>'text', 'required'=>true, 'max'=>50],
            'ville_zoo' => ['label'=>'Ville', 'type'=>'text', 'required'=>true, 'max'=>50],
        ],
        'liste_colonnes' => ['id_zoo','nom_zoo','pays_zoo','ville_zoo'],
        'searchable'     => ['nom_zoo','pays_zoo','ville_zoo'],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== EST_CHEF ====================
    'est_chef' => [
        'table'        => 'est_chef',
        'label'        => 'Chefs de zone',
        'label_single' => 'Chef de zone',
        'pk_multiple'  => ['id_zone'],
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_zone'      => ['label'=>'Zone',      'type'=>'fk', 'required'=>true,
                               'fk_table'=>'zone','fk_pk'=>'id_zone','fk_label'=>'nom_zone'],
            'id_personnel' => ['label'=>'Personnel', 'type'=>'fk', 'required'=>true,
                               'fk_table'=>'personnel','fk_pk'=>'id_personnel','fk_label'=>'nom_personnel'],
        ],
        'liste_colonnes' => ['id_zone','id_personnel'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== COHABITER ====================
    'cohabiter' => [
        'table'        => 'cohabiter',
        'label'        => 'Cohabitations',
        'label_single' => 'Cohabitation',
        'pk_multiple'  => ['id_espece_1','id_espece_2'],
        'roles_voir'   => ROLES_ANIMAUX,
'roles_edit'   => ['veterinaire','gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_espece_1' => ['label'=>'Espèce 1', 'type'=>'fk', 'required'=>true,
                               'fk_table'=>'espece','fk_pk'=>'id_espece','fk_label'=>'nom_usuel'],
            'id_espece_2' => ['label'=>'Espèce 2', 'type'=>'fk', 'required'=>true,
                               'fk_table'=>'espece','fk_pk'=>'id_espece','fk_label'=>'nom_usuel'],
        ],
        'liste_colonnes' => ['id_espece_1','id_espece_2'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== PARENT ====================
    'parent' => [
        'table'        => 'parent',
        'label'        => 'Filiations',
        'label_single' => 'Filiation',
        'pk_multiple'  => ['id_rfid_enfant','id_rfid_parent'],
        'roles_voir'   => ROLES_ANIMAUX,
'roles_edit'   => ['soigneur','gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_rfid_enfant' => ['label'=>'Enfant', 'type'=>'fk', 'required'=>true,
                                  'fk_table'=>'animal','fk_pk'=>'id_rfid','fk_label'=>'nom_animal'],
            'id_rfid_parent' => ['label'=>'Parent', 'type'=>'fk', 'required'=>true,
                                  'fk_table'=>'animal','fk_pk'=>'id_rfid','fk_label'=>'nom_animal'],
        ],
        'liste_colonnes' => ['id_rfid_enfant','id_rfid_parent'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== PARRAINER ====================
    'parrainer' => [
        'table'        => 'parrainer',
        'label'        => 'Parrainages',
        'label_single' => 'Parrainage',
        'pk_multiple'  => ['id_rfid','id_visiteur','id_niveau'],
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_rfid'     => ['label'=>'Animal',   'type'=>'fk', 'required'=>true,
                               'fk_table'=>'animal','fk_pk'=>'id_rfid','fk_label'=>'nom_animal'],
            'id_visiteur' => ['label'=>'Visiteur', 'type'=>'fk', 'required'=>true,
                               'fk_table'=>'visiteur','fk_pk'=>'id_visiteur','fk_label'=>'nom_visiteur'],
            'id_niveau'   => ['label'=>'Niveau',   'type'=>'fk', 'required'=>true,
                               'fk_table'=>'niveau_contribution','fk_pk'=>'id_niveau','fk_label'=>'nom_niveau'],
        ],
        'liste_colonnes' => ['id_rfid','id_visiteur','id_niveau'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],

    // ==================== DONNE_ACCES ====================
    'donne_acces' => [
        'table'        => 'donne_acces',
        'label'        => 'Accès par niveau',
        'label_single' => 'Accès',
        'pk_multiple'  => ['id_niveau','id_prestation'],
        'roles_voir'   => ['gestionnaire','dirigeant'],
'roles_edit'   => ['gestionnaire'],
'roles_delete' => ['gestionnaire'],
        'colonnes' => [
            'id_niveau'     => ['label'=>'Niveau',     'type'=>'fk', 'required'=>true,
                                 'fk_table'=>'niveau_contribution','fk_pk'=>'id_niveau','fk_label'=>'nom_niveau'],
            'id_prestation' => ['label'=>'Prestation', 'type'=>'fk', 'required'=>true,
                                 'fk_table'=>'prestation','fk_pk'=>'id_prestation','fk_label'=>'nom_prestation'],
        ],
        'liste_colonnes' => ['id_niveau','id_prestation'],
        'searchable'     => [],
        'detail'         => false,
        'relations'      => [],
    ],
];


function primaryColumns(array $config): array { return isset($config['pk']) ? [$config['pk']] : $config['pk_multiple']; }
function recordWhere(array $config, string $prefix = ''): string {
    $conditions = [];
    foreach (primaryColumns($config) as $col) {
        $param = isset($config['pk']) ? 'id' : $col;
        $value = $_GET[$param] ?? '';
        if ($value === '') { http_response_code(400); exit('Identifiant manquant.'); }
        if ($config['colonnes'][$col]['type'] === 'date') {
            if (!validDate($value)) { http_response_code(400); exit('Date invalide.'); }
            $conditions[] = "$prefix$col = TO_DATE(:key_$col, 'DD/MM/YYYY')";
        } else { $conditions[] = "RTRIM($prefix$col) = RTRIM(:key_$col)"; }
    }
    return implode(' AND ', $conditions);
}
function bindRecordKey($stmt, array $config): void {
    foreach (primaryColumns($config) as $col) {
        $param = isset($config['pk']) ? 'id' : $col;
        oci_bind_by_name($stmt, ':key_' . $col, $_GET[$param]);
    }
}
function recordSelect(array $config): string {
    $cols = [];
    foreach ($config['colonnes'] as $col=>$def) {
        $cols[] = $def['type'] === 'date' ? "TO_CHAR($col, 'DD/MM/YYYY') AS $col" : $col;
    }
    return implode(', ', $cols);
}
function validDate(string $value): bool {
    if (!preg_match('~^(\d{2})/(\d{2})/(\d{4})$~', $value, $m)) { return false; }
    return checkdate((int)$m[2], (int)$m[1], (int)$m[3]);
}
function validateValue(string $value, array $def): ?string {
    if ($value === '') { return $def['required'] ? 'Ce champ est obligatoire.' : null; }
    if ($def['type'] === 'password' && (strlen($value) > 72 || str_contains($value, "\0"))) { return 'Mot de passe invalide (72 octets maximum).'; }
    if ($def['type'] !== 'password' && isset($def['max']) && strlen($value) > $def['max']) { return 'Valeur trop longue.'; }
    if ($def['type'] === 'date' && !validDate($value)) { return 'Date invalide (JJ/MM/AAAA).'; }
    if ($def['type'] === 'number' && !is_numeric($value)) { return 'Valeur numérique requise.'; }
    if ($def['type'] === 'select' && !in_array($value, $def['options'], true)) { return 'Choix invalide.'; }
    if ($def['type'] === 'bool' && !in_array($value, ['0','1'], true)) { return 'Choix invalide.'; }
    return null;
}
function validateBusiness(array $values): array {
    $errors = [];
    foreach (['poids','surface','salaire','dose_soin','dose_nourriture','chiffre_d_affaire'] as $field) {
        if (isset($values[$field]) && is_numeric($values[$field]) && $values[$field] < 0) { $errors[$field] = 'Valeur négative interdite.'; }
    }
    foreach (['latitude'=>90,'longitude'=>180] as $field=>$max) {
        if (isset($values[$field]) && is_numeric($values[$field]) && abs((float)$values[$field]) > $max) { $errors[$field] = 'Coordonnée hors limites.'; }
    }
    foreach ([['id_rfid_enfant','id_rfid_parent'],['id_personnel','id_personnel_chef'],['id_personnel','id_personnel_remplacant']] as [$a,$b]) {
        if (!empty($values[$a]) && !empty($values[$b]) && trim($values[$a]) === trim($values[$b])) { $errors[$b] = 'Les deux identifiants doivent être différents.'; }
    }
    if (!empty($values['date_embauche']) && !empty($values['date_fin_embauche']) && validDate($values['date_embauche']) && validDate($values['date_fin_embauche'])) {
        $date = fn($v) => implode('-', array_reverse(explode('/', $v)));
        if ($date($values['date_fin_embauche']) < $date($values['date_embauche'])) { $errors['date_fin_embauche'] = 'La fin doit suivre la date d’embauche.'; }
    }
    return $errors;
}
