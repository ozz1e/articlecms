<?php
if (! function_exists('getDir')) {
    function getDir($path)
    {
        if( !is_dir($path) ){
            return [];
        }
        $files = scandir($path);
        $fileItem = [];
        foreach ($files as $v) {
            $newPath = $path.DIRECTORY_SEPARATOR.$v;
            if( is_dir($newPath) && $v != '.' && $v != '..' ){
                $fileItem = array_merge($fileItem,getDir($newPath));
            }else if( is_file($newPath)){
                $fileItem[] = $v;
            }
        }
        return $fileItem;
    }
}

if( !function_exists('filterHtml')){
    function filterHtml( $content = '' )
    {
        return htmlentities(trim($content));
    }
}
