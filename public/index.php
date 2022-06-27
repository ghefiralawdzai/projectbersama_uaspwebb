<?php
/**
 * Created by Pizaini <pizaini@uin-suska.ac.id>
 * Date: 01/06/2022
 * Time: 8:23
 */

/**
 * Load PHP Composer autoload.php
 */
require __DIR__.'/../vendor/autoload.php';

/*
 * Set global response JSON
 *
 */
header('Content-Type: application/json');

/**
 * Global response format
 */
require __DIR__.'/../config/response.php';

/**
 * Load koneksi
 */
require __DIR__.'/../config/koneksi.php';

/**
 * Routing
 */
$requestUri = parse_url($_SERVER['REQUEST_URI']);
$paths = $requestUri['path'];
$pathArray = explode('/', $paths);

/*
 * URL dengan path
 * /page
 * /page/file
 * /page/file?quer=val, dll
 */
$page = $pathArray['1'] ?? '';
$file = $pathArray['2'] ?? '';
/*
 * URL /
 * Menjadi /default/index
 */
if(empty($page)){
    $page = 'default';
}
/*
 * URL /page/
 * Diarahkan ke /page/index
 */
if(empty($file)){
    $file = 'index';
}
$includeFile = __DIR__.'/../pages/'.$page.'/'.$file.'.php';

/*
 * Include file berdasarkan route
 */
if(!file_exists($includeFile)){
    http_response_code(404);
    $reply['error'] = "Not found route $page/$file";
    echo json_encode($reply);
}else{
    include $includeFile;
}