<?php

/*
 * CKFinder Configuration File
 *
 * For the official documentation visit https://docs.ckeditor.com/ckfinder/ckfinder3-php/
 */

/*============================ PHP Error Reporting ====================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/debugging.html

// Production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

// Development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
session_start();

/**
 * 判断是否登录了文章系统
 * @return bool
 */
function isLogin() {
//    $is_login =  isset($_SESSION['your_cache_user']) && $_SESSION['your_cache_user']!='';
//
//    if($is_login) {
//        return true;
//    }
//
//    return false;
    return true;
}

/*============================ General Settings =======================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html

$config = array();

/*============================ Enable PHP Connector HERE ==============================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_authentication

$config['authentication'] = function () {
    $is_login = isLogin();
    if( $is_login ) {
        return true;
    } else {
        exit('请先<a href="/aomeicms/fe785ff7723e5667fe6d2326ae94be9b?from='.urlencode('https://www.multcloud.com/0d958d0af15d73beeec6852c13911a700/ckfinder.html').'">登录</a>');
    }
};

/*============================ License Key ============================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_licenseKey

$config['licenseName'] = 'itlya.com';
$config['licenseKey']  = '*Z?2-*1**-9**Q-*9**-*E**-D*N*-3**C';

/*============================ CKFinder Internal Directory ============================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_privateDir

$config['privateDir'] = array(
    'backend' => 'default',
    'tags'   => '.ckfinder/tags',
    'logs'   => '.ckfinder/logs',
    'cache'  => '.ckfinder/cache',
    'thumbs' => '.ckfinder/cache/thumbs',
);

/*============================ Images and Thumbnails ==================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_images

$config['images'] = array(
    'maxWidth'  => 1920,
    'maxHeight' => 1200,
    'quality'   => 80,
    'sizes' => array(
        'small'  => array('width' => 480, 'height' => 320, 'quality' => 80),
        'medium' => array('width' => 600, 'height' => 480, 'quality' => 80),
        'large'  => array('width' => 800, 'height' => 600, 'quality' => 80)
    )
);

/*=================================== Backends ========================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_backends

// $config['backends'][] = array(
//     'name'         => 'default',
//     'adapter'      => 'local',
//     'baseUrl'      => '/',
// //  'root'         => '', // Can be used to explicitly set the CKFinder user files directory.
//     'chmodFiles'   => 0777,
//     'chmodFolders' => 0755,
//     'filesystemEncoding' => 'UTF-8',
// );
$config['backends'][] = array(
    'name'         => 'default',
    'adapter'      => 'local',
    'baseUrl'      => '/',
    'host'         => 'localhost',
    'username'     => 'my_ftp_ub',
    'port'         => 21,
    'password'     => '888888'
);

/*================================ Resource Types =====================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_resourceTypes

$config['defaultResourceTypes'] = 'en_articles,en_tutorials,en_tutorials_image,en_help,';
$config['defaultResourceTypes'] .= 'de_articles,de_tutorials,de_tutorials_image,de_help,';
$config['defaultResourceTypes'] .= 'fr_articles,fr_tutorials,fr_tutorials_image,fr_help,';
$config['defaultResourceTypes'] .= 'jp_articles,jp_tutorials,jp_tutorials_image,jp_help,';
$config['defaultResourceTypes'] .= 'it_articles,it_tutorials,it_tutorials_image,it_help,';
$config['defaultResourceTypes'] .= 'es_articles,es_tutorials,es_tutorials_image,es_help,';
$config['defaultResourceTypes'] .= 'tw_articles,tw_tutorials,tw_tutorials_image,tw_help,';
$config['defaultResourceTypes'] .= 'screenshot';

$config['resourceTypes'][] = array(
    'name'              => 'Files', // Single quotes not allowed.
    'directory'         => 'files',
    'maxSize'           => 0,
    'allowedExtensions' => '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// gallery
$config['resourceTypes'][] = array(
    'name'              => 'screenshot',
    'directory'         => 'screenshot',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// en
$config['resourceTypes'][] = array(
    'name'              => 'en_articles',
    'directory'         => 'articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'en_tutorials',
    'directory'         => 'tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'en_tutorials_image',
    'directory'         => 'tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'en_help',
    'directory'         => 'help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// de
$config['resourceTypes'][] = array(
    'name'              => 'de_articles',
    'directory'         => 'de/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'de_tutorials',
    'directory'         => 'de/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'de_tutorials_image',
    'directory'         => 'de/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'de_help',
    'directory'         => 'de/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// fr
$config['resourceTypes'][] = array(
    'name'              => 'fr_articles',
    'directory'         => 'fr/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'fr_tutorials',
    'directory'         => 'fr/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'fr_tutorials_image',
    'directory'         => 'fr/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'fr_help',
    'directory'         => 'fr/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// jp
$config['resourceTypes'][] = array(
    'name'              => 'jp_articles',
    'directory'         => 'jp/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'jp_tutorials',
    'directory'         => 'jp/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'jp_tutorials_image',
    'directory'         => 'jp/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'jp_help',
    'directory'         => 'jp/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// it
$config['resourceTypes'][] = array(
    'name'              => 'it_articles',
    'directory'         => 'it/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'it_tutorials',
    'directory'         => 'it/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'it_tutorials_image',
    'directory'         => 'it/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'it_help',
    'directory'         => 'it/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// es
$config['resourceTypes'][] = array(
    'name'              => 'es_articles',
    'directory'         => 'es/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'es_tutorials',
    'directory'         => 'es/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'es_tutorials_image',
    'directory'         => 'es/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'es_help',
    'directory'         => 'es/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

// tw
$config['resourceTypes'][] = array(
    'name'              => 'tw_articles',
    'directory'         => 'tw/articles/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'tw_tutorials',
    'directory'         => 'tw/tutorials/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'tw_tutorials_image',
    'directory'         => 'tw/tutorials/image',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);
$config['resourceTypes'][] = array(
    'name'              => 'tw_help',
    'directory'         => 'tw/help/images',
    'maxSize'           => '300K',
    'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);


/*================================ Access Control =====================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_roleSessionVar

$config['roleSessionVar'] = 'amFinderRole';

// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_accessControl
$config['accessControl'][] = array(
    'role'                => '*',
    'resourceType'        => '*',
    'folder'              => '/',

    'FOLDER_VIEW'         => true,
    'FOLDER_CREATE'       => true,
    'FOLDER_RENAME'       => true,
    'FOLDER_DELETE'       => true,

    'FILE_VIEW'           => true,
    'FILE_CREATE'         => true,
    'FILE_RENAME'         => true,
    'FILE_DELETE'         => true,

    'IMAGE_RESIZE'        => true,
    'IMAGE_RESIZE_CUSTOM' => true
);


/*================================ Other Settings =====================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html

$config['overwriteOnUpload'] = false;
$config['checkDoubleExtension'] = true;
$config['disallowUnsafeCharacters'] = false;
$config['secureImageUploads'] = true;
$config['checkSizeAfterScaling'] = true;
$config['htmlExtensions'] = array('html', 'htm', 'xml', 'js');
$config['hideFolders'] = array('.*', 'CVS', '__thumbs');
$config['hideFiles'] = array('.*');
$config['forceAscii'] = false;
$config['xSendfile'] = false;

// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_debug
$config['debug'] = true;

/*==================================== Plugins ========================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_plugins

$config['pluginsDirectory'] = __DIR__ . '/plugins';
$config['plugins'] = array();

/*================================ Cache settings =====================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_cache

$config['cache'] = array(
    'imagePreview' => 24 * 3600,
    'thumbnails'   => 24 * 3600 * 365,
    'proxyCommand' => 0
);

/*============================ Temp Directory settings ================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_tempDirectory

$config['tempDirectory'] = sys_get_temp_dir();

/*============================ Session Cause Performance Issues =======================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_sessionWriteClose

$config['sessionWriteClose'] = true;

/*================================= CSRF protection ===================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_csrfProtection

$config['csrfProtection'] = true;

/*===================================== Headers =======================================*/
// https://docs.ckeditor.com/ckfinder/ckfinder3-php/configuration.html#configuration_options_headers

$config['headers'] = array();

/*============================== End of Configuration =================================*/

// Config must be returned - do not change it.
return $config;
