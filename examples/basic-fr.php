<?php
    // Charger le fichier.
    require_once "./api-allocine-helper-2.2.php";
    
    // Créer un objet AlloHelper.
    $allohelper = new AlloHelper;

    // Définir les paramètres
    $motsCles = "The Dark Knight";
    $page = 1;
    
    
    // Il est important d'utiliser le bloc try-catch pour gérer les erreurs.
    try
    {
        // Envoi de la requête avec les paramètres, et enregistrement des résultats dans $donnees.
        $donnees = $allohelper->search( $motsCles, $page );
            
        // Pas de résultat ?
        if ( count( $donnees['movie'] ) < 1 )
        {
            // Afficher un message d'erreur.
            echo '<p>Pas de résultat pour "' . $motsCles . '"</p>';
        }
        
        else
        {
            // Pour chaque résultat de film.
            foreach ( $donnees['movie'] as $film )
            {
                // Afficher le titre.
                echo "<h2>" . $film['title'] . "</h2>";
            }
        }
    }
    
    // En cas d'erreur.
    catch ( ErrorException $e )
    {
        // Afficher un message d'erreur.
        echo "Erreur " . $e->getCode() . ": " . $e->getMessage();
    }
?>