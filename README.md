API Allociné Helper PHP
=======================

How modify language:
--------------------

The API get by default information from Allocine.com (french), but you can change this for get from over server.

### Dynamically change:

You can modify dynamically the server, but changements are erased when PHP is closed.
French (fr), english (en), spanish (es) and german (de) are available, example:

```
    AlloHelper::lang('en');
    
    // Language is now english, data and images are retrieved from Screenrush.co.uk
```

### Change default language:

For modify the default language, just comment/uncomment corresponding lines at the beginning of the file `api-allocine-helper.php`:

```
    # Allociné.fr, France
    // define( 'ALLO_DEFAULT_URL_API', "api.allocine.fr" );
    // define( 'ALLO_DEFAULT_URL_IMAGES', "images.allocine.fr" );
    
    # Screenrush.co.uk, United-Kingdom
    // define( 'ALLO_DEFAULT_URL_API', "api.screenrush.co.uk" );
    // define( 'ALLO_DEFAULT_URL_IMAGES', "images.screenrush.co.uk" );
    
    # Filmstarts.de, Deutschland
    define( 'ALLO_DEFAULT_URL_API', "api.filmstarts.de" );
    define( 'ALLO_DEFAULT_URL_IMAGES', "bilder.filmstarts.de" );
    
    # Sensacine.com, España
    // define( 'ALLO_DEFAULT_URL_API', "api.sensacine.com" );
    // define( 'ALLO_DEFAULT_URL_IMAGES', "imagenes.sensacine.com" );
    
    // Language is now german, data and images are retrieved from Filmstarts.de
```

English:
--------

API Allociné Helper is support for using the API of Allociné, of Screenrush, of Sensacine and of Filmstarts: find lots of information about movies, people, tv series, etc.

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

L'API Allociné Helper permet d'utiliser plus facilement l'API d'Allociné, Screenrush, Sensacine ou Filmstarts pour récupérer un maximum d'informations à propos de films, stars, séries TV, etc.

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
