<?php

class Application {

    private $handlers = [];

    public function get($route, $handler) {
        $this->append('GET', $route, $handler);
    }

    public function post($route, $handler) {
        $this->append('POST', $route, $handler);
    }

    public function run() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->handlers as $item) {
            list($route, $handlerMethod, $handler) = $item;
            $preparedRoute = preg_quote($route, '/');
            if ($method == $handlerMethod && preg_match("/^$preparedRoute$/i", $uri)) {
                echo $handler($_GET);
            }
        }
    }

    private function append($method, $route, $handler) {
        $this->handlers[] = [$route, $method, $handler];
    }
}
