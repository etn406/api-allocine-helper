<?php

    /**
    * Manipuler facilement les URLs des images.
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
         * @var Exception
         */
        private $throwExceptions;


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
                    $this->imageHost = ALLO_DEFAULT_URL_IMAGES;
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
         * @param string $imageHost
         * @throws ErrorException
         */
        
        public function __construct($url = null, $imageHost = ALLO_DEFAULT_URL_IMAGES)
        {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
            {
                $this->imageHost = $imageHost;
                $this->imagePath = self::DEFAULT_IMAGE_PATH;
                
            }
            else
            {
                $urlParse = parse_url($url);
                
                $this->imageHost = !empty($urlParse['host']) ? $urlParse['host'] : $imageHost;
                
                if (!empty($urlParse['path']))
                    $this->imagePath = $urlParse['path'];
                else
                    $this->error("This isn't a URL to an image.", 7);
                
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

                AlloHelper::$_lastError = $error;

                if ($this->throwExceptions)
                    throw $error;
            }

            return AlloHelper::$_lastError;
        }
    }

