<?php
    require_once "../api-allocine-helper.php";
    
    // Construct the object
    $allohelper = new AlloHelper;
    
    
    // Get keywords
    echo "Keywords: ";
    
    // Trim the input text.
    $search = trim(substr(fgets(STDIN), 0, -1));
    
    // Parameters
    $page = 1;
    $count = 16;
    
    try
    {
        // Request
        $data = $allohelper->search($search, $page, $count);
        
        // No result ?
        if (!$data or count($data->movie) < 1)
            throw new ErrorException('No result for "' . $search . '"');
        
        // View number of results.
        echo "// " . $data->results->movie .' results for "' . $search . '":' . PHP_EOL;
        
        // For each movie result.
        foreach ($data->movie as $i => $movie)
        {
            // i | code | title
            echo $i . "\t" . $movie->code . "\t" . $movie->title . PHP_EOL;
        }
    }
    
    // Error
    catch (ErrorException $e)
    {
        echo "Error " . $e->getCode() . ": " . $e->getMessage() . PHP_EOL;
    }
?>
