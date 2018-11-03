<?php
namespace SON\Init;

abstract class Bootstrap{
  private $routes;

  public function __construct(){
    $this->initRoutes();
    $this->run($this->getUrl());
  }

  #Root url = /gtic/public/
  abstract protected function initRoutes();

  protected function run($url){
    $route_index = array_search($url, array_column($this->routes, 'route'));
    if ($route_index === false) {
      header("HTTP/1.0 404 Not Found");
      echo "<h1>404 Not Found</h1>";
      echo "The page that you have requested could not be found.";
      exit();
    } else {
      $route = array_values($this->routes)[$route_index];
      $class = "App\\Controller\\".ucfirst($route['controller']);
      $controller = new $class;
      $controller->{$route['action']}();
    }
  }

  protected function setRoutes(array $routes){
    $this->routes = $routes;
  }

  protected function getUrl(){
    return parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
  }
}
?>
