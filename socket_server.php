<?php
// 创建一个 TCP/IP 套接字
$serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($serverSocket === false) {
	die("socket_create() 失败: " . socket_strerror(socket_last_error()) . "\n");
}
if (defined('SO_REUSEPORT')) {
	socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
}

if (defined('SO_REUSEPORT')) {
	socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEPORT, 1);
}

// 绑定到本地地址和端口
$bindResult = socket_bind($serverSocket, '127.0.0.1', 8888);
if ($bindResult === false) {
	die("socket_bind() 失败: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
}

// 监听连接
$listenResult = socket_listen($serverSocket, 10);
if ($listenResult === false) {
	die("socket_listen() 失败: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
}

echo "等待客户端连接...\n";

// 无限循环以接收客户端连接
while (true) {
	// 接收客户端连接
	$clientSocket = socket_accept($serverSocket);
	if ($clientSocket === false) {
		echo "socket_accept() 失败: " . socket_strerror(socket_last_error($serverSocket)) . "\n";
		continue;
	}
	
	echo "客户端连接成功，等待消息...\n";
	
	// 无限循环监听客户端消息
	while (true) {
		// 读取客户端发送的消息
		$message = socket_read($clientSocket, 1024);
		
		// 如果消息为空，表示客户端已断开连接
		if ($message === false || $message === "") {
			echo "客户端断开连接.\n";
			socket_close($clientSocket);
			break;
		}
		
		echo "收到消息: $message\n";
		
		// 处理客户端消息，生成响应
		$response = "服务器收到: " . trim($message);
		socket_write($clientSocket, $response, strlen($response));
	}
}

// 关闭服务器套接字
socket_close($serverSocket);
?>
