<?php
//socket_create
$clientSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($clientSocket === false) {
	die("socket_create() fail: " . socket_strerror(socket_last_error()) . "\n");
}

$connectResult = socket_connect($clientSocket, '127.0.0.1', 8888);
if ($connectResult === false) {
	die("socket_connect() fail: " . socket_strerror(socket_last_error($clientSocket)) . "\n");
}

echo "link to server success, waiting for msg...\n";

while (true) {
	echo "Enter something: ";
	$message = trim(fgets(STDIN));
	
	if ($message === "close") {
		echo "exit...\n";
		break;
	}
	
	//send msg
	socket_write($clientSocket, $message, strlen($message));
	
	//recev msg
	$response = socket_read($clientSocket, 1024);
	if ($response === false) {
		echo "socket_read() fail: " . socket_strerror(socket_last_error($clientSocket)) . "\n";
		break;
	}
	
	echo "Recv_msg: $response\n";
}

socket_close($clientSocket);
?>
