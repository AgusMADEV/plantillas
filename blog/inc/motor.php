<?php
/**
 * Template Engine
 *
 * Provides a function to render HTML templates with support for:
 * - Simple placeholders (e.g., {title})
 * - Loop blocks defined with <template id="key"> ... </template> (where "key" corresponds to an array in the data)
 * - Conditional blocks using <if>, <elseif>, and <else> directives.
 * - Recursive includes using <iframe> tags where the id attribute specifies the filename of the template to be included.
 *
 * Usage:
 *   $rendered = renderTemplate('path/to/template.html', $data);
 *
 * @param string $templateFile Path to the HTML template file.
 * @param array  $data         Associative array containing the replacement values.
 *
 * @return string The rendered template.
 */
function renderTemplate($templateFile, $data) {
    if (!file_exists($templateFile)) {
        return "Error: Template file '$templateFile' not found.";
    }
    
    // Read the contents of the template file.
    $template = file_get_contents($templateFile);
    
    // Process loop blocks (<template id="key"> ... </template>)
    $template = preg_replace_callback(
        '/<template\s+id="(\w+)"\s*>(.*?)<\/template>/s',
        function($matches) use ($data) {
            $arrayKey  = $matches[1];
            $loopBlock = $matches[2];
            $result    = '';
            
            if (isset($data[$arrayKey]) && is_array($data[$arrayKey])) {
                foreach ($data[$arrayKey] as $item) {
                    $result .= preg_replace_callback('/\{(\w+)\}/', function($m) use ($item) {
                        $key = $m[1];
                        return isset($item[$key]) ? $item[$key] : $m[0];
                    }, $loopBlock);
                }
            }
            return $result;
        },
        $template
    );
    
    // Process conditional blocks (<if>, <elseif>, <else>)
    $template = preg_replace_callback(
        '/<if\s+condition="(\w+)"\s*>(.*?)<\/if>/s',
        function($matches) use ($data) {
            $primaryCondition = $matches[1];
            $innerContent     = $matches[2];
            $segments         = [];
            
            // Pattern to match <elseif> and <else> tags within the if block.
            $pattern = '/<(elseif|else)(?:\s+condition="(\w+)")?\s*>/s';
            preg_match_all($pattern, $innerContent, $m, PREG_OFFSET_CAPTURE);
            
            if (count($m[0]) > 0) {
                // Capture content before the first <elseif>/<else> tag.
                $firstPos = $m[0][0][1];
                $segments[] = [
                    'type'      => 'if',
                    'condition' => $primaryCondition,
                    'content'   => substr($innerContent, 0, $firstPos)
                ];
                
                // Process each <elseif> or <else> segment.
                for ($i = 0; $i < count($m[0]); $i++) {
                    $tag   = $m[1][$i][0]; // 'elseif' or 'else'
                    $cond  = isset($m[2][$i][0]) ? $m[2][$i][0] : null;
                    $start = $m[0][$i][1];
                    $end   = $start + strlen($m[0][$i][0]);
                    $nextPos = (isset($m[0][$i+1])) ? $m[0][$i+1][1] : strlen($innerContent);
                    $segContent = substr($innerContent, $end, $nextPos - $end);
                    $segments[] = [
                        'type'      => $tag,
                        'condition' => $cond,
                        'content'   => $segContent
                    ];
                }
            } else {
                // No elseif/else tags found; treat the entire content as the if block.
                $segments[] = [
                    'type'      => 'if',
                    'condition' => $primaryCondition,
                    'content'   => $innerContent
                ];
            }
            
            // Evaluate segments in order.
            foreach ($segments as $segment) {
                if ($segment['type'] === 'if' || $segment['type'] === 'elseif') {
                    if (!empty($data[$segment['condition']])) {
                        return $segment['content'];
                    }
                } elseif ($segment['type'] === 'else') {
                    return $segment['content'];
                }
            }
            return '';
        },
        $template
    );
    
    // Process include directives using <iframe> tags.
    // The id attribute specifies the filename of the template to be included.
    $template = preg_replace_callback(
        '/<iframe\s+id="([^"]+)"\s*>(.*?)<\/iframe>/s',
        function($matches) use ($data) {
            $includeFile = $matches[1];
            // Recursively render the included template.
            return renderTemplate($includeFile, $data);
        },
        $template
    );
    
    // Process simple placeholders {key} using the main data array.
    $rendered = preg_replace_callback('/\{(\w+)\}/', function($matches) use ($data) {
        $key = $matches[1];
        return isset($data[$key]) ? $data[$key] : $matches[0];
    }, $template);
    
    return $rendered;
}
?>

