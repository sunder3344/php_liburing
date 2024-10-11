<?php
const EVENT_ACCEPT = 0;
const EVENT_READ = 1;
const EVENT_WRITE = 2;
const LISTENQ = 10;
const CQE_LEN = 10;
const RING_LEN = 1024;			//ring queue num
const MAXLINE = 4096;			//buffer length
const MAX_CONN = 4096;			//connect_pool length

const IO_TYPE_DISK = 1;
const IO_TYPE_SOCKET = 2;
const IORING_SETUP_SQPOLL = 2;

$server_port = 8888;
$server_addr = "0.0.0.0";

function set_accept_event($ring, $sfd, $clientAddr, $clilen, $flags = 0) {
// 	global $pool;
	$sqe = io_uring_get_sqe($ring);
	io_uring_prep_accept($sqe, $sfd, $clientAddr, $clilen, $flags);
	
// 	$info = get_conn_info($pool);
	$info = io_get_conn_info();
	if ($info) {
		io_set_conn_val($info, "connfd", $sfd);
		io_set_conn_val($info, "event", EVENT_ACCEPT);
		io_uring_sqe_set_data($sqe, $info, IO_TYPE_SOCKET);
	} else {
		echo "Get conn failed...";
		io_free_conn($info);
// 		$pool = return_conn_info($pool, $info);
	}
}

function set_recv_event($ring, $sfd, $info, $flags) {
	$sqe = io_uring_get_sqe($ring);
	io_uring_prep_recv($sqe, $sfd, io_get_conn_val($info, "buffer"), MAXLINE, $flags);
	
	io_set_conn_val($info, "connfd", $sfd);
	io_set_conn_val($info, "event", EVENT_READ);
	io_uring_sqe_set_data($sqe, $info, IO_TYPE_SOCKET);
}

function set_send_event($ring, $sfd, $info, $length, $flags) {
	$sqe = io_uring_get_sqe($ring);
	io_uring_prep_send($sqe, $sfd, io_get_conn_val($info, "buffer"), $length, $flags);
	
	io_set_conn_val($info, "connfd", $sfd);
	io_set_conn_val($info, "event", EVENT_WRITE);
	io_uring_sqe_set_data($sqe, $info, IO_TYPE_SOCKET);
}


//main function
$serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($serverSocket === false) {
	die("socket_create() fail: " . socket_strerror(socket_last_error()) . "\n");
}
if (defined('SO_REUSEPORT')) {
	socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
}

if (defined('SO_REUSEPORT')) {
	socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEPORT, 1);
}
	
