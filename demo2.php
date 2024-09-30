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

function queue_prepped($ring, $data) {
	global $fd1;
	global $fd2;
	$sqe = io_uring_get_sqe($ring);
	
	if (io_query_data($data, "read")) {
		io_uring_prep_readv($sqe, $fd1, $data, 1, io_query_data($data, "offset"));
	} else {
		io_uring_prep_writev($sqe, $fd2, $data, 1, io_query_data($data, "offset"));
	}
	io_uring_sqe_set_data($sqe, $data);
}

function queue_read($ring, $size, $offset) {
	global $fd1;
	$data = io_set_data("", $size);
	
	$sqe = io_uring_get_sqe($ring);
	if (!$sqe) {
		io_free_data($data);
		return 1;
	}
	io_update_data($data, "read", 1);
	io_update_data($data, "offset", $offset);
	io_update_data($data, "first_offset", $offset);
	io_update_data($data, "iov_base", $data);
	io_update_data($data, "iov_len", $size);
	io_update_data($data, "first_len", $size);
	
	io_uring_prep_readv($sqe, $fd1, $data, 1, $offset);
	io_uring_sqe_set_data($sqe, $data);
	return 0;
}

function queue_write($ring, $data) {
	io_update_data($data, "read", 0);
	io_update_data($data, "offset", io_query_data($data, "first_offset"));
	io_update_data($data, "iov_base", $data);
	io_update_data($data, "iov_len", io_query_data($data, "first_len"));
	
	queue_prepped($ring, $data);
	io_uring_submit($ring);
}

function copy_file($ring, $insize) {
	$write_left = $insize;
	$writes = 0;
	$reads = 0;
	$offset = 0;
	$cqe = io_generate_cqe();
	$reserve_cqe = $cqe;
	
	while ($insize || $write_left) {
		$had_reads = 0;
		$got_comp = 0;
		
		$had_reads = $reads;
		while ($insize) {
			$this_size = $insize;
			
			if ($reads + $writes >= QD) {
				break;
			}
			if ($this_size > BS) {
				$this_size = BS;
			} else if (!$this_size) {
				break;
			}
			if (queue_read($ring, $this_size, $offset))
				break;
			$insize -= $this_size;
			$offset += $this_size;
			$reads++;
		}
		
		if ($had_reads != $reads) {
			$ret = io_uring_submit($ring);
			if ($ret < 0) {
				echo "io_uring_submit: error!";
				break;
			}
		}
		
		$got_comp = 0;
		while ($write_left) {
			//$data = io_init_data();
			
			if (!$got_comp) {
				$ret = io_uring_wait_cqe($ring, $cqe);
				$got_comp = 1;
			} else {
				$ret = io_uring_peek_cqe($ring, $cqe);
				if ($ret == -11) {
					$cqe = NULL;
					$ret = 0;
				}
			}
			if ($ret < 0) {
				echo "io_uring_peek_cqe: error";
				return 1;
			}
			$cqe = $ret;
			//if (!$cqe)
			if ($ret == 0)
				break;
			
			$data = io_uring_cqe_get_data($cqe);
			if (io_get_cqe_res($cqe) < 0) {
				if (io_get_cqe_res($cqe) == -11) {
					queue_prepped($ring, $data);
					io_uring_submit($ring);
					io_uring_cqe_seen($ring, $cqe);
					continue;
				}
				echo "cqe failed";
				return 1;
			} else if (io_get_cqe_res($cqe) != io_query_data($data, "iov_len")) {
				/* Short read/write, adjust and requeue */
				io_update_data($data, "iov_base", io_query_data($data, "iov_base") + io_get_cqe_res($cqe));
				io_update_data($data, "iov_len", io_query_data($data, "iov_len") - io_get_cqe_res($cqe));
				io_update_data($data, "offset", io_query_data($data, "offset") + io_get_cqe_res($cqe));
				queue_prepped($ring, $data);
				io_uring_submit($ring);
				io_uring_cqe_seen($ring, $cqe);
				continue;
			}
			
			/*
			 * All done. if write, nothing else to do. if read,
			 * queue up corresponding write.
			 */
			if (io_query_data($data, "read") == 1) {
				queue_write($ring, $data);
				$write_left -= io_query_data($data, "first_len");
				$reads--;
				$writes++;
			} else {
				io_free_data($data);
				$writes--;
			}
			io_uring_cqe_seen($ring, $cqe);
		}
	}
	
	/* wait out pending writes */
	while ($writes) {
		//$data = io_init_data();
		
		$cqe = io_uring_wait_cqe($ring, $cqe);
		if ($cqe < 0) {
			echo "wait_cqe error\n";
			return 1;
		}
		if (io_get_cqe_res($cqe) < 0) {
			echo "write res =".io_get_cqe_res($cqe);
			return 1;
		}
		$data = io_uring_cqe_get_data($cqe);
		io_free_data($data);
		$writes--;
		io_uring_cqe_seen($ring, $cqe);
	}
	
	io_free_cqe($reserve_cqe);
	return 0;
}

//$read_path = "/usr/local/nginx/logs/111.log";
//$write_path = "/tmp/111.log";
if ($argc != 3) {
	echo "Usage: " . $argv[0] . " param1 [param2] ...\n";
	exit;
}
$read_path = $argv[1];
$write_path = $argv[2];
$fd1 = fopen($read_path, "r");
$fd2 = fopen($write_path, "w");

$ring = setup_context();
$insize = filesize($read_path);
$ret = copy_file($ring, $insize);

fclose($fd1);
fclose($fd2);
io_uring_queue_exit($ring);