<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;
use \SON\Di\Container;

// Set the websocket port
$io = new SocketIO(5530);

// When a client is connecting to the server
$io->on('connection', function($socket) {

    // First, send only to him a challenge to resolve
    $socket->challenge = bin2hex(random_bytes(32));
    $socket->emit('connecting', array(
        "challenge" => $socket->challenge
    ));

    // If the challenge is resolved, allow the connection.
    // Disconnect the client otherwise.
    $socket->on('challenge solution', function($token_data)use($socket) {
        $Usuario = Container::getClass("Usuario");
        $user = $Usuario->findByLogin($token_data["username"]);
        $challenge = $socket->challenge.$user["data_ultimo_login"];
        $challenge_solution = hash("sha256", $challenge, false);
        if ($challenge_solution === $token_data["challengeSolution"]) {
            $socket->userInfo = array(
                "id" => $user["id"],
                "nome" => $user["nome"]
            );
            $socket->join("authed users");
        } else {
            $socket->disconnect(true);
        }
    });
});

Worker::runAll();
?>
