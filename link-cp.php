<?php
const QD = 64;
const BS = (32*1024);

function setup_context() {
	$ret = io_uring_queue_init(QD, 0);
	if ($ret < 0) {
		echo "queue init error!";
		die();
	}
	return $ret;
}

function queue_rw_pair($ring, $size, $offset) {
	global $fd1;
	global $fd2;
	$data = io_set_data("", $size);
	
	io_update_data($data, "read", 0);
	io_update_data($data, "offset", $offset);
	io_update_data($data, "iov_base", $data);
	io_update_data($data, "iov_len", $size);
	
	$sqe = io_uring_get_sqe($ring);
	if (!$sqe) {
		io_free_data($data);
	}
	io_uring_prep_readv($sqe, $fd1, $data, 1, $offset);
	io_sqe_set_flag($sqe, IOSQE_IO_LINK);
	io_uring_sqe_set_data($sqe, $data);
	
	$sqe2 = io_uring_get_sqe($ring);
	io_uring_prep_writev($sqe2, $fd2, $data, 1, $offset);
	io_uring_sqe_set_data($sqe2, $data);
}

function handle_cqe($ring, $cqe, $inflight) {
	$data = io_uring_cqe_get_data($cqe);
	$ret = 0;
	
	io_update_data($data, "read", io_query_data($data, "read") + 1);
	
	if (io_get_cqe_res($cqe) < 0) {
		if (io_get_cqe_res($cqe) == -125) {
			queue_rw_pair($ring, io_query_data($data, "iov_len"), io_query_data($data, "offset"));
			$inflight += 2;
		} else {
			echo "cqe error...";
			$ret = 1;
		}
	}
	
	if (io_query_data($data, "read") == 2) {
		io_free_data($data);
	}
	io_uring_cqe_seen($ring, $cqe);
	return $ret;
}

function copy_file($ring, $insize, $inflight) {
	$offset = 0;
	$cqe = io_generate_cqe();
	$reserve_cqe = $cqe;
	while ($insize) {
		$has_inflight = $inflight;
		
		while ($insize && $inflight < QD) {
			$this_size = BS;
			if ($this_size > $insize)
				$this_size = $insize;
			
			queue_rw_pair($ring, $this_size, $offset);
			$offset += $this_size;
			$insize -= $this_size;
			$inflight += 2;
		}
		if ($has_inflight != $inflight)
			io_uring_submit($ring);
			
		if ($insize)
			$depth = QD;
		else
			$depth = 1;
		
		while ($inflight >= $depth) {
			$ret = io_uring_wait_cqe($ring, $cqe);
			if ($ret < 0) {
				echo "wait cqe";
				return 1;
			}
			$cqe = $ret;
			if (handle_cqe($ring, $cqe, $inflight))
				return 1;
			$inflight--;
		}
	}
	io_free_cqe($reserve_cqe);
	return 0;
}

//$read_path = "/usr/local/nginx/logs/111.log";
//$write_path = "/tmp/111.log";
static $inflight;
if ($argc != 3) {
	echo "Usage: " . $argv[0] . " [source_file] [dest_file] ...\n";
	exit;
}
$read_path = $argv[1];
$write_path = $argv[2];
$fd1 = fopen($read_path, "r");
$fd2 = fopen($write_path, "w");

$ring = setup_context();
$insize = filesize($read_path);
$ret = copy_file($ring, $insize, $inflight);

fclose($fd1);
fclose($fd2);
io_uring_queue_exit($ring);