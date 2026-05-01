<?php
session_start();

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = ucfirst($url[0]) . 'Controller';
$controllerFile = 'controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName;
    $method = isset($url[1]) ? $url[1] : 'index';
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], array_slice($url, 2));
    } else {
        http_response_code(404);
        echo "404 Not Found";
    }
} else {
    http_response_code(404);
    echo "404 Not Found";
}
