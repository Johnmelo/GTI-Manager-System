<?php
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;
use \SON\Di\Container;

// Set the websocket port
$io = new SocketIO(5530);

// When a client is connected to the server
$io->on('connection', function($socket)use($io) {

    // First, send only to him a challenge to resolve
    $socket->challenge = bin2hex(random_bytes(32));
    $socket->emit('connecting', array(
        "challenge" => $socket->challenge
    ));

    // If the challenge is resolved, allow the connection.
    // Disconnect the client otherwise.
    $socket->on('challenge solution', function($token_data)use($socket) {
        $Usuario = Container::getClass("Usuario");
        $UsuarioRole = Container::getClass("UsuarioRole");

        // User info
        $user = $Usuario->findByLogin($token_data["username"]);
        $userRole = $UsuarioRole->findByIdUser($user['id']);

        // Check if challenge was concluded
        $challenge = $socket->challenge.$user["data_ultimo_login"];
        $challenge_solution = hash("sha256", $challenge, false);
        if ($challenge_solution === $token_data["challengeSolution"]) {
            // Store the user data in its socket
            $socket->userInfo = array(
                "id" => $user["id"],
                "nome" => $user["nome"]
            );
            // Organize the users in groups (rooms)
            if ($userRole['cliente'] === "1") {
                $socket->join("clients");
            } else if ($userRole['tecnico'] === "1") {
                $socket->join("technicians");
                $socket->join("support");
            } else if ($userRole['gerente'] === "1") {
                $socket->join("admins");
                $socket->join("support");
            }
        } else {
            $socket->disconnect(true);
        }
    });

    // When support sends data
    $socket->on('from support', function($event, $data)use($socket, $io) {
        // Send to connected users from support
        $socket->broadcast->to('support')->emit($event, $data);
        // And send to the requester client
        $clientID = $data['id_cliente'];
        emitToUserID($io, $clientID, $event, $data);
    });

    // When clients sends data
    $socket->on('from client', function($event, $data)use($socket) {
        $socket->broadcast->to('support')->emit($event, $data);
    });
});

function emitToUserID($io, $userID, $event, $data) {
    // Get the connected users
    $connected = $io->nsps['/']->sockets;
    // Search for the user index
    $requesterClientIndex = array_search($userID, array_column(array_column($connected, 'userInfo'), 'id'));
    // If the user was found (it's connected to the WS server)
    if ($requesterClientIndex !== false) {
        // Get the user socket and emit to it
        $requesterClient = array_values($connected)[$requesterClientIndex];
        $requesterClient->emit($event, $data);
    }
}

Worker::runAll();
?>
