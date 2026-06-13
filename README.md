<h1 align="center">PokéQuizz</h1>

<p align="center">
  Découvrez l'univers Pokémon avec <strong>PokéQuizz</strong> ! Testez vos connaissances, devinez le nom du Pokémon affiché et grimpez dans le Top 20 du classement.
</p>

---

## Sommaire

- [Sommaire](#sommaire)
- [Présentation](#présentation)
- [Fonctionnement du jeu](#fonctionnement-du-jeu)
- [Stack technique](#stack-technique)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Base de données](#base-de-données)
- [Lancer le projet](#lancer-le-projet)
- [Tests](#tests)

---

## Présentation

PokéQuizz est un jeu web de culture Pokémon. Le joueur choisit un pseudo, puis doit
deviner le nom des Pokémon affichés à l'écran. Les meilleurs scores sont enregistrés
dans un classement et seuls les **20 meilleurs joueurs** y figurent.

Les images des Pokémon sont récupérées dynamiquement via la
[PokéAPI](https://pokeapi.co/), puis mises en cache pour limiter les appels réseau.

## Fonctionnement du jeu

- 🎯 Un jeu de connaissances sur les Pokémon.
- ❤️ Le joueur dispose de **3 vies** ; chaque mauvaise réponse en retire une.
- ➕ **+1 point** par bonne réponse.
- 🏆 L'objectif est d'intégrer le **Top 20** du classement.
- 💾 À la fin d'une partie, le score est enregistré et le classement mis à jour.

## Stack technique

| Domaine         | Technologies                          |
| --------------- | ------------------------------------- |
| Back-end        | PHP 8.2, Symfony 6.3                  |
| Front-end       | Twig, JavaScript, CSS, Webpack Encore |
| Base de données | MySQL (Doctrine ORM + migrations)     |
| API externe     | [PokéAPI](https://pokeapi.co/)        |
| Outils          | Docker / Docker Compose, phpMyAdmin   |
| Tests           | PHPUnit                               |

## Prérequis

```
PHP 8.2.8
Composer 2.5.8
Symfony CLI 5.5.7
Symfony 6.3.2
Node.js et npm (ou yarn)
Docker et Docker Compose
```

<p align="center"><img src=".github/assets/symfony.png" alt="logo Symfony"></p>

> Pour installer Symfony, suivez le [guide officiel](https://symfony.com/doc/current/setup.html).
> Vérifiez ensuite vos prérequis (hors Docker) avec :

```bash
symfony check:requirements
```

<p align="center"><img src=".github/assets/docker.png" alt="logo Docker"></p>

> Pour installer Docker et Docker Compose, rendez-vous sur le
> [site officiel](https://www.docker.com/products/docker-desktop/). Vérifiez ensuite
> les versions installées :

```bash
docker --version
docker-compose --version
```

## Installation

Clonez le dépôt puis installez les dépendances PHP et front-end :

```bash
git clone https://github.com/Mart1n-S/PokeQuizz.git
cd PokeQuizz

composer install
npm install
npm run build
```

## Base de données

La configuration de connexion se trouve dans le fichier `.env` :

```
DATABASE_URL="mysql://root:password@127.0.0.1:3306/pokequizz?serverVersion=8.0.34&charset=utf8mb4"
```

Démarrez le conteneur MySQL (et phpMyAdmin, accessible sur
[http://localhost:8080](http://localhost:8080)) :

```bash
docker-compose up -d
```

Appliquez ensuite les migrations :

```bash
symfony console doctrine:migrations:migrate
```

(Optionnel) Chargez les fixtures pour disposer d'un classement de démonstration en
environnement de développement :

```bash
php bin/console doctrine:fixtures:load
```

## Lancer le projet

Démarrez le serveur de développement Symfony :

```bash
symfony server:start --no-tls
```

L'application est alors disponible sur l'adresse indiquée par la CLI (par défaut
[http://localhost:8000](http://localhost:8000)).

> 💡 Pendant le développement front-end, lancez `npm run watch` pour recompiler
> automatiquement les assets à chaque modification.

## Tests

Exécutez la suite de tests PHPUnit :

```bash
php bin/phpunit --testdox
```
