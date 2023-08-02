<h1 align="center">PokéQuizz</h1><br>

<p align="center">
Découvrez l'univers Pokémon avec Pokéquizz ! Testez vos connaissances, devinez le nom du Pokémon affiché et grimpez dans le top 20.</p><br>

<h2 align="center">Environnement de développement 📚</h2>
<br>
<h3 align="center">Prérequis 🧱</h3>

```
PHP 8.2.8

Composer 2.5.8

Symfony CLI 5.5.7

Symfony 6.3.2

Docker

Docker-compose
```

<br>
<p align="center"><img src=".github\assets\symfony.png" alt="logo symfony"></p>

> Pour tout installer, suivez le guide en cliquant [ICI](https://symfony.com/doc/current/setup.html) <br>
> Une fois cela fait, vérifiez les prérequis (sauf Docker et Docker-compose) en utilisant la commande suivante (de la CLI Symfony) 👇 : <br>

```
symfony check:requirements
```

<br>
<p align="center"><img src=".github\assets\docker.png" alt="logo docker"></p>

> Pour installer Docker et Docker-compose, cliquez [ICI](https://www.docker.com/products/docker-desktop/) <br>
> Une fois cela fait, vérifiez la version de Docker et Docker-compose avec les commandes 👇 : <br>

```
docker --version

docker-compose --version
```

<br>
<h3 align="center">Lancer l'environnement de développement 🚀</h3>

```
docker-compose up -d

symfony server:start --no-tls
```

<br>
<h3 align="center">Lancer des tests 🧪</h3>

```
php bin/phpunit --testdox
```
