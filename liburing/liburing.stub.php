<?php

/**
 * @generate-class-entries
 * @undocumentable
 */

function test_echo(string $str = ""): string {}

function test_pass(int &$num): int {}

function init_conn_pool(int $size): int {}

function get_conn_info(int &$pool): int {}

function return_conn_info(int $pool, int $conn): int {}

function release_conn_info(int $pool): void {}

function io_uring_queue_init(int $depth, int $flag = 0): int {}

function io_uring_get_sqe(int $ring): int {}

function io_init_data(): int {}

function io_set_data(string $content = "", int $size): int {}

function io_update_data(int $iodata, string $key, int $val): void {}

function io_query_data(int $iodata, string $key): int {}

function io_free_data(int $iodata): void {}

function io_uring_prep_readv(int $sqe, object $file, int $iodata, int $nr_vecs, int $offset): int {}

function io_uring_sqe_set_data(int $sqe, int $iodata, int $type): void {};

function io_uring_prep_writev(int $sqe, object $file, int $iodata, int $nr_vecs, int $offset): int {}

function io_uring_submit(int $ring): int {}

function io_generate_cqe() : int {}

function io_uring_wait_cqe(int $ring, int $cqe) : int {}

function io_uring_peek_cqe(int $ring, int $cqe) : int {}

function io_cqe_set_flag(int $cqe, int $flag): void {}

function io_uring_cqe_seen(int $ring, int $cqe) : int {}

function io_uring_cqe_get_data(int $cqe, int $type) : int {}

function io_get_cqe_res(int $cqe): int {}

function io_free_cqe(int $cqe): void {}

function io_sqe_set_flag(int $sqe, int $flag): void {}

function io_uring_queue_exit(int $ring): void {}

function io_init_params(): int {}

function io_setup_params(int $params, string $attr, int $val): void {}

function io_create_ring(): int {}

function io_uring_queue_init_params(int $depth, int $ring, int $params): int {}

function io_uring_prep_accept(int $sqe, int $fd, int $flags): void {}

function io_uring_prep_recv(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}

function io_uring_prep_send(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}

function io_uring_peek_batch_cqe(int $ring, int $cqes, int $cqe_len): int {}

function io_uring_cq_advance(int $ring, int $count): void {}

function io_get_conn_info(): int {}

function io_set_conn_val(int $conn, int $key, int $val): void {}

function io_get_conn_val(int $conn, int $key): int {}

function io_generate_cqes(int $len): int {}

function io_get_cqe_by_index(int $cqes, int $index): int {}

function io_free_conn(int $conn): void {}

function io_close_fd(int $fd): bool {}

function io_free_cqes(int $cqes): void {}

function io_free_params(int $params): void {}

function io_free_ring(int $ring): void {}

function io_buffer2Str(int $buffer, int $len): string {}

function io_str2Buffer(int $buffer, string $str): void {}