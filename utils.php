<?php
/**
 * RemoveDuplicatedLines
 * This function removes all duplicated lines of the given text file.
 *
 * @param     string
 * @param     bool
 * @return    string
 */
function RemoveDuplicatedLines($Filepath, $IgnoreCase=false, $NewLine="\n"){
 
    if (!file_exists($Filepath)){
        $ErrorMsg  = 'RemoveDuplicatedLines error: ';
        $ErrorMsg .= 'The given file ' . $Filepath . ' does not exist!';
        die($ErrorMsg);
    }
 
    $Content = file_get_contents($Filepath);
 
    $Content = RemoveDuplicatedLinesByString($Content, $IgnoreCase, $NewLine);
 
    // Is the file writeable?
    if (!is_writeable($Filepath)){
        $ErrorMsg  = 'RemoveDuplicatedLines error: ';
        $ErrorMsg .= 'The given file ' . $Filepath . ' is not writeable!';    
        die($ErrorMsg);
    }
 
    // Write the new file
    $FileResource = fopen($Filepath, 'w+');      
    fwrite($FileResource, $Content);        
    fclose($FileResource);   
}
 
 
/**
 * RemoveDuplicatedLinesByString
 * This function removes all duplicated lines of the given string.
 *
 * @param     string
 * @param     bool
 * @return    string
 */
function RemoveDuplicatedLinesByString($Lines, $IgnoreCase=false, $NewLine="\n"){
 
    if (is_array($Lines))
        $Lines = implode($NewLine, $Lines);
 
    $Lines = explode($NewLine, $Lines);
 
    $LineArray = array();
 
    $Duplicates = 0;
 
    // Go trough all lines of the given file
    for ($Line=0; $Line < count($Lines); $Line++){
 
        // Trim whitespace for the current line
        $CurrentLine = trim($Lines[$Line]);
 
        // Skip empty lines
        if ($CurrentLine == '')
            continue;
 
        // Use the line contents as array key
        $LineKey = $CurrentLine;
 
        if ($IgnoreCase)
            $LineKey = strtolower($LineKey);
 
        // Check if the array key already exists,
        // if not add it otherwise increase the counter
        if (!isset($LineArray[$LineKey]))
            $LineArray[$LineKey] = $CurrentLine;        
        else                
            $Duplicates++;
    }
 
    // Sort the array
    asort($LineArray);
 
    // Return how many lines got removed
    return implode($NewLine, array_values($LineArray));    
}
?>