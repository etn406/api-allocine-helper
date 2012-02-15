API Allociné Helper PHP
=======================

English
-------

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
    
?>
```



Français
--------

L'API Allociné Helper permet d'utiliser plus facilement l'API d'Allociné, Screenrush, Sensacine ou Filmstarts pour récupérer un maximum d'informations à propos de films, stars, séries TV, etc.

### Installation

C'est juste un script, copiez-le dans le répertoire souhaité et faites un `require_once "./api-allocine-helper.php";` dans votre code.

### Usage

L'utilisation est très simple, néanmoins il est fortement conseillé de connaître la POO, et le bloc `try{} catch(){}`.

Premièrement, créer un objet `AlloHelper`:

```
<?php
    // Include the script
    require_once "./api-allocine-helper.php";
    
    // Creat the object
    $helper = new AlloHelper;
    
?>
```
