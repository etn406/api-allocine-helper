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
    * 1. Aucune fonction de récupération de données distantes n'est disponible (php_curl|file_get_contents).
    * 2. Erreur durant la récupération des données sur le serveur d'Allociné.
    * 3. Erreur durant la conversion des données JSON en array.
    * 4. Les mots-clés pour la recherche doivent contenir plus d'un caractère.
    * 5. Allociné a retourné une erreur (Le message de l'erreur est le message de l'ErrorException).
    * 6. offset inexistant (Uniquement dans la classe AlloData).
    * 7. Ce n'est pas un lien vers une image qui a été fournit en paramètre à la méthode __construct() de la classe AlloImage. 
    * 
    * 
    * @licence http://creativecommons.org/licenses/by-sa/3.0/fr/
    * @author Etienne Gauvin <etiennegauvin41@gmail.com>
    * @version 2.2
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
    
    
    ###################################################################
    
    
    /**
    * Exécuter les requêtes et traiter les données.
    */
    
    class AlloHelper
    {
        
        /**
         * Contient la dernière ErrorException
         * @var ErrorException|null
         */
        
        private static $_lastError;
        
        
        /**
         * Provoquer une ErrorException et/ou retourne la dernière provoquée.
         * 
         * @param string $message=null Le message de l'erreur
         * @param int $code=0 Le code de l'erreur
         * @return ErrorException|null
         */
        
        public static function error($message = null, $code = 0)
        {
            if ($message !== null)
            {
                $error = new ErrorException($message, $code);
                
                self::$_lastError = $error;
                
                if (ALLO_THROW_EXCEPTIONS)
                    throw $error;
            }
            
            return self::$_lastError;
        }
        
        
        /**
         * Contient l'adresse du site où chercher les données.
         * @var string
         */
        
        public static $APIUrl = ALLO_DEFAULT_URL_API;
        
        
        /**
         * Contient l'adresse du site où chercher les images.
         * @var string
         */
        
        public static $imagesUrl = ALLO_DEFAULT_URL_IMAGES;
        
        
        /**
         * Modifier le langage.
         * Les initiales du langage sont telles que défini dans la liste des codes ISO 639-1.
         * Le français (fr), l'allemand (de), l'anglais (en), le turque (tr) et l'espagnol (es) sont disponibles.
         * 
         * @see http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         * 
         * @param string $lang=null Les initiales du langage.
         */
        
        public static function lang($lang = null)
        {
            switch((string) $lang)
            {
                case 'de': case 'filmstarts.de':
                    self::$APIUrl = "api.filmstarts.de";
                    self::$imagesUrl = "bilder.filmstarts.de";
                break;
                
                case 'es': case 'sensacine.com':
                    self::$APIUrl = "api.sensacine.com";
                    self::$imagesUrl = "imagenes.sensacine.com";
                break;
                
                case 'fr': case 'allocine.fr':
                    self::$APIUrl = "api.allocine.fr";
                    self::$imagesUrl = "images.allocine.fr";
                break;
                
                case 'en': case 'screenrush.co.uk':
                    self::$APIUrl = "api.screenrush.co.uk";
                    self::$imagesUrl = "images.screenrush.co.uk";
                break;
                
                case 'tr': case 'beyazperde.com':
                    self::$APIUrl = "api.beyazperde.com";
                    self::$imagesUrl = "tri.acimg.net";
                break;
            }
        }
        
        
        /**
         * Préréglages pour les paramètres d'URL
		 * @var array
         */
        
        private $_presets = array();
        
        
        /**
         * Gestionnaire cURL utilisé pour la prochaine requête.
         * @var resource
         */
        
        private $_cURL;
        
        
        /**
         * Ajouter/modifier des préréglages.
         * 
         * @param array|string $preset Si c'est un array alors chaque paire "clé" => "valeur" ou "clé=valeur" sera enregistrée dans les préréglages, sinon si c'est une chaîne alors c'est le nom du préréglage et $value est sa valeur.
         * @param string|array|int $value La valeur du préréglage si $preset est une chaîne de caractères.
         * @return this
         */
        
        public function set($preset, $value=null)
        {
            if (is_array($preset))
                foreach($preset as $name => $value)
                    $this->_presets[ (string) $name ] = $value;
            
            elseif (is_string($preset))
                $this->_presets[ $preset ] = $value;
            
            return $this;
        }
        
        
        /**
         * Retourne les préréglages.
         * 
         * @param string|null $preset=null Indiquer le nom d'un préréglage pour connaître sa valeur.
         * @return mixed
         */
        
        public function getPresets($preset = null)
        {
            if ($preset === null)
                return $this->_presets;
            else
                return @$this->_presets[$preset];
        }
        
        
        /**
         * Effacer un/des préréglages.
         * 
         * @param array $presets=array() Indiquer les préréglages à effacer ou laisser vide pour tout effacer.
         * @param bool $inverse=false Si $inverse vaut true alors tous les préréglages seront effacés sauf ceux indiqués dans $presets.
         * @return this
         */
        
        public function clearPresets($presets = array(), $inverse = false)
        {
            if (empty($presets))
                $this->_presets = array();
            else {
                if ($inverse)
                    foreach($this->_presets as $psn => $ps)
                        if (!in_array($psn, $presets))
                            unset($this->_presets[$psn]);
                else
                    foreach($presets as $ps)
                        unset($this->_presets[$ps]);
            }
            
            return $this;
        }
        
        
        /**
         * Informations sur la dernière requête.
         * @var array
         */
        
        protected $lastRequest;
        
        
        /**
         * Retourne les information sur la dernière requête.
         * [URL, IP, userAgent, presets, rawData]
         * @return array|null
         */
        
        public function getRequestInfos()
        {
          if (!empty($this->lastRequest))
            return $this->lastRequest;
        }
        
        
        /**
         * Retourne un URL créé à partir de différentes données.
         * Les paramètres seront ajoutés dans l'ordre, sous leur forme "clé=valeur" ou "valeur" si il n'y a pas de clé.
         * Si c'est un array les sous éléments seront implosés et séparés par des virgules "clé" => array("val1", "val2", "val3") deviendra "clé=val1,val2,val3"
         * Les valeurs et les clés ne passent pas par la fonction urlencode !
         * 
         * @param string $type Le type de données à récupérer (exemple: "rest/v3/movie")
         */
        
        protected function creatURL($type)
        {
            $this->set(array(
                'format' => 'json',
                'partner' => ALLO_PARTNER,
            ));
			            
            $queryURL = ALLO_DEFAULT_URL_API . '/' . $type;
			      $searchQuery = str_replace('%2B', '+', http_build_query($this->getPresets())) . '&sed=' . date('Ymd');
			      $toEncrypt = ALLOCINE_SECRET_KEY . $searchQuery;
			      $sig = urlencode(base64_encode(sha1($toEncrypt, true)));
			      $queryURL .= '?' . $searchQuery . '&sig=' . $sig;
			
			      return $queryURL;
        }
        
        
        /**
         * Retourne un user-agent aléatoire.
         * @return string
         */
        
        public static function getRandomUserAgent()
        {
            $v = rand(1, 4) . '.' . rand(0, 9);
            $a = rand(0, 9);
            $b = rand(0, 99);
            $c = rand(0, 999);
          
            $userAgents = array(
                "Mozilla/5.0 (Linux; U; Android $v; fr-fr; Nexus One Build/FRF91) AppleWebKit/5$b.$c (KHTML, like Gecko) Version/$a.$a Mobile Safari/5$b.$c",
                "Mozilla/5.0 (Linux; U; Android $v; fr-fr; Dell Streak Build/Donut AppleWebKit/5$b.$c+ (KHTML, like Gecko) Version/3.$a.2 Mobile Safari/ 5$b.$c.1",
                "Mozilla/5.0 (Linux; U; Android 4.$v; fr-fr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30",
                "Mozilla/5.0 (Linux; U; Android 4.$v; fr-fr; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30",
                "Mozilla/5.0 (Linux; U; Android $v; en-gb) AppleWebKit/999+ (KHTML, like Gecko) Safari/9$b.$a",
                "Mozilla/5.0 (Linux; U; Android $v.5; fr-fr; HTC_IncredibleS_S710e Build/GRJ$b) AppleWebKit/5$b.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Vision Build/GRI$b) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android $v.4; fr-fr; HTC Desire Build/GRJ$b) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; T-Mobile myTouch 3G Slide Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android $v.3; fr-fr; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; LG-LU3000 Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_DesireS_S510e Build/GRI$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_DesireS_S510e Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile",
                "Mozilla/5.0 (Linux; U; Android $v.3; fr-fr; HTC Desire Build/GRI$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Desire Build/FRF$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/$a.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android $v; fr-lu; HTC Legend Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/$a.$a Mobile Safari/$c.$a",
                "Mozilla/5.0 (Linux; U; Android $v; fr-fr; HTC_DesireHD_A9191 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
                "Mozilla/5.0 (Linux; U; Android $v.1; fr-fr; HTC_DesireZ_A7$c Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.$a",
                "Mozilla/5.0 (Linux; U; Android $v.1; en-gb; HTC_DesireZ_A7272 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.1",
                "Mozilla/5.0 (Linux; U; Android $v; fr-fr; LG-P5$b Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"
            );
            
			      return $userAgents[rand(0, count($userAgents) - 1)];
        }
        
        
        /**
          * Retourne le gestionnaire cURL qui sera utilisé pour la prochaine requête.
          * 
          * @see http://php.net/manual/fr/ref.curl.php
          * @see http://php.net/manual/fr/function.curl-setopt.php
          * @return resource|null
          * @throws ErrorException
          */
        
        public function getCURLHandler()
        {
            if (function_exists("curl_init"))
            {
                if ($this->_cURL === null)
                    return ($this->_cURL = curl_init());
                else
                    return $this->_cURL;
            }
            else
                $this->error("The extension php_curl must be enabled.", 1);
        }
        
        
        /**
         * Récupérer des données JSON et les convertir depuis un URL grâce à php_curl, ou à défaut file_get_contents().
         * 
         * @param string $url L'URL vers lequel aller chercher les données JSON.
         * @return array|false Un array contenant les données en cas de succès, false si une erreur est survenue.
         * @throws ErrorException
         */
        
        protected function getDataFromURL($url)
        {
            if (function_exists("curl_init"))
            {
                $curl = ($this->_cURL == null) ? curl_init() : $this->_cURL;
				        $userAgent = self::getRandomUserAgent();
				        $ip = rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255);
				        
                curl_setopt ($curl, CURLOPT_URL, $url);
                curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
				        curl_setopt ($curl, CURLOPT_USERAGENT, $userAgent);
				        
				        $headers[] = "REMOTE_ADDR: $ip";
				        $headers[] = "HTTP_X_FORWARDED_FOR: $ip";
				        
				        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        
				        $data = curl_exec($curl);
                curl_close($curl);
                
                $this->lastRequest = array(
                  'userAgent' => $userAgent,
                  'URL' => $url,
                  'IP' => $ip,
                  'presets' => $this->getPresets(),
                  'rawData' => $data
                );
            }
            
            else
            {
                $this->error("The extension php_curl must be installed with PHP and enabled.", 1);
                return false;
            }
            
            if (empty($data))
            {
                $this->error("An error occurred while retrieving the data.", 2);
                return false;
            }
            
            $data = @json_decode($data, 1);
            
            if (empty($data))
            {
                $this->error("An error occurred when converting data.", 3);
                return false;
            }
            else return $data;
        }
        
        
        /*
         * Méthodes de récupération des données
         */
        
        /**
         * Récupérer les données pour un type de données et un élément du tableau retourné donné.
         * Utilisé en interne pour diminuer et clarifier le code dans les méthodes ne nécessitant pas de traitement particulier sur leurs données.
         * 
         * @param string $type Voir AlloHelper::creatURL()
         * @param string $container L'élément contenant les données dans le tableau retourné par Allociné
         * @return AlloData|array|false
         * @throws ErrorException
         */
        
        protected function getData($type, $container, &$url)
        {
            // Récupération des données
            $data = $this->getDataFromURL($url = $this->creatURL($type));
            
            // En cas d'erreur
            if (empty($data))
                return false;
                
            // Succès ($data est encore un array)
            else
            {
                if (empty($data['error']))
                    // On retourne les données
                    if (class_exists('AlloData'))
                        return new AlloData($data[$container]);
                    else
                        return $data;
                
                // En cas d'erreur signalée par Allociné
                else
                {
                    $this->error($data['error']['$'], 5);
                    return false;
                }
            }
        }
        
        /**
         * Effectuer une recherche sur Allociné.
         * Possibilité de trier les résultats de films par ressemblance avec la chaîne de recherche.
         * 
         * @param string $q La chaîne de recherche.
         * @param int $page=1 La page des résultats.
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param bool $sortMovies=false Réorganiser ou non les films selon la ressemblance entre leur titre et la chaîne de recherche.
         * @param array $filter=array() Filtrer les résultats pour gagner en rapidité. Peut-être remplit par "movietheater", "movie", "theater", "person", "news", "tvseries", "location", "character", "video" ou "photo".
         * @param &$url=null Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         * @throws ErrorException
         */
        
        public function search($q, $page = 1, $count = 10, $sortMovies = false, array $filter = array(), &$url = null)
        {
            
            // Traitement de la chaîne de recherche
            if (!is_string($q) || strlen($q) < 2)
            {
                $this->error("The keywords should contain more than one character.", 4);
                return false;
            }
            
            $accents = "àáâãäçèéêëìíîïñòóôõöùúûüýÿ'";
            $normal  = 'aaaaaceeeeiiiinooooouuuuyy ';
            $q = utf8_encode(strtr(strtolower(trim($q)), $accents, $normal));
            
            // Préréglages
            $this->set(array(
                'q' => urlencode($q),
                'filter' => (array) $filter,
                'count' => (int) $count,
                'page' => (int) $page
           ));
            
            // Création de l'URL
            $url = $this->creatURL('rest/v3/search');
            
            // Envoi de la requête
            $data = $this->getDataFromURL($url);
            
            // En cas d'erreur
            if (empty($data['error']))
            {
                $data = $data['feed'];
                
                if (!empty($data['movie']))
                {
                    foreach ($data['movie'] as $iresult => &$result)
                    {
                        $result['productionYear'] = (int) @$result['productionYear'];
                        $result['originalTitle'] = (string) @$result['originalTitle'];
                        
                        if (empty($result['title']))
                            $result['title'] = @$result['originalTitle'];
                        
                        $result['release'] = (array) @$result['release'];
                        $result['release']['releaseDate'] = (string) @$result['release']['releaseDate'];
                        
                        $result['statistics'] = (array) @$result['statistics'];
                        $result['statistics']['pressRating'] = (float) @$result['statistics']['pressRating'];
                        $result['statistics']['userRating'] = (float) @$result['statistics']['userRating'];
                        
                        $result['castingShort'] = (array) @$result['castingShort'];
                        $result['castingShort']['directors'] = (string) @$result['castingShort']['directors'];
                        $result['castingShort']['actors'] = (string) @$result['castingShort']['actors'];
                        
                        if (!empty($result['poster']['href']))
                            $result['poster'] = new AlloImage($result['poster']['href']);
                        else
                            $result['poster'] = new AlloImage();
                        
                        $result['posterURL'] = $result['poster']->url();
                        $result['link'] = (array) @$result['link'];
                    }
                }
                
                // Réorganisation des films
                if ($sortMovies && !empty($data['movie']))
                {
                    $movies = &$data['movie'];
                    $resultats = array();
                    
                    // Tableau contenant $cleFilm => $similitude
                    $similitudes = array();
                    
                    // Oncalcule la distance de levenstein entre la chaîne de recherche et le titre pour chaque film
                    foreach ($movies as $i => &$m)
                        $similitudes[$i] = levenshtein($q, strtr(strtolower($m['title']), $accents, $normal));
                    
                    // On réorganise le tableau des similitudes, mais en gardant les clés.
                    asort($similitudes, true);
                    
                    // On remplit le tableau des résultats dans l'ordre des similitudes.
                    foreach ($similitudes as $i => $sim)
                        $resultats[] = $movies[$i];
                    
                    
                    $data['movieSorted'] = $resultats;
                    $data['movie'] = $movies;
                }
                
                // Réorganisation des compteurs des résultats
                if (!empty($data['results']))
                {
                    foreach ($data['results'] as $r)
                        $data['results'][$r['type']] = (int) $r['$'];
                }
                
                // On retourne les données
                if (class_exists('AlloData'))
                    return new AlloData($data);
                else
                    return $data;
            }
            
            // En cas d'erreur signalée par Allociné
            else
            {
                $this->error($data['error']['$'], 5);
                return false;
            }
        }
        
        
        /**
         * Récupérer les critiques des spectateurs et de la presse à propos d'un film, d'une série TV.
         * 
         * @param int $code L'identifiant du film/de la série.
         * @param string $filter='press' Le type de critique ('press' ou 'public') à renvoyer.
         * @param string $type='movie' Le type de données ("movie" ou "tvseries") auquel faire correspondre l'identifiant $code.
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param int $page=1 La page des résultats.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function reviewlist($code, $filter='press', $type='movie', $count = 10, $page = 1, &$url = null)
        {
            // Type de critiques (presse/public)
            switch ($filter)
            {
                case 'press': case 'presse': case 'desk-press':
                $filter = 'desk-press';
                break;
                
                default:
                $filter = 'public';
            }
            
            // Préréglages
            $this->set(array(
                'code' => $code,
                'filter' => (array) $filter,
                'type' => (string) $type,
                'count' => (int) $count,
                'page' => (int) $page
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/reviewlist', 'feed', $url);
        }
        
        
        /**
         * Récupérer une liste de films en fonction de différents paramètres.
         * 
         * @param string $filter='nowshowing' Le type de résultats à afficher: 'nowshowing' (films au cinéma) ou 'comingsoon' (bientôt au cinéma);
         * @param string $order='dateasc' L'ordre dans lequel afficher les données: 'dateasc' (chronologique), 'datedesc' (anti-chronologique), 'theatercount' (nombre de salles) ou 'toprank' (popularité).
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param int $page=1 La page des résultats.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function movielist($filter=array('nowshowing'), $order=array('dateasc'), $count = 10, $page = 1, &$url = null)
        {
            // Préréglages
            $this->set(array(
                'filter' => (array) $filter,
                'order' => (array) $order,
                'count' => (int) $count,
                'page' => (int) $page
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/movielist', 'feed', $url);
        }
        
        
        /**
         * Récupérer une liste de cinémas et la liste des films qui y passent actuellement en fonction d'un code postal.
         * 
         * @param mixed $zip Le code postal de la ville du/des cinéma(s).
         * @param $date=null Spécifier une date pour les horaires.
         * @param $movieCode=null Spécifier les horaires d'un film (par identifiant).
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param int $page=1 La page des résultats.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function showtimesByZip($zip, $date=null, $movieCode=null, $count = 10, $page = 1, &$url = null)
        {
            // Préréglages
            $this->set('zip', $zip);
            $this->set('count', (int) $count);
            $this->set('page', (int) $page);
            
            if ($date !== null)
                $this->set('date', $date);
            
            if ($movieCode !== null)
                $this->set('movie', $movieCode);
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/showtimelist', 'feed', $url);
        }
        
        
        
        /**
         * Récupérer une liste de cinémas et la liste des films qui y passent actuellement en fonction de coordonnées géographiques (latitude, longitude [, rayon]).
         * 
         * @param float $lat La coordonnée latitude du cinéma.
         * @param float $long La coordonnée longitude du cinéma.
         * @param int $radius Le rayon dans lequel chercher.
         * @param $date=null Spécifier une date pour les horaires.
         * @param $movieCode=null Spécifier les horaires d'un film (par identifiant).
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param int $page=1 La page des résultats.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function showtimesByPosition($lat, $long, $radius=10, $date=null, $movieCode=null, $count = 10, $page = 1, &$url = null)
        {
            // Préréglages
            $this->set('lat', (float) $lat);
            $this->set('long', (float) $long);
            $this->set('radius', (int) $radius);
            $this->set('count', (int) $count);
            $this->set('page', (int) $page);
            
            if ($date !== null)
                $this->set('date', $date);
            
            if ($movieCode !== null)
                $this->set('movie', $movieCode);
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/showtimelist', 'feed', $url);
        }
        
        
        
        /**
         * Récupérer une liste de cinémas et la liste des films qui y passent actuellement en fonction d'un ou de plusieurs identifiant(s) de cinéma(s);
         * 
         * @param array|string $theaters Un identifiant/une liste d'identifiants de cinéma(s).
         * @param $date=null Spécifier une date pour les horaires.
         * @param $movieCode=null Spécifier les horaires d'un film (par identifiant).
         * @param int $count=10 Le nombre maximum de résultats par page.
         * @param int $page=1 La page des résultats.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function showtimesByTheaters($theaters, $date=null, $movieCode=null, $count = 10, $page = 1, &$url = null)
        {
            // Préréglages
            $this->set('theaters', (array) $theaters);
            $this->set('count', (int) $count);
            $this->set('page', (int) $page);
            
            if ($date !== null)
                $this->set('date', $date);
            
            if ($movieCode !== null)
                $this->set('movie', $movieCode);
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/showtimelist', 'feed', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur un film.
         * 
         * @param int $code L'identifiant du film.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         * @throws ErrorException
         */
        
        public function movie($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Création de l'URL
            $url = $this->creatURL('rest/v3/movie');
            
            // Envoi de la requête
            $data = $this->getDataFromURL($url);
            
            // En cas d'erreur
            if (empty($data))
                return false;
                
            // Succès ($data est encore un array)
            else
            {
                if (empty($data['error']))
                {
                    $data = $data['movie'];
                    
                    // Remplacer "title" par "originalTitle" (si il n'existe pas)
                    if (empty($data['title']))
                        $data['title'] = $data['originalTitle'];
                    
                    // Poster
                    if (!empty($data['poster']) and !empty($data['poster']['href']))
                        $data['poster'] = new AlloImage($data['poster']['href']);
                    else
                        $data['poster'] = new AlloImage();
                        
                    // On retourne les données
                    if (class_exists('AlloData'))
                        return new AlloData($data);
                    else
                        return $data;
                }
                
                // En cas d'erreur signalée par Allociné
                else
                {
                    $this->error($data['error']['$'], 5);
                    return false;
                }
            }
        }
        
        
        /**
         * Récupérer toutes les informations sur un article.
         * 
         * @param int $code L'identifiant de l'article.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function news($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/news', 'news', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur une personne.
         * 
         * @param int $code L'identifiant de la personne.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function person($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/person', 'person', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur un media (vidéo/photo).
         * 
         * @param int $code L'identifiant du media.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function media($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/media', 'media', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur la filmographie d'une personne.
         * 
         * @param int $code L'identifiant de la personne.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function filmography($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/filmography', 'person', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur une série TV.
         * 
         * @param int $code L'identifiant de la série TV.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function tvserie($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/tvseries', 'tvseries', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur une saison d'une série TV.
         * 
         * @param int $code L'identifiant de la saison.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function season($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/season', 'season', $url);
        }
        
        
        /**
         * Récupérer toutes les informations sur un épisode d'une saison d'une série TV.
         * 
         * @param int $code L'identifiant de l'épisode.
         * @param int $profile='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
         * @param &$url Contiendra l'URL utilisé.
         * 
         * @return AlloData|array|false
         */
        
        public function episode($code, $profile = 'medium', &$url = null)
        {
            // Profile (quantité d'informations)
            switch($profile)
            {
                case 'small': break;
                case 'large': break;
                default: $profile = 'medium'; break;
                
                case 1: $profile = 'small'; break;
                case 3: $profile = 'large';
            }
            
            // Préréglages
            $this->set(array(
                'code' => (int) $code,
                'profile' => (string) $profile,
           ));
            
            // Récupération et revoi des données
            return $this->getData('rest/v3/episode', 'episode', $url);
        }
        
    }
    
    
    /**
    * Manipuler facilement les données reçues.
    * Il est possible de supprimer complètement cette classe sans autre modification du code.
    * 
    * @implements ArrayAccess, SeekableIterator, Countable
    */
    
    class AlloData implements ArrayAccess, SeekableIterator, Countable
    {
        
        /**
         * Contiendra les données
         * @var array
         */
        
        private $_data = array();
        
        
        /**
         * Valeur de remplacement pour les symboles '$' ou false pour ne rien modifier.
         */
        
        const REPLACEMENT_OF_DOLLAR_SIGN = 'value';
        
        
        /**
         * Décoder une variable depuis l'UTF8.
         * 
         * @param mixed $var Seules les chaînes sont décodées, mais aucune erreur ne sera provoquée si ce n'en est pas une.
         * @param mixed $tab=false Si ce paramètre vaut true alors le tableau sera parcouru de manière récursive et toutes les chaînes de caractèrezs seront converties.
         * @return array|string Le tableau|la chaîne décodé(e)
         */
        
        public static function utf8_decode($var, $tab = false)
        {
            if (ALLO_UTF8_DECODE)
            {
                if (is_string($var)) return utf8_decode(str_replace('â€™', "'", $var));
                elseif (!is_array($var) || !$tab) return $var;
                else
                {
                    $return = array();
                    foreach ($var as $i => $cell)
                        $return[utf8_decode($i)] = self::utf8_decode($cell, true);
                    return $return;
                }
            }
            else
                return $var;
        }
        
        
        /**
         * Constructeur
         */
        
        public function __construct($data)
        {
            $this->_data = (array) $data;
        }
        
        
        /**
         * Retourne un pointeur sur une valeur existante dans les données enregistrées, ou null si elle n'existe pas.
         * 
         * @param $offset=null Retourne un pointeur sur tout le tableau si $offset==null
         * @return Une référence vers la valeur demandée, ou null si elle n'existe pas.
         * @throws ErrorException
         */
        
        protected function &_getProperty($offset = null, $ignoreException = false)
        {
            $data = &$this->_data;
            
            if ($offset === null)
                return $data;
            
            else
            {
                if (isset($data[$offset]))
                    return $data[$offset];
                
                
                elseif ($offset == self::REPLACEMENT_OF_DOLLAR_SIGN && isset($data['$']))
                    return $data['$'];
                
                else
                {
                    if (!$ignoreException)
                        AlloHelper::error("This offset ($offset) does not exist.", 6);
                    
                    // Eviter une erreur en retournant une référence, si les exceptions sont désactivées.
                    $b = null;
                    $a =&$b;
                    
                    return $a;
                }
            }
        }
        
        /**
         * Retourne les données sous forme d'un array
         * 
         */
        
        public function getArray()
        {
            return (array) self::utf8_decode($this->_getProperty(), true);
        }
        
        
        /**
         * Si l'on essaie d'accéder à une propriété inexistante (donc un élément de $this->_data)
         * 
         */
        
        public function __get($offset)
        {
            $data = $this->_getProperty($offset);
            if (is_array($data))
                return new AlloData($data);
            else return self::utf8_decode($data);
        }
        
        
        /**
         * Impossible de créer/modifier une propriété
         * 
         */
        
        public function __set($offset, $value)
        {
            $data = &$this->_getProperty($offset);
            $data = $value;
        }
        
        
        /*
         * Implémentation des interfaces
         */
        
        /**
         * Pointeur interne
         * @var int
         */
        
        private $_position = 0;
        
        /**
         * Retourne la valeur de l'index courant.
         */
        
        public function current()
        {
            $data = $this->_getProperty($this->_position);
            if (is_array($data))
                return new AlloData($data);
            else return self::utf8_decode($data);
        }
        
        /**
         * Retourne true ou false selon l'existence ou non d'une occurence dans les données.
         */
        
        public function valid()
        {
            return ($this->_getProperty($this->_position, true) !== null);
        }
        
        /**
         * Retourne la position actuelle.
         */
        
        public function key()
        {
            return $this->_position;
        }
        
        /**
         * Incrémente l'index.
         */
        
        public function next()
        {
            $this->_position++;
        }
        
        
        /**
         * Réinitialise l'index.
         */
        
        public function rewind()
        {
            $this->_position = 0;
        }
        
        
        /**
         * Pour modifier directement la position dans le tableau.
         * 
         * @param int $newPosition
         * @throws ErrorException
         */
        
        public function seek($newPosition)
        {
            $lastPosition = $this->_position;
            $this->_position = $newPosition;
            
            if (!$this->valid())
            {
                AlloHelper::error("This offset ($offset) does not exist.", 6);
                $this->position = $anciennePosition;
            }
        }
        
        
        /**
         * Retourne le nombre d'occurences dans le tableau.
         * 
         * @return int
         */
        
        public function count()
        {
            return count($this->_data);
        }
        
        /**
         * Si l'on essaie d'accéder à l'objet comme à un tableau.
         * 
         * @param string|int $offset
         * @return mixed
         */
        
        public function offsetGet($offset)
        {
            $data = $this->_getProperty($offset);
            if (is_array($data))
                return new AlloData($data);
            else return self::utf8_decode($data);
        }
        
        
        /**
         * Si l'on veut de créer/modifier une propriété (interface ArrayAccess)
         * 
         * @param string|int $offset
         * @param mixed $value
         */
        
        public function offsetSet($offset, $value)
        {
            $data = &$this->_getProperty($offset);
            $data = $value;
        }
        
        
        /**
         * Lors de la vérification de l'existence d'une propriété avec isset (interface ArrayAccess)
         * 
         * @param string|int $offset
         * @return bool
         */
        
        public function offsetExists($offset)
        {
            return ($this->_getProperty($offset, true) !== null);
        }
        
        
        /**
         * Lors de la vérification de l'existence d'une propriété avec isset (interface ArrayAccess)
         * 
         * @param string|int $offset
         * @return bool
         */
        
        public function __isset($offset)
        {
            return ($this->_getProperty($offset, true) !== null);
        }
        
        
        /**
         * Il n'est pas possible de détruire une la variable référencée, seule la référence est détruite...
         * De toute façon ça n'a pas d'utilité.
         */
        
        public function offsetUnset($offset)
        {
            return;
        }
        
        
        /**
         * Coller toutes les valeurs/sous-valeurs du tableau associatif.
         * Exemple : $film->genre->implode() collera toutes les valeurs de $film->genre[i]->value avec des virgules.
         * 
         * @param string $separator=', '         Le séparateur des valeurs.
         * @param string $lastSeparator=' et '   Le séparateur des dernière et l'avant-dernière valeurs.
         * @param string $offset='value'         Les offsets à concaténer ('$' == 'value').
         * @return string
         */
        
        public function implode($separator = ', ', $lastSeparator = ' & ', $offset = 'value')
        {
            $tab = (array) $this->_getProperty();
            
            if (count($tab) === 1)
            {
                $data = new AlloData($tab[0]);
                if (isset($data[$offset]) && is_string($data[$offset]))
                    return $data[$offset];
            }
            
            elseif (count($tab) < 1)   return '';
            
            $values = array();
            
            foreach ($tab as $i => $stab)
            {
                $data = new AlloData($stab);
                if (isset($data[$offset]) && is_string($data[$offset]))
                    $values[] = $data[$offset];
            }
            
            $last = array_slice($values, -1, 1);
            
            if ($values)
                return implode((string) $separator, array_slice($values, 0, -1)) . ((count($values) > 1) ? (string) $lastSeparator . $last[0] : '');
            else
                return '';
        }
        
    }
    
    
    /**
    * Manipuler facilement les URLs des images.
    * 
    */
    
    class AlloImage
    {
        
        /**
         * Répertoire de l'image par défaut
         * @const string
         */
        
        const DEFAULT_IMAGE_PATH = "commons/emptymedia/AffichetteAllocine.gif";
        
        
        /**
         * Liste des icônes diponibles
         * @var array
         */
        
        public static $icons = array(
            'play.png' => null,
            'overplay.png' => null,
            'overlayVod120.png' => array('r', 120, 160),
       );
        
        
        /**
         * Contient les paramètres de l'icône.
         * @var array|false
         */
        
        private $imageIcon = false;
        
        
        /**
         * Contient les paramètres de la bordure
         * @var array|false
         */
        
        private $imageBorder = false;
        
        
        /**
         * Contient les paramètres de la taille de l'image.
         * @var array|false
         */
        
        private $imageSize = false;
        
        
        /**
         * Contient l'adresse du serveur de l'image.
         * @var string
         */
        
        private $imageHost;
        
        
        /**
         * Contient le répertoire de l'image sur Allociné.
         * @var string
         */
        
        private $imagePath;
        
        
        /**
         * Image par défaut
         * 
         * @return this
         */
        
        public function reset()
        {
            $this->destroyBorder();
            $this->destroyIcon();
            $this->maxSize();
            
            return $this;
        }
        
        /**
         * Modifier l'icône sur l'image.
         * 
         * @param string $position='c' La position de l'icône par rapport au centre de l'image (en une ou deux lettres), d'après la rose des sable. Renseigner une position invalide (telle que 'c') pour centrer l'icône.
         * @param int $margin=4 Le nombre de pixel entre l'icône et le(s) bord(s) le(s) plus proche(s).
         * @param string $icon='play.png' Le nom de l'icône à ajouter. La liste des icônes se trouve dans AlloImage::$icons.
         * @return this
         */
        
        public function icon($position='c', $margin=4, $icon='play.png')
        {
            if (!empty($this->icons[$icon]))
            {
                $p = $this->icons[$icon];
                
                switch ($p[0])
                {
                    case 'r': $this->resize($p[1], $p[2]); break;
                    case 'c': $this->cut($p[1], $p[2]); break;
                }
            }
            
            $this->imageIcon = array(
                'position' => substr($position, 0, 2),
                'margin' => (int) $margin,
                'icon' => (string) $icon
           );
            
            return $this;
        }
        
        
        /**
         * Renvoie les paramètres enregistrés pour l'icône.
         * 
         * @return array|false
         */
        
        public function getIcon()
        {
            return $this->imageIcon;
        }
        
        
        /**
         * Efface les paramètres enregistrés pour l'icône.
         * 
         * @return this
         */
        
        public function destroyIcon()
        {
            $this->imageIcon = false;
            return $this;
        }
        
        
        /**
         * Modifier la bordure de l'image.
         * 
         * @param int $size=1 L'épaisseur de la bordure en pixels.
         * @param string $color='000000' La couleur de la bordure en hexadécimal (sans # initial). [http://en.wikipedia.org/wiki/Web_colors#Hex_triplet]
         * @return this
         */
        
        public function border($size=1, $color="000000")
        {
            $this->imageBorder = array(
                'size' => (int) $size,
                'color' => (string) $color
           );
            
            return $this;
        }
        
        
        /**
         * Renvoie les paramètres enregistrés de la bordure.
         * 
         * @return array|false
         */
        
        public function getBorder()
        {
            return $this->imageBorder;
        }
        
        
        /**
         * Efface la bordure.
         * 
         * @return this
         */
        
        public function destroyBorder()
        {
            $this->imageBorder = false;
            return $this;
        }
        
        
        /**
         * Modifier proportionnellement la taille de l'image au plus petit.
         * Si les deux paramètres sont laissés tels quels ($xmax='x' et $ymax='y'), l'image sera de taille normale.
         * Appeler cette fonction efface les paramètres enregistrés pour AlloImage::cut() (Les deux méthodes ne peuvent être utilisées en même temps).
         * 
         * @param int $xmax='x' La largeur maximale de l'image, en pixels. Laisser 'x' pour une largeur automatique en fonction de $ymax.
         * @param int $ymax='y' La hauteur maximale de l'image, en pixels. Laisser 'y' pour une hauteur automatique en fonction de $xmax.
         * @return this
         */
        
        public function resize($xmax='x', $ymax='y')
        {
            $this->imageSize = array(
                'method' => 'r',
                'xmax' => $xmax,
                'ymax' => $ymax
           );
            
            return $this;
        }
        
        
        /**
         * Redimensionner l'image au plus petit, puis couper les bords trop grands.
         * Appeler cette fonction efface les paramètres enregistrés pour AlloImage::resize() (Les deux méthodes ne peuvent être utilisées en même temps).
         * 
         * @param int $xmax La largeur maximale de l'image, en pixels.
         * @param int $ymax La hauteur maximale de l'image, en pixels.
         * @return this
         */
        
        public function cut($xmax, $ymax)
        {
            $this->imageSize = array(
                'method' => 'c',
                'xmax' => (int) $xmax,
                'ymax' => (int) $ymax
           );
            
            return $this;
        }
        
        
        /**
         * Retourne les paramètres enregistrés du redimensionnement/recoupe de l'image.
         * 
         * @return array|false
         */
        
        public function getSize()
        {
            return $this->imageSize;
        }
        
        
        /**
         * Règle l'image à sa taille maximale (Effacer redimensionnement/recoupe)
         * 
         * @return array|false
         */
        
        public function maxSize()
        {
            $this->imageSize = false;
            return $this;
        }
        
        
        /**
         * Retourne le host de l'image.
         * 
         * @return string
         */
        
        public function getImageHost()
        {
            return $this->imageHost;
        }
        
        
        /**
         * Modifier le serveur (host) de l'image.
         * 
         * @param string $server L'adresse sans slash du serveur (ex: 'images.allocine.fr'), le même paramètre que pour AlloHelper::lang(), ou 'default' pour régler selon le langage enregistré.
         * @return this
         */
        
        public function setImageHost($server)
        {
            switch ($server)
            {
                case 'default':
                case 'de': case 'filmstarts.de':
                case 'es': case 'sensacine.com':
                case 'fr': case 'allocine.fr':
                case 'en': case 'screenrush.co.uk':
                    $this->imageHost = self::$imagesUrl;
                break;
                
                default:
                    $this->imageHost = $server;
            }
            
            return $this;
        }
        
        
        /**
         * Créer une nouvelle image grâce à son URL.
         * Si l'url est invalide, l'image utilisée sera celle par défaut.
         * 
         * @param string $url=null L'URL de l'image.
         * @throws ErrorException
         */
        
        public function __construct($url = null)
        {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
            {
                $this->imageHost = AlloHelper::$imagesUrl;
                $this->imagePath = self::DEFAULT_IMAGE_PATH;
                
            }
            else
            {
                $urlParse = parse_url($url);
                
                $this->imageHost = !empty($urlParse['host']) ? $urlParse['host'] : AlloHelper::$imagesUrl;
                
                if (!empty($urlParse['path']))
                    $this->imagePath = $urlParse['path'];
                else
                    AlloHelper::error("This isn't a URL to an image.", 7);
                
                // Parsage de l'URL
                $explodePath = explode('/', $this->imagePath);
                
                // Première partie vide ?
                if (empty($explodePath[0]))
                    unset($explodePath[0]);
                
                // Détecte les paramètres jusqu'au début du path réel.
                foreach ($explodePath as $iPathPart => $pathPart)
                {
                    if (strpos($pathPart, '_') === false)
                        break;
                    else
                        unset($explodePath[$iPathPart]);
                    
                    // Icône
                    if (strpos($pathPart, 'o') === 0 && preg_match("#^o_(.+)_(.+)_(.+)$#i", $pathPart, $i) != false)
                    {
                        $this->icon($i[3], $i[2], $i[1]);
                    }
                    
                    // Bordure
                    elseif (strpos($pathPart, 'b') === 0 && preg_match("#^b[xy]?_([0-9]+)_([0-9a-f]{6}|.*)$#i", $pathPart, $i) != false)
                    {
                        if (preg_match("#^[0-9a-f]{6}$#i", $i[2]) == false)
                            $i[2] = "000000";
                        
                        $this->border($i[1], $i[2]);
                    }
                    
                    // Redimensionnement
                    elseif (preg_match("#^r[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)$#i", $pathPart, $i) != false)
                    {
                        $this->resize((int) $i[1], (int) $i[2]);
                    }
                    
                    // Recoupe
                    elseif (preg_match("#^c[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)$#i", $pathPart, $i) != false)
                    {
                        $this->cut((int) $i[1], (int) $i[2]);
                    }
                }
                
                $this->imagePath = implode('/', $explodePath);
            }
        }
        
        
        /**
         * Construit l'URL à partir des paramètres enregistrés.
         * @return string
         */
        
        public function url()
        {
            $params = array();
            
            // Taille
            if ($this->imageSize !== false)
                $params[] = "{$this->imageSize['method']}_{$this->imageSize['xmax']}_{$this->imageSize['ymax']}";
            
            // Bordure
            if ($this->imageBorder !== false)
                $params[] = "b_{$this->imageBorder['size']}_{$this->imageBorder['color']}";
            
            // Icône
            if ($this->imageIcon !== false)
                $params[] = "o_{$this->imageIcon['icon']}_{$this->imageIcon['margin']}_{$this->imageIcon['position']}";
            
            return "http://{$this->imageHost}" . (!empty($params) ? '/' . implode('/', $params) : '') . "/{$this->imagePath}";
        }
        
        
        /**
         * Alias de AlloImage::url()
         * 
         * @return string
         */
        
        public function __toString()
        {
            return $this->url();
        }
        
    }

