<?php

    /**
    * Manipuler facilement les données reçues.
    * Il est possible de supprimer complètement cette classe sans autre modification du code.
    * 
    * @implements ArrayAccess, SeekableIterator, Countable
    */

    class AlloData implements ArrayAccess, SeekableIterator, Countable
    {
        /**
         * @var Exception
         */
        public $throwExceptions;
        
        /**
         * Contiendra les données
         * @var array
         */
        private $_data = array();

        /**
         * @var bool Flag pour activer le decodage UTF8
         */
        protected $utf8Decode = ALLO_UTF8_DECODE;

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
        
        public function utf8_decode($var, $tab = false)
        {
            if ($this->utf8Decode)
            {
                if (is_string($var)) return utf8_decode(str_replace('â€™', "'", $var));
                elseif (!is_array($var) || !$tab) return $var;
                else
                {
                    $return = array();
                    foreach ($var as $i => $cell)
                        $return[utf8_decode($i)] = $this->utf8_decode($cell, true);
                    return $return;
                }
            }
            else
                return $var;
        }


        /**
         * Constructeur
         * @param $data
         * @param bool $utf8Decode
         */
        
        public function __construct($data, $utf8Decode = ALLO_UTF8_DECODE)
        {
            $this->_data = (array) $data;
            $this->utf8Decode = $utf8Decode;
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
                        $this->error("This offset ($offset) does not exist.", 6);
                    
                    // Eviter une erreur en retournant une référence, si les exceptions sont désactivées.
                    $b = null;
                    $a =&$b;
                    
                    return $a;
                }
            }
        }
        
        /**
         * @param boolean $utf8_decode
         * @return array
         */
        public function getArray($utf8_decode = false)
        {
            return (array) $this->utf8_decode($this->_getProperty(), $utf8_decode);
        }

        /**
         * Retourne les données sous forme d'un object
         * @param boolean $utf8_decode
         * @return object
         */
        public function getObject($utf8_decode = false)
        {
            return (object) $this->utf8_decode($this->_getProperty(), $utf8_decode);
        }


        /**
         * Si l'on essaie d'accéder à une propriété inexistante (donc un élément de $this->_data)
         * @param $offset
         * @return AlloData|array|string
         */
        
        public function __get($offset)
        {
            $data = $this->_getProperty($offset);
            if (is_array($data))
                return new AlloData($data, $this->utf8Decode);
            else return $this->utf8_decode($data);
        }
        
        
        /**
         * Impossible de créer/modifier une propriété
         * @param $offset
         * @param $value
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
                return new AlloData($data, $this->utf8Decode);
            else return $this->utf8_decode($data);
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
                $this->error("This offset ($newPosition) does not exist.", 6);
                $this->position = $lastPosition;
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
                return new AlloData($data, $this->utf8Decode);
            else return $this->utf8_decode($data);
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
                $data = new AlloData($tab[0], $this->utf8Decode);
                if (isset($data[$offset]) && is_string($data[$offset]))
                    return $data[$offset];
            }
            
            elseif (count($tab) < 1)   return '';
            
            $values = array();
            
            foreach ($tab as $i => $stab)
            {
                $data = new AlloData($stab, $this->utf8Decode);
                if (isset($data[$offset]) && is_string($data[$offset]))
                    $values[] = $data[$offset];
            }
            
            $last = array_slice($values, -1, 1);
            
            if ($values)
                return implode((string) $separator, array_slice($values, 0, -1)) . ((count($values) > 1) ? (string) $lastSeparator . $last[0] : '');
            else
                return '';
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
    }

