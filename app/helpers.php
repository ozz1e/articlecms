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

/**
 * 判断当前用户是否有权限操作文章
 */
if( !function_exists('checkPostOwner') ){
    function checkPostOwner( $postId = 0 ){
        if( !Admin::user()->inRoles(['administrator', 'manager']) ){
            //文章涉及到彻底删除和恢复 所以需要withTrashed
            $postEditor = Post::withTrashed()->find($postId,'editor_id');
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

/**
 * 过滤文章html文件名的特殊字符
 */
if( !function_exists('filterPostTitle') ){
    function filterPostFileName( $title ='' ){
        $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\=|\\\|\|/";
        return preg_replace($regex,"",$title);
    }
}
