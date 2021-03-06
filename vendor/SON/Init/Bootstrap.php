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
    $route = $this->routes[$url];
    if ($route !== null) {
      try {
        $class = "App\\Controller\\".ucfirst($route['controller']);
        $controller = new $class;
        $controller->{$route['action']}();
      } catch (\Exception $e) {
        $errorCode = $e->getCode();
        if ($errorCode === 2002) {
          header("HTTP/1.0 503 Failed Database Connection");
          echo "<h1>503 Service Unavailable</h1>";
          echo "Failed to connect to the database";
          exit();
        }
      }
    } else {
      header("HTTP/1.0 404 Not Found");
      echo "<h1>404 Not Found</h1>";
      echo "The page that you have requested could not be found.";
      exit();
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
