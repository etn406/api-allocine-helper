<?php
    require_once "./api-allocine-helper-2.2.php";
    
    // Get keywords
    do
    {
        echo "Search: ";
        
        $search = trim( substr( fgets( STDIN ), 0, -1 ) );
    }
    while ( empty( $search ) );
    
    // Construct the object
    $allohelper = new AlloHelper;
    
    try
    {
        // Parameters
        $page = 1;
        $count = 16;
        
        do
        {
            // Request
            $data = $allohelper->search( $search, $page, $count );
            
            // No result ?
            if ( count( $data->movie ) < 1 )
                throw new ErrorException( 'No result for "' . $search . '"' );
            
            // Just the title
            if ( $page === 1 )
                echo PHP_EOL . $data->results->movie .' results for "' . $search . '":' . PHP_EOL . '-----------------' . str_repeat( '-', strlen( $search ) ) . PHP_EOL;
            
            // Each movie result
            foreach ( $data->movie as $i => $movie )
            {
                echo ( ( $i > 1 ) ? PHP_EOL : '' ) . ( $i + $count * ($page - 1) + 1 ) . '- ' . $movie->title;
            }
            
            // Next page
            $page++;
        }
        
        // Wait for quit or continue.
        while ( ! trim( substr( fgets( STDIN ), 0, -1 ) ) );
        
    }
    
    // Error
    catch ( ErrorException $e )
    {
        echo "Error " . $e->getCode() . ": " . $e->getMessage();
    }
?>