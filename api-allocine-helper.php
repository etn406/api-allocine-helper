<?php
    
    /**
    * API Allociné Helper 2
    * =====================
    * 
    * Utiliser plus facilement l'API d'Allociné.fr, de Screenrush.co.uk, de Filmstarts.de, de Beyazperde.com, de Sensacine.com ou de Adorocinema.com pour récupérer des informations sur les films, stars, séances, cinés, news, etc...
    * Il est possible de supprimer la classe AlloData sans autre modification du code pour éviter son utilisation.
    * 
    * Codes des erreurs:
    * ------------------
    * 1. L'extension PHP cURL n'est pas disponible.
    * 2. Erreur durant la récupération des données sur le serveur d'Allociné.
    * 3. Erreur durant la conversion des données JSON en array.
    * 4. Les mots-clés pour la recherche doivent contenir plus d'un caractère.
    * 5. Allociné a retourné une erreur (Le message de l'erreur est le message de l'ErrorException).
    * 6. offset inexistant (Uniquement dans la classe AlloData).
    * 7. Ce n'est pas un lien vers une image qui a été fournit en paramètre à la méthode __construct() de la classe AlloImage.
    * 8. L'extension PHP JSON n'est pas disponible.
    * 
    * 
    * @licence http://creativecommons.org/licenses/by-sa/3.0/fr/
    * @author Etienne Gauvin <etienne@gauvin.info>
    * @version 2.3
    */
    
    ###################################################################
    ## Modifier les constantes ci-dessous en fonction de vos besoins ##
    ###################################################################
    
    /**
    * Clé secrète
    * @var string
    */
    
    define('ALLOCINE_SECRET_KEY', '29d185d98c984a359e6e6f26a0474269');
      
      
    /**
    * L'URL de l'API et du serveur des images (par défaut).
    * The URL of the API and the server images (default).
    * @var string
    */
    
    # Allociné.fr, France
    define('ALLO_DEFAULT_URL_API', "api.allocine.fr");
    define('ALLO_DEFAULT_URL_IMAGES', "images.allocine.fr");
    
    
    /** Languages (except french) don't work anymore :/ **/
    
    # Screenrush.co.uk, United-Kingdom
    // define('ALLO_DEFAULT_URL_API', "api.screenrush.co.uk");
    // define('ALLO_DEFAULT_URL_IMAGES', "images.screenrush.co.uk");
    
    # Beyazperde.com, Türkiye
    // define('ALLO_DEFAULT_URL_API', "api.beyazperde.com");
    // define('ALLO_DEFAULT_URL_IMAGES', "tri.acimg.net");
    
    # Filmstarts.de, Deutschland
    // define('ALLO_DEFAULT_URL_API', "api.filmstarts.de");
    // define('ALLO_DEFAULT_URL_IMAGES', "bilder.filmstarts.de");
    
    # Sensacine.com, España
    // define('ALLO_DEFAULT_URL_API', "api.sensacine.com");
    // define('ALLO_DEFAULT_URL_IMAGES', "imagenes.sensacine.com");
    
    # Adorocinema.com, Brasil
    // define('ALLO_DEFAULT_URL_API', "api.adorocinema.com");
    // define('ALLO_DEFAULT_URL_IMAGES', "br.web.img1.acsta.net");
    
    
    /**
    * Activer/désactiver les Exceptions
    * Enable/disable Exceptions
    * 
    * @var bool
    */
    
    define('ALLO_THROW_EXCEPTIONS', true);
    
    
    /**
    * Décoder de l'UTF8 les données réceptionnées
    * Automatically decode the received data from UTF8
    * 
    * @var bool
    */
    
    define('ALLO_UTF8_DECODE', true);
    
    
    /**
    * Le partenaire utilisé pour toutes les requêtes.
    * The partner used for all requests.
    * 
    * @var string
    */
    
    define('ALLO_PARTNER', '100043982026'); 
    
    
    /**
    * Activer la détection des problèmes d'apostrophes.
    * 
    * @var bool
    */
    
    define('ALLO_AUTO_CORRECT_APOSTROPHES', true);
    
    
    /**
     * Inclusion des fichiers
     */
    
    include_once "AlloHelper.class.php";
    include_once "AlloData.class.php";
    include_once "AlloImage.class.php";
    
    