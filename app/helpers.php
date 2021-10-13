<?php

use App\Models\Post;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\DB;

if (! function_exists('getDir')) {
    function getDir($path){
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
    function filterHtml( $content = '' ){
        return htmlspecialchars(htmlentities($content));
    }
}

/**
 * 还原html内容
 */
if( !function_exists('deCodeHtml')){
    function deCodeHtml( $content = '' ){
        return htmlspecialchars_decode(html_entity_decode($content));
    }
}

if( !function_exists('checkPostOwner') ){
    function checkPostOwner( $postId = 0 ){
        if( !Admin::user()->inRoles(['editor', 'developer']) ){
            $postEditor = Post::query()->find($postId,'editor_id');
            $postUser = DB::table('user_editor')->where('editor_id',$postEditor['editor_id'])->pluck('user_id')->toArray();
            if( !in_array(Admin::user()->id,$postUser) ){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
}
