<?php
    // Load the file.
    require_once "../api-allocine-helper.php";
    
    // Construct the object.
    $allohelper = new AlloHelper;

    // Define parameters.
    $keywords = "The Dark Knight";
    $page = 1;
    
    // It's important to catch Exceptions.
    try
    {
        // Request data with parameters, and save the response in $data.
        $data = $allohelper->search( $keywords, $page );
            
        // No result ?
        if ( count( $data->movie ) < 1 )
        {
            // Print a error message.
            echo '<p>No result for "' . $keywords . '"</p>';
        }
        
        else
        {
            // For each movie result.
            foreach ( $data['movie'] as $movie )
            {
                // Print the title.
                echo "<h2>" . $movie['title'] . "</h2>";
            }
        }
    }
    
    // Error
    catch ( ErrorException $e )
    {
        // Print a error message.
        echo "Error " . $e->getCode() . ": " . $e->getMessage();
    }
?>
