/**
* Allociné Image Helper
* @author Etienne Gauvin
* @date 26/01/2012
* @licence Creative Commons BY-NC
* @version 1.00
*/

/**
* Host de l'image par défaut
* @const string
*/

const AIH_DEFAULT_IMAGE_HOST = "images.allocine.fr";

/**
* Répertoire de l'image par défaut
* @const string
*/

const AIH_DEFAULT_IMAGE_PATH = "commons/emptymedia/AffichetteAllocine.gif";


var AlloImage = function( url )
{
	/**
	* Liste des icônes diponibles
	* @var array
	*/

	this.icons = {
		'play.png': null,
		'overplay.png': null,
		'overlayVod120.png': {1: 'r', 2: 120, 3: 160}
	};


	/**
	* L'image actuelle est-elle l'image par défaut ?
	* @var bool
	*/

	this.isDefaultImage = true;


	/**
	* Contient les paramètres de l'icône.
	* @var array|false
	*/

	this.imageIcon = false;


	/**
	* Contient les paramètres de la bordure
	* @var array|false
	*/

	this.imageBorder = false;


	/**
	* Contient les paramètres de la taille de l'image.
	* @var array|false
	*/

	this.imageSize = false;


	/**
	* Contient l'adresse du serveur de l'image.
	* @var string
	*/

	this.imageHost = false;


	/**
	* Contient le répertoire de l'image sur Allociné.
	* @var string
	*/

	this.imagePath = false;

	
	/**
	* *** Constructeur ***
	* Créer une nouvelle image grâce à son URL.
	* Si l'url est invalide, l'image utilisée sera celle par défaut.
	* 
	* @param string url=null L'URL de l'image.
	*/
	
	if ( url === undefined )
	{
		this.imageHost = AIH_DEFAULT_IMAGE_HOST;
		this.imagePath = AIH_DEFAULT_IMAGE_PATH;
	}
	else
	{
		this.isDefaultImage = false;
		var key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port', 'relative', 'path', 'directory', 'file', 'query', 'fragment'];
		var parser = /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/;

		var m = parser.exec(url),
			urlParse = {},
			i = 14;
		
		while (i--)
		{
			if (m[i]) {
			  urlParse[key[i]] = m[i];  
			}
		}
		
		this.imageHost = (urlParse['host'] !== undefined) ? urlParse['host'] : AIH_DEFAULT_IMAGE_HOST;
		this.imagePath = (urlParse['path'] !== undefined) ? urlParse['path'] : AIH_DEFAULT_IMAGE_PATH;
	}

	// Parsage de l'URL
	var explodePath = this.imagePath.split('/');
	
	// Première partie vide ?
	if (explodePath[0] === '')
		explodePath[0] = undefined;
	
	// Résultat textuel de tout ce qui n'est pas un paramètre
	this.imagePath = '';
	
	// Détecte les paramètres jusqu'au début du path réel.
	for (iPathPart in explodePath)
	{
		if (explodePath[iPathPart] !== undefined)
		{
			var pathPart = explodePath[iPathPart];
			var matches = false;
			
			// Icône
			if (pathPart.indexOf('o', 0) === 0 && (matches = pathPart.match(/^o_(.+)_(.+)_(.+)/i)) != undefined)
			{
				this.icon(matches[3], matches[2], matches[1]);
			}
			
			// Bordure
			else if (pathPart.indexOf('b', 0) === 0 && (matches = pathPart.match(/^b[xy]?_([0-9]+)_([0-9a-f]{6}|.*)/i)) != undefined)
			{
				if (matches[2].search(/^[0-9a-f]{6}$/i) === -1)
					matches[2] = "000000";
				
				this.border(matches[1], matches[2]);
			}
			
			// Redimensionnement
			else if (pathPart.indexOf('r', 0) === 0 && (matches = pathPart.match(/^r[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)/i)) != undefined)
			{
				this.resize(matches[1], matches[2]);
			}
			
			// Recoupe
			else if (pathPart.indexOf('c', 0) === 0 && (matches = pathPart.match(/^c[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)/i)) != undefined)
			{
				this.cut(matches[1], matches[2]);
			}
			
			else
			{
				this.imagePath += ((this.imagePath==='')?'':'/') + explodePath[iPathPart];
			}
		}
	}
	
	
	/**
	* Modifier l'icône sur l'image.
	* 
	* @param string position='c' La position de l'icône par rapport au centre de l'image (en une ou deux lettres), d'après la rose des sable. Renseigner une position invalide (telle que 'c') pour centrer l'icône.
	* @param int margin=4 Le nombre de pixel entre l'icône et le(s) bord(s) le(s) plus proche(s).
	* @param string icon='play.png' Le nom de l'icône à ajouter. La liste des icônes se trouve dans AlloImage::icons.
	* @return this
	*/

	this.icon = function ( position, margin, icon )
	{
		position = (position === undefined) ? 'c' : position;
		margin = (margin === undefined) ? 4 : margin;
		icon = (icon === undefined) ? 'play.png' : icon;
		
		
		if (this.icons[icon] != undefined)
		{
			var p = this.icons[icon];
			
			switch (p[0])
			{
				case 'r': this.resize(p[1], p[2]); break;
				case 'c': this.cut(p[1], p[2]); break;
			}
		}
		
		this.imageIcon = {
			'position': position.substring(0, 2),
			'margin': margin,
			'icon': icon
		};
		
		return this;
	};;


	/**
	* Renvoie les paramètres enregistrés pour l'icône.
	* 
	* @return array|false
	*/

	this.getIcon = function ()
	{
		return this.imageIcon;
	};


	/**
	* Efface les paramètres enregistrés pour l'icône.
	* 
	* @return this
	*/

	this.destroyIcon = function ()
	{
		this.imageIcon = false;
		return this;
	};


	/**
	* Modifier la bordure de l'image.
	* 
	* @param int size=1 L'épaisseur de la bordure en pixels.
	* @param string color='000000' La couleur de la bordure en hexadécimal (sans # initial).
	* @return this
	*/

	this.border = function( size, color )
	{
		size = (size === undefined) ? 1 : size;
		color = (color === undefined) ? "000000" : color;
		
		this.imageBorder = {
			'size': size,
			'color': color
		};
		
		return this;
	};


	/**
	* Renvoie les paramètres enregistrés de la bordure.
	* 
	* @return array|false
	*/

	this.getBorder = function ()
	{
		return this.imageBorder;
	};


	/**
	* Efface la bordure.
	* 
	* @return this
	*/

	this.destroyBorder = function ()
	{
		this.imageBorder = false;
		return this;
	};

	
	/**
	* Efface tous les paramètres de l'image
	* 
	* @return this
	*/

	this.reset = function ()
	{
		this.destroyBorder().destroyIcon().maxSize();
		return this;
	};


	/**
	* Modifier proportionnellement la taille de l'image au plus petit.
	* Si les deux paramètres sont laissés tels quels (xmax='x' et ymax='y'), l'image sera de taille normale.
	* Appeler cette fonction efface les paramètres enregistrés pour AlloImage::cut() (Les deux méthodes ne peuvent être utilisées en même temps).
	* 
	* @param int xmax='x' La largeur maximale de l'image, en pixels. Laisser 'x' pour une largeur automatique en fonction de ymax.
	* @param int ymax='y' La hauteur maximale de l'image, en pixels. Laisser 'y' pour une hauteur automatique en fonction de xmax.
	* @return this
	*/

	this.resize = function ( xmax, ymax )
	{
		xmax = (xmax === undefined) ? 'x' : xmax;
		ymax = (ymax === undefined) ? 'y' : ymax;
		
		this.imageSize = {
			'method': 'r',
			'xmax': xmax,
			'ymax': ymax
		};
		
		return this;
	};


	/**
	* Redimensionner l'image au plus petit, puis couper les bords trop grands.
	* Appeler cette fonction efface les paramètres enregistrés pour AlloImage::resize() (Les deux méthodes ne peuvent être utilisées en même temps).
	* 
	* @param int xmax La largeur maximale de l'image, en pixels.
	* @param int ymax La hauteur maximale de l'image, en pixels.
	* @return this
	*/

	this.cut = function ( xmax, ymax )
	{
		this.imageSize = {
			'method': 'c',
			'xmax': xmax,
			'ymax': ymax
		};
		
		return this;
	};


	/**
	* Retourne les paramètres enregistrés du redimensionnement/recoupe de l'image.
	* 
	* @return array|false
	*/

	this.getSize = function ()
	{
		return this.imageSize;
	};


	/**
	* Règle l'image à sa taille maximale (Effacer redimensionnement/recoupe)
	* 
	* @return array|false
	*/

	this.maxSize = function ()
	{
		this.imageSize = false;
		return this;
	};


	/**
	* Retourne le host de l'image.
	* 
	* @return string
	*/

	this.getImageHost = function ()
	{
		return this.imageHost;
	};


	/**
	* Modifier le serveur (host) de l'image.
	* 
	* @param string server L'adresse sans slash du serveur (ex: 'images.allocine.fr'), le même paramètre que pour AlloHelper::lang(), ou 'default' pour régler selon le langage enregistré.
	* @return this
	*/

	this.setImageHost = function ( server )
	{
		switch (server)
		{
			case 'default':
			case 'de': case 'filmstarts.de':
			case 'es': case 'sensacine.com':
			case 'fr': case 'allocine.fr':
			case 'en': case 'screenrush.co.uk':
				this.imageHost = this.imagesUrl;
			break;
			
			default:
				this.imageHost = server;
		}
		
		return this;
	};
	
	/**
	* Construit l'URL à partir des paramètres enregistrés.
	* 
	* @return string
	*/

	this.url = function ()
	{
		var params = new Array();
		
		// Taille
		if ( this.imageSize !== false )
			params[0] = this.imageSize.method + '_' + this.imageSize.xmax + '_' + this.imageSize.ymax;
		
		// Bordure
		if ( this.imageBorder !== false )
			params[1] = 'b_' + this.imageBorder.size + '_' + this.imageBorder.color;
		
		// Icône
		if ( this.imageIcon !== false )
			params[2] = 'o_' + this.imageIcon.icon + '_' + this.imageIcon.margin + '_' + this.imageIcon.position;
		
		return 'http://' + this.imageHost + (params.length ? '/' + params.join('/') : '') + '/' + this.imagePath;
	};
	
};
