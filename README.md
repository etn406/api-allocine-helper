API Allociné Helper PHP
=======================

English:
--------

API Allociné Helper is support for using the API of [AlloCiné](http://www.allocine.fr/), of [Beyazperde](http://www.beyazperde.com/), of [Screenrush](http://www.screenrush.co.uk/), of [Sensacine](http://www.sensacine.com/) and of [Filmstarts](http://www.filmstarts.de/).
Find lots of information about movies, people, tv series, etc, with the class **AlloHelper**.
It is possible to manipulate received data with the class **AlloData** (optional).
And in bonus you can modify simply posters and photos from the Allociné server just by changing the URL with the class **AlloImage**.


### Installation

This is just a script, you put it in your favorite directory and you do a `require_once "./api-allocine-helper.php";` in your code.

### Usage

Usage is very simple, however it is strongly advisable to know OOP, and the `try{} catch(){}` block.

First, creat an `AlloHelper` object:

```
<?php
    // Include the script
    require_once "./api-allocine-helper.php";
    
    // Creat the object
    $helper = new AlloHelper;
    
```

For more clarity, we define parameters before: the movie's code, and the quantity of information to get.

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

Français:
---------

L'API Allociné Helper permet d'utiliser plus facilement l'API d'[AlloCiné](http://www.allocine.fr/), [Beyazperde](http://www.beyazperde.com/), [Screenrush](http://www.screenrush.co.uk/), [Sensacine](http://www.sensacine.com/) et [Filmstarts](http://www.filmstarts.de/).  
Trouvez des informations sur les films, stars, articles, horaires, etc, grâce à la classe **AlloHelper**.  
Il est possible de manipuler les données reçues grâce à la classe **AlloData** (optionnel).  
Et en bonus, vous pouvez modifier simplement les posters et images stockés sur Allociné en changeant l'URL grâce à la classe **AlloImage**.

### Installation

C'est juste un script, copiez-le dans le répertoire souhaité et faites un `require_once "./api-allocine-helper.php";` dans votre code.

### Usage

L'utilisation est très simple, néanmoins il est fortement conseillé de connaître la POO, et le bloc `try{} catch(){}`.
Exemple d'utilisation pour récupérer les informations d'un film:

Premièrement, créer un objet `AlloHelper`:

```
<?php
    // Inclure le script
    require_once "./api-allocine-helper.php";
    
    // Créer l'objet
    $helper = new AlloHelper;
    
```

Pour plus de clareté, on définit les paramètres à l'avance: le code du film, et la quantité d'informations a récupérer.

```
    $code = 27061;
    $profile = 'small';
    
```

Ensuite, il est conseillé d'effectuer des requêtes dans un bloc `try{} catch(){}` pour la gestion des exceptions.

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
