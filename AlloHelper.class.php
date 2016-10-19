<?php

    /**
    * Exécuter les requêtes et traiter les données.
    */
    
    class AlloHelper
    {

        /**
         * Contient la dernière ErrorException
         * @var ErrorException|null
         */
        
        public static $_lastError;

        /**
         * @var boolean Flag pour declencher des exceptions
         */
        protected $throwExceptions = ALLO_THROW_EXCEPTIONS;

        /**
         * @var string Clé secrète
         */
        protected $allocineSecretKey = ALLOCINE_SECRET_KEY;

        /**
         * @var bool Flag pour activer le decodage UTF8
         */
        protected $utf8Decode = ALLO_UTF8_DECODE;

        /**
         * @var bool Flag pour corriger les apostrophes
         */
        protected $autoCorrectApostrophe = ALLO_AUTO_CORRECT_APOSTROPHES;

        /**
         * @var string Le partenaire utilisé pour toutes les requêtes.
         */
        protected $partner = ALLO_PARTNER;

        /**
         * Contient l'adresse du site où chercher les données.
         * @var string
         */
        protected $APIUrl = ALLO_DEFAULT_URL_API;

        /**
         * Contient l'adresse du site où chercher les images.
         * @var string
         */
        protected $imagesUrl = ALLO_DEFAULT_URL_IMAGES;

        /**
         * Provoquer une ErrorException et/ou retourne la dernière provoquée.
         * 
         * @param string $message=null Le message de l'erreur
         * @param int $code=0 Le code de l'erreur
         * @return ErrorException|null
         * @throws ErrorException
         */
        public function error($message = null, $code = 0)
        {
            if ($message !== null)
            {
                $error = new ErrorException($message, $code);
                
                self::$_lastError = $error;
                
                if ($this->throwExceptions)
                    throw $error;
            }
            
            return self::$_lastError;
        }

        /**
         * Modifier le langage.
         * Les initiales du langage sont telles que défini dans la liste des codes ISO 639-1.
         * Le français (fr), l'allemand (de), l'anglais (en), le turque (tr) et l'espagnol (es) sont disponibles.
         * 
         * @see http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         * 
         * @param string $lang=null Les initiales du langage.
         */
        
        public function lang($lang = null)
        {
            switch((string) $lang)
            {
                case 'de': case 'filmstarts.de':
                    $this->APIUrl = "api.filmstarts.de";
                    $this->imagesUrl = "bilder.filmstarts.de";
                break;
                
                case 'es': case 'sensacine.com':
                    $this->APIUrl = "api.sensacine.com";
                    $this->imagesUrl = "imagenes.sensacine.com";
                break;
                
                case 'fr': case 'allocine.fr':
                    $this->APIUrl = "api.allocine.fr";
                    $this->imagesUrl = "images.allocine.fr";
                break;
                
                case 'en': case 'screenrush.co.uk':
                    $this->APIUrl = "api.screenrush.co.uk";
                    $this->imagesUrl = "images.screenrush.co.uk";
                break;
                
                case 'tr': case 'beyazperde.com':
                    $this->APIUrl = "api.beyazperde.com";
                    $this->imagesUrl = "tri.acimg.net";
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
         * @return string
         */
        
        protected function createURL($type)
        {
            $this->set(array(
                'format' => 'json',
                'partner' => $this->partner,
            ));
            $params = $this->getPresets();
            $params['filter'] = isset($params['filter']) ? implode(",", $params['filter']) : null;

            $queryURL = $this->APIUrl . '/' . $type;
                  $searchQuery = str_replace('%2B', '+', http_build_query($params)) . '&sed=' . date('Ymd');
                  $toEncrypt = $this->allocineSecretKey . $searchQuery;
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
            if (!function_exists('json_decode'))
            {
                $this->error("The extension php_json must be installed with PHP and enabled.", 8);
                return false;
            }
            
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
                        $curlError = curl_error($curl);
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
                $this->error("An cURL error occurred while retrieving the data: $curlError." , 2);
                return false;
            }
            
            $data = @json_decode($data, true);
            
            if (empty($data) or !is_array($data) or json_last_error())
            {
                $this->error("An JSON error (" . json_last_error() . ") occurred when converting data: " . json_last_error_msg(), 3);
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
         * @param string $type Voir AlloHelper::createURL()
         * @param string $container L'élément contenant les données dans le tableau retourné par Allociné
         * @return AlloData|array|false
         * @throws ErrorException
         */
        
        protected function getData($type, $container, &$url)
        {
            // Récupération des données
            $data = $this->getDataFromURL($url = $this->createURL($type));
            
            // En cas d'erreur
            if (empty($data))
                return false;
                
            // Succès ($data est encore un array)
            else
            {
                if (empty($data['error']))
                    // On retourne les données
                    if (class_exists('AlloData'))
                        return new AlloData($data[$container], $this->utf8Decode);
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
            $url = $this->createURL('rest/v3/search');
            
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
                            $result['poster'] = new AlloImage($result['poster']['href'], $this->imagesUrl);
                        else
                            $result['poster'] = new AlloImage(null, $this->imagesUrl);
                        
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
                    return new AlloData($data, $this->utf8Decode);
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
         * @param int|string $profile ='medium' La quantité d'informations à renvoyer: 'small', 'medium', 'large', 1 pour 'small', 2 pour 'medium', 3 pour 'large'.
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
            $url = $this->createURL('rest/v3/movie');
            
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
                        $data['poster'] = new AlloImage($data['poster']['href'], $this->imagesUrl);
                    else
                        $data['poster'] = new AlloImage(null, $this->imagesUrl);
                    
                    // Correction des apostrophes dans le synopsis si nécessaire
                    if ($this->autoCorrectApostrophe and !empty($data['synopsis']))
                      $data['synopsis'] = preg_replace("#\p{L}\K[‘’](?=\p{L})#u", "'", $data['synopsis']);
                    
                    if ($this->autoCorrectApostrophe and !empty($data['synopsisShort']))
                      $data['synopsisShort'] = preg_replace("#\p{L}\K[‘’](?=\p{L})#u", "'", $data['synopsisShort']);
                    
                    // On retourne les données
                    if (class_exists(AlloData::class))
                        return new AlloData($data, $this->utf8Decode);
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

        /**
         * @param boolean $utf8Decode
         */
        public function setUtf8Decode($utf8Decode)
        {
            $this->utf8Decode = $utf8Decode;
        }

        /**
         * @return boolean
         */
        public function getUtf8Decode()
        {
            return $this->utf8Decode;
        }

        /**
         * @param array $lastRequest
         */
        public function setLastRequest($lastRequest)
        {
            $this->lastRequest = $lastRequest;
        }

        /**
         * @return array
         */
        public function getLastRequest()
        {
            return $this->lastRequest;
        }

        /**
         * @param string $partner
         */
        public function setPartner($partner)
        {
            $this->partner = $partner;
        }

        /**
         * @return string
         */
        public function getPartner()
        {
            return $this->partner;
        }

        /**
         * @param boolean $throwExceptions
         */
        public function setThrowExceptions($throwExceptions)
        {
            $this->throwExceptions = $throwExceptions;
        }

        /**
         * @return boolean
         */
        public function getThrowExceptions()
        {
            return $this->throwExceptions;
        }

        /**
         * @param boolean $autoCorrectApostrophe
         */
        public function setAutoCorrectApostrophe($autoCorrectApostrophe)
        {
            $this->autoCorrectApostrophe = $autoCorrectApostrophe;
        }

        /**
         * @return boolean
         */
        public function getAutoCorrectApostrophe()
        {
            return $this->autoCorrectApostrophe;
        }

        /**
         * @param string $allocineSecretKey
         */
        public function setAllocineSecretKey($allocineSecretKey)
        {
            $this->allocineSecretKey = $allocineSecretKey;
        }

        /**
         * @return string
         */
        public function getAllocineSecretKey()
        {
            return $this->allocineSecretKey;
        }

        /**
         * @param string $imagesUrl
         */
        public function setImagesUrl($imagesUrl)
        {
            $this->imagesUrl = $imagesUrl;
        }

        /**
         * @return string
         */
        public function getImagesUrl()
        {
            return $this->imagesUrl;
        }

        /**
         * @param string $APIUrl
         */
        public function setAPIUrl($APIUrl)
        {
            $this->APIUrl = $APIUrl;
        }

        /**
         * @return string
         */
        public function getAPIUrl()
        {
            return $this->APIUrl;
        }
    }