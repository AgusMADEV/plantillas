<?php
/**
 * Renders a template by replacing placeholders with data.
 *
 * Supports simple placeholders (e.g. {titulo}) and loop blocks defined by
 * <template id="key"> ... </template>, where "key" corresponds to an array in $data.
 *
 * @param string $templateFile Path to the HTML template file.
 * @param array  $data         Associative array containing the replacement values.
 *
 * @return string The rendered template.
 */
function renderTemplate($templateFile, $data) {
    // Check if the template file exists
    if (!file_exists($templateFile)) {
        return "Error: Template file not found.";
    }
    
    // Read the contents of the template file
    $template = file_get_contents($templateFile);
    
    // Process <template id="..."> blocks for loops
    $template = preg_replace_callback(
        '/<template\s+id="(\w+)"\s*>(.*?)<\/template>/s',
        function($matches) use ($data) {
            $arrayKey  = $matches[1];
            $loopBlock = $matches[2];
            $result    = '';
            
            // Check if the corresponding data exists and is an array
            if (isset($data[$arrayKey]) && is_array($data[$arrayKey])) {
                foreach ($data[$arrayKey] as $item) {
                    // Process placeholders within the loop block using the item array
                    $result .= preg_replace_callback('/\{(\w+)\}/', function($m) use ($item) {
                        $key = $m[1];
                        return isset($item[$key]) ? $item[$key] : $m[0];
                    }, $loopBlock);
                }
            } else {
                // If data for this key is not an array, leave the block untouched or replace with empty string
                $result = '';
            }
            
            return $result;
        },
        $template
    );
    
    // Process the remaining placeholders using the main data array
    $rendered = preg_replace_callback('/\{(\w+)\}/', function($matches) use ($data) {
        $key = $matches[1];
        return isset($data[$key]) ? $data[$key] : $matches[0];
    }, $template);
    
    return $rendered;
}
?>

