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

/**
 * 过滤html内容
 */
if( !function_exists('filterHtml')){
    function filterHtml( $content = '' )
    {
        return htmlspecialchars(htmlentities($content));
    }
}

/**
 * 还原html内容
 */
if( !function_exists('deCodeHtml')){
    function deCodeHtml( $content = '' )
    {
        return htmlspecialchars_decode(html_entity_decode($content));
    }
}