//socket bind
$bindResult = socket_bind($serverSocket, $server_addr, $server_port);
if ($bindResult === false) {
	die("socket_bind() fail: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
}

//socket listen
$listenResult = socket_listen($serverSocket, LISTENQ);
if ($listenResult === false) {
	die("socket_listen() fail: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
}

//init uring
$ring = io_create_ring();
$params = io_init_params();
// $pool = init_conn_pool(MAX_CONN);
io_setup_params($params, "flags", IORING_SETUP_SQPOLL);
io_setup_params($params, "sq_thread_idle", 2000);
$res = io_uring_queue_init_params(RING_LEN, $ring, $params);
if ($res < 0) {
	die("set uring param error!");
}

$stream = socket_export_stream($serverSocket);
if ($stream === false) {
	die("socket_export_stream() error\n");
}

//file description
$sockfd = (int) $stream;
$clilen = io_create_socket_len();
$clientAddr = io_create_socket_addr();

set_accept_event($ring, $sockfd, $clientAddr, $clilen, 0);

echo "waiting for client: ".$sockfd."...\n";

//receive client msg...
$cqe = io_generate_cqe();
$reserve_cqe = $cqe;
while (true) {
	$tmp = io_uring_submit($ring);
	if ($tmp < 0) {
		continue;
	}
	
	//waiting here
	$new_cqe = io_uring_wait_cqe($ring, $cqe);
	if ($new_cqe < 0) {
		continue;
	}
	
	$cqes = io_generate_cqes(CQE_LEN);

	$cqecount = io_uring_peek_batch_cqe($ring, $cqes, CQE_LEN);
	for ($i = 0; $i < $cqecount; $i++) {
		$new_cqe = io_get_cqe_by_index($cqes, $i);
		$ci = io_uring_cqe_get_data($new_cqe, IO_TYPE_SOCKET);
		if ($ci < 0) {
			continue;
		}
		if (io_get_conn_val($ci, "event") == EVENT_ACCEPT) {
			if (io_get_cqe_res($new_cqe) < 0) {
				echo "cqe->res=".io_get_cqe_res($new_cqe);
				io_free_conn($ci);
// 				$pool = return_conn_info($pool, $ci);
				continue;
			}
			$connfd = io_get_cqe_res($new_cqe);				//if cqe->res > 0, it means success，res is new fd
// 			$new_info = get_conn_info($pool);
			$new_info = io_get_conn_info();
			if ($new_info) {
				set_recv_event($ring, $connfd, $new_info, 0);
			} else {
				echo "Get conn failed!";
				io_close_fd(io_get_conn_val($ci, "connfd"));
				io_free_conn($ci);
// 				$pool = return_conn_info($pool, $ci);
				continue;
			}
			set_accept_event($ring, io_get_conn_val($ci, "connfd"), $clientAddr, $clilen, 0);
			io_free_conn($ci);
// 			$pool = return_conn_info($pool, $ci);
		} else if (io_get_conn_val($ci, "event") == EVENT_READ) {
			
			if (io_get_cqe_res($new_cqe) == 0) {
				echo "client close\n";
				io_close_fd(io_get_conn_val($ci, "connfd"));
				io_free_conn($ci);
// 				$pool = return_conn_info($pool, $ci);
			} else if (io_get_cqe_res($new_cqe) < 0) {
				echo "read error, cqe_res=".io_get_cqe_res($new_cqe);
				io_close_fd(io_get_conn_val($ci, "connfd"));
				io_free_conn($ci);
// 				$pool = return_conn_info($pool, $ci);
			} else {
				//self-defined response
				$buff = io_get_conn_val($ci, "buffer");
				$buff_len = io_get_cqe_res($new_cqe);
				$buff_str = io_buffer2Str($buff, $buff_len);
				echo "recv: ".$buff.", ".$buff_str.PHP_EOL;
				io_set_conn_val($ci, "buffer_length", io_get_cqe_res($new_cqe));
				
				$echoStr = "什么？开玩笑？？!!!这就是你的回答：".$buff_str;
				io_str2Buffer($buff, $echoStr);
				io_set_conn_val($ci, "buffer", $buff);
				io_set_conn_val($ci, "buffer_length", strlen($echoStr));
				set_send_event($ring, io_get_conn_val($ci, "connfd"), $ci, strlen($echoStr), 0);
				
				//echo response
				//set_send_event($ring, io_get_conn_val($ci, "connfd"), $ci, io_get_cqe_res($new_cqe), 0);
			}
		} else if (io_get_conn_val($ci, "event") == EVENT_WRITE) {
			if (io_get_cqe_res($new_cqe) < 0) {
				echo ("write error, cqe_res=".io_get_cqe_res($new_cqe));
				io_close_fd(io_get_conn_val($ci, "connfd"));
				io_free_conn($ci);
// 				$pool = return_conn_info($pool, $ci);
			}
			set_recv_event($ring, io_get_conn_val($ci, "connfd"), $ci, 0);
		}
	}
	io_uring_cq_advance($ring, $cqecount);
	io_free_cqes($cqes);
}
io_free_cqe($reserve_cqe);

//close socket
socket_close($serverSocket);
//close ring_params
io_free_params($params);
//close ring
io_free_ring($ring);
//free connection pool
//release_conn_info($pool);
//free socketaddr
io_free_socket_addr($clientAddr);
//free socklen
io_free_socket_len($clilen);
?>
