# Projet Sortir.com
Ce projet a été développé en équipe dans le cadre d'une formation sur Symfony, sur une période de 8 jours.\
Il a été réalisé conformément à un cahier des charges détaillé, définissant les fonctionnalités à implémenter.

Symfony : version LTS (6.4.9)  
PHP : version 8.2  
IDE : PhpStorm

## Répartition des tâches
| Responsable | Fonctionnalité |
|-------------|----------------|
| Anthony     | Développement de la fonctionnalité de connexion et de la gestion des profils utilisateurs |
| Laura       | Mise en place des fonctionnalités de visualisation des sorties, filtrage, gestion des états et gestion de la partie administrateur |
| Chloé       | Formulaires de création et de modification des sorties |
| Axel        | Fonctionnalités d'inscription et de désistement, affichage mobile et tablette |


# Initialisation et installation de la base de données   
Exécutez la commande suivante pour créer la base de données:\
```symfony console doctrine:database:create```

Créez les tables en exécutant une migration avec:\
```symfony console make:migration```

Pour peupler la base de données avec des données fictives, utilisez la commande:\
```symfony console doctrine:fixtures:load```

# Installation des dépendances
Pour installer les dépendances, executez la commande suivante:\
```composer install```

# Mettre à jour les états des sorties
Mode automatique avec rafraichissement toutes les minutes : ```php bin/console messenger:consume```\
Choisir option 3 (scheduler_default)

# Connexion pour les tests
Login: Admin  
Mdp: Pa$$w0rd

OU 

Login: User1  
Mdp: Pa$$w0rd
