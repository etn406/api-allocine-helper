API Allociné Helper PHP
=======================

[English version](#english)

L'API Allociné Helper permet d'utiliser plus simplement l'API d'[AlloCiné](http://www.allocine.fr/).
La classe **AlloHelper** permet de trouver des informations sur les films, stars, articles, horaires et critiques.
Il est possible de manipuler les données reçues grâce à la classe **AlloData** (optionnel).
La classe **AlloImage** permet de manipuler facilement la taille des posters et images stockés sur Allociné.

### Installation

Déplacer le fichier `api-allocine-helper.php` dans le répertoire souhaité.
Un simple `require_once "./api-allocine-helper.php";` permet d'utiliser l'API dans votre code.

### Usage

L'utilisation est très simple, néanmoins il est fortement conseillé de connaître la programmation orientée objet, et de savoir utiliser le bloc `try{} catch(){}`.
Exemple d'utilisation pour récupérer les informations d'un film:

Premièrement, inclure le fichier et créer un objet `AlloHelper`:

```
<?php
    // Inclure le script
    require_once "./api-allocine-helper.php";
    
    // Créer l'objet
    $helper = new AlloHelper;
    
```

Pour plus de clareté, on définit les paramètres à l'avance: le code du film, et la quantité d'informations à récupérer.

```
    $code = 27061;
    $profile = 'small';
    
```

Ensuite, il est conseillé d'effectuer des requêtes dans un bloc `try{} catch(){}` pour gérer les erreurs.

```
    try
    {
        // Envoi de la requête
        $movie = $helper->movie( $code, $profile );
        
        // Afficher le titre
        echo "Titre du film: ", $movie->title, PHP_EOL;
        
        // Afficher toutes les données
        print_r($movie->getArray());
        
    }
    catch( ErrorException $error )
    {
        // En cas d'erreur
        echo "Erreur n°", $error->getCode(), ": ", $error->getMessage(), PHP_EOL;
    }
?>
```


English
-------

API Allociné Helper is a support for using the API of [AlloCiné](http://www.allocine.fr/).
Find a lot of information about movies, people, tv series, etc, with the class **AlloHelper**.
It is possible to manipulate the received data with the class **AlloData** (optional).
And as a bonus, you can simply modify posters and photos from the Allociné server just by changing the URL with the class **AlloImage**.


### Installation

This is just a script, you put it in your favorite directory and you do a `require_once "./api-allocine-helper.php";` in your code.

### Usage

Usage is very simple, however it is strongly advisable to know OOP, and the `try{} catch(){}` block.

First, create an `AlloHelper` object:

```
<?php
    // Include the script
    require_once "./api-allocine-helper.php";
    
    // Creat the object
    $helper = new AlloHelper;
    
```

For more clarity, we should define parameters before: the movie's code, and the quantity of information to get.

```
    $code = 27061;
    $profile = 'small';
    
```

Next, it's advisable to do requests in an `try{} catch(){}` block for handling errors.

```
    try
    {
        // Request sending
        $movie = $helper->movie( $code, $profile );
        
        // Print the title
        echo "Title: ", $movie->title, PHP_EOL;
        
        // Print all data
        print_r($movie->getArray());
        
    }
    catch( ErrorException $error )
    {
        // Error
        echo "Error ", $error->getCode(), ": ", $error->getMessage(), PHP_EOL;
    }
?>
```

