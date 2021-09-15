<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    //语言管理
    $router->resource('lang', 'LangController');
    //文件管理
    $router->get('media', 'MediaController@index')->name('media-index');
    $router->get('media/download', 'MediaController@download')->name('media-download');
    $router->delete('media/delete', 'MediaController@delete')->name('media-delete');
    $router->put('media/move', 'MediaController@move')->name('media-move');
    $router->post('media/upload', 'MediaController@upload')->name('media-upload');
    $router->post('media/folder', 'MediaController@newFolder')->name('media-new-folder');
    //作者管理
    $router->resource('editor', 'EditorController');
    $router->post('editor/createEditor', 'EditorController@createEditor');
    $router->put('editor/{id}/updateEditor', 'EditorController@updateEditor');
    $router->post('editor/uploadAvatar', 'EditorController@uploadAvatar');
    $router->delete('editor/{id}/deleteEditor','EditorController@deleteEditor');
    //图片管理
    $router->get('image','ImageController@index');
    //模板管理
    $router->resource('template','TemplateController');
    $router->get('template/{id}/editOnLine','TemplateController@editTemplate');
    $router->post('template/{id}/saveTemplate','TemplateController@saveTemplate');
    $router->delete('template/{id}/delete','TemplateController@deleteTemplate');
    //文章目录
    $router->get('directory/{id}/tempList','DirectoryController@tempList');
    $router->get('directory/tempList','DirectoryController@tempList');
    $router->get('directory/dialogCreateEditor','DirectoryController@dialogCreateEditor');
    $router->resource('directory','DirectoryController');
    $router->post('directory/createDirectory','DirectoryController@createDirectory');
    $router->put('directory/{id}/updateDirectory','DirectoryController@updateDirectory');
    $router->post('directory/includeDirectory','DirectoryController@includeDirectory');
    //文章管理
    $router->any('post/modifyHtmlFile','PostController@modifyHtmlFile');
    $router->get('post/loadPostList','PostController@loadPostList');
    $router->get('post/loadAmpList','PostController@loadAmpList');
    $router->get('post/postBlockList','PostController@postBlockList');
    $router->post('post/createArticle','PostController@createArticle');
    $router->resource('post','PostController');
});
