<?php

/**
 * @generate-class-entries
 * @undocumentable
 */

/**
 * @var int
 * @cvalue 1
 */
const IO_TYPE_DISK = UNKNOWN;
/**
 * @var int
 * @cvalue 2
 */
const IO_TYPE_SOCKET = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_FIXED_FILE
 */
const IOSQE_FIXED_FILE = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_IO_DRAIN
 */
const IOSQE_IO_DRAIN = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_IO_LINK
 */
const IOSQE_IO_LINK = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_IO_HARDLINK
 */
const IOSQE_IO_HARDLINK = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_ASYNC
 */
const IOSQE_ASYNC = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_BUFFER_SELECT
 */
const IOSQE_BUFFER_SELECT = UNKNOWN;
/**
 * @var int
 * @cvalue IOSQE_CQE_SKIP_SUCCESS
 */
const IOSQE_CQE_SKIP_SUCCESS = UNKNOWN;

/*
 * io_uring_setup() flags
 */
/**
 * @var int
 * @cvalue IORING_SETUP_IOPOLL
 */
const IORING_SETUP_IOPOLL = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_SQPOLL
 */
const IORING_SETUP_SQPOLL = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_SQ_AFF
 */
const IORING_SETUP_SQ_AFF = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_CQSIZE
 */
const IORING_SETUP_CQSIZE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_CLAMP
 */
const IORING_SETUP_CLAMP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_ATTACH_WQ
 */
const IORING_SETUP_ATTACH_WQ = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_R_DISABLED
 */
const IORING_SETUP_R_DISABLED = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_SUBMIT_ALL
 */
const IORING_SETUP_SUBMIT_ALL = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_COOP_TASKRUN
 */
const IORING_SETUP_COOP_TASKRUN = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_TASKRUN_FLAG
 */
const IORING_SETUP_TASKRUN_FLAG = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_SQE128
 */
const IORING_SETUP_SQE128 = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_CQE32
 */
const IORING_SETUP_CQE32 = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_SINGLE_ISSUER
 */
const IORING_SETUP_SINGLE_ISSUER = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_DEFER_TASKRUN
 */
const IORING_SETUP_DEFER_TASKRUN = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_NO_MMAP
 */
const IORING_SETUP_NO_MMAP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_REGISTERED_FD_ONLY
 */
const IORING_SETUP_REGISTERED_FD_ONLY = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SETUP_NO_SQARRAY
 */
const IORING_SETUP_NO_SQARRAY = UNKNOWN;

/*
 * sqe->uring_cmd_flags
 */
/**
 * @var int
 * @cvalue IORING_URING_CMD_FIXED
 */
const IORING_URING_CMD_FIXED = UNKNOWN;

/*
 * sqe->fsync_flags
 */
/**
 * @var int
 * @cvalue IORING_FSYNC_DATASYNC
 */
const IORING_FSYNC_DATASYNC = UNKNOWN;

/*
 * sqe->timeout_flags
 */
/**
 * @var int
 * @cvalue IORING_TIMEOUT_ABS
 */
const IORING_TIMEOUT_ABS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_UPDATE
 */
const IORING_TIMEOUT_UPDATE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_BOOTTIME
 */
const IORING_TIMEOUT_BOOTTIME = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_REALTIME
 */
const IORING_TIMEOUT_REALTIME = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_LINK_TIMEOUT_UPDATE
 */
const IORING_LINK_TIMEOUT_UPDATE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_ETIME_SUCCESS
 */
const IORING_TIMEOUT_ETIME_SUCCESS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_MULTISHOT
 */
const IORING_TIMEOUT_MULTISHOT = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_CLOCK_MASK
 */
const IORING_TIMEOUT_CLOCK_MASK = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_TIMEOUT_UPDATE_MASK
 */
const IORING_TIMEOUT_UPDATE_MASK = UNKNOWN;

/*
 * sqe->splice_flags
 */
/**
 * @var int
 * @cvalue SPLICE_F_FD_IN_FIXED
 */
const SPLICE_F_FD_IN_FIXED = UNKNOWN;

/*
 * POLL_ADD flags
 */
/**
 * @var int
 * @cvalue IORING_POLL_ADD_MULTI
 */
const IORING_POLL_ADD_MULTI = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_POLL_UPDATE_EVENTS
 */
const IORING_POLL_UPDATE_EVENTS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_POLL_UPDATE_USER_DATA
 */
const IORING_POLL_UPDATE_USER_DATA = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_POLL_ADD_LEVEL
 */
const IORING_POLL_ADD_LEVEL = UNKNOWN;

/*
 * ASYNC_CANCEL flags.
 */
/**
 * @var int
 * @cvalue IORING_ASYNC_CANCEL_ALL
 */
const IORING_ASYNC_CANCEL_ALL = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ASYNC_CANCEL_FD
 */
const IORING_ASYNC_CANCEL_FD = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ASYNC_CANCEL_ANY
 */
const IORING_ASYNC_CANCEL_ANY = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ASYNC_CANCEL_FD_FIXED
 */
const IORING_ASYNC_CANCEL_FD_FIXED = UNKNOWN;

/*
 * send/sendmsg and recv/recvmsg flags (sqe->ioprio)
 */
/**
 * @var int
 * @cvalue IORING_RECVSEND_POLL_FIRST
 */
const IORING_RECVSEND_POLL_FIRST = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_RECV_MULTISHOT
 */
const IORING_RECV_MULTISHOT = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_RECVSEND_FIXED_BUF
 */
const IORING_RECVSEND_FIXED_BUF = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SEND_ZC_REPORT_USAGE
 */
const IORING_SEND_ZC_REPORT_USAGE = UNKNOWN;

/*
 * cqe.res for IORING_CQE_F_NOTIF
 */
/**
 * @var int
 * @cvalue IORING_NOTIF_USAGE_ZC_COPIED
 */
const IORING_NOTIF_USAGE_ZC_COPIED = UNKNOWN;

/*
 * accept flags stored in sqe->ioprio
 */
/**
 * @var int
 * @cvalue IORING_ACCEPT_MULTISHOT
 */
const IORING_ACCEPT_MULTISHOT = UNKNOWN;

/*
 * IORING_OP_MSG_RING flags (sqe->msg_ring_flags)
 */
/**
 * @var int
 * @cvalue IORING_MSG_RING_CQE_SKIP
 */
const IORING_MSG_RING_CQE_SKIP = UNKNOWN;

/* Pass through the flags from sqe->file_index to cqe->flags */
/**
 * @var int
 * @cvalue IORING_MSG_RING_FLAGS_PASS
 */
const IORING_MSG_RING_FLAGS_PASS = UNKNOWN;

/*
 * IORING_OP_FIXED_FD_INSTALL flags (sqe->install_fd_flags)
 */
/**
 * @var int
 * @cvalue IORING_FIXED_FD_NO_CLOEXEC
 */
const IORING_FIXED_FD_NO_CLOEXEC = UNKNOWN;

/*
 * cqe->flags
 */
/**
 * @var int
 * @cvalue IORING_CQE_F_BUFFER
 */
const IORING_CQE_F_BUFFER = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_CQE_F_MORE
 */
const IORING_CQE_F_MORE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_CQE_F_SOCK_NONEMPTY
 */
const IORING_CQE_F_SOCK_NONEMPTY = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_CQE_F_NOTIF
 */
const IORING_CQE_F_NOTIF = UNKNOWN;

/*
 * Magic offsets for the application to mmap the data it needs
 */
/**
 * @var int
 * @cvalue IORING_OFF_SQ_RING
 */
const IORING_OFF_SQ_RING = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_OFF_CQ_RING
 */
const IORING_OFF_CQ_RING = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_OFF_SQES
 */
const IORING_OFF_SQES = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_OFF_PBUF_RING
 */
const IORING_OFF_PBUF_RING = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_OFF_PBUF_SHIFT
 */
const IORING_OFF_PBUF_SHIFT = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_OFF_MMAP_MASK
 */
const IORING_OFF_MMAP_MASK = UNKNOWN;

/*
 * sq_ring->flags
 */
/**
 * @var int
 * @cvalue IORING_SQ_NEED_WAKEUP
 */
const IORING_SQ_NEED_WAKEUP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SQ_CQ_OVERFLOW
 */
const IORING_SQ_CQ_OVERFLOW = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_SQ_TASKRUN
 */
const IORING_SQ_TASKRUN = UNKNOWN;

/* disable eventfd notifications */
/**
 * @var int
 * @cvalue IORING_CQ_EVENTFD_DISABLED
 */
const IORING_CQ_EVENTFD_DISABLED = UNKNOWN;

/*
 * io_uring_enter(2) flags
 */
/**
 * @var int
 * @cvalue IORING_ENTER_GETEVENTS
 */
const IORING_ENTER_GETEVENTS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ENTER_SQ_WAKEUP
 */
const IORING_ENTER_SQ_WAKEUP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ENTER_SQ_WAIT
 */
const IORING_ENTER_SQ_WAIT = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ENTER_EXT_ARG
 */
const IORING_ENTER_EXT_ARG = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_ENTER_REGISTERED_RING
 */
const IORING_ENTER_REGISTERED_RING = UNKNOWN;

/*
 * io_uring_params->features flags
 */
/**
 * @var int
 * @cvalue IORING_FEAT_SINGLE_MMAP
 */
const IORING_FEAT_SINGLE_MMAP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_NODROP
 */
const IORING_FEAT_NODROP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_SUBMIT_STABLE
 */
const IORING_FEAT_SUBMIT_STABLE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_RW_CUR_POS
 */
const IORING_FEAT_RW_CUR_POS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_CUR_PERSONALITY
 */
const IORING_FEAT_CUR_PERSONALITY = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_FAST_POLL
 */
const IORING_FEAT_FAST_POLL = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_POLL_32BITS
 */
const IORING_FEAT_POLL_32BITS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_SQPOLL_NONFIXED
 */
const IORING_FEAT_SQPOLL_NONFIXED = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_EXT_ARG
 */
const IORING_FEAT_EXT_ARG = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_NATIVE_WORKERS
 */
const IORING_FEAT_NATIVE_WORKERS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_RSRC_TAGS
 */
const IORING_FEAT_RSRC_TAGS = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_CQE_SKIP
 */
const IORING_FEAT_CQE_SKIP = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_LINKED_FILE
 */
const IORING_FEAT_LINKED_FILE = UNKNOWN;
/**
 * @var int
 * @cvalue IORING_FEAT_REG_REG_RING
 */
const IORING_FEAT_REG_REG_RING = UNKNOWN;

/*
 * Register a fully sparse file space, rather than pass in an array of all -1 file descriptors.
 */
/**
 * @var int
 * @cvalue IORING_RSRC_REGISTER_SPARSE
 */
const IORING_RSRC_REGISTER_SPARSE = UNKNOWN;

/* Skip updating fd indexes set to this value in the fd table */
/**
 * @var int
 * @cvalue IORING_REGISTER_FILES_SKIP
 */
const IORING_REGISTER_FILES_SKIP = UNKNOWN;
/**
 * @var int
 * @cvalue IO_URING_OP_SUPPORTED
 */
const IO_URING_OP_SUPPORTED = UNKNOWN;

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

function io_create_socket_len(): int {}

function io_free_socket_len(int $len): void {}

function io_create_socket_addr(): int {}

function io_free_socket_addr(int $addr): void {}

function io_uring_prep_accept(int $sqe, int $fd, int $clientAddr, int $clilen, int $flags): void {}

function io_uring_prep_recv(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}

function io_uring_prep_send(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}

function io_uring_peek_batch_cqe(int $ring, int $cqes, int $cqe_len): int {}

function io_uring_cq_advance(int $ring, int $count): void {}

function io_get_conn_info(): int {}

function io_set_conn_val(int $conn, string $key, int $val): void {}

function io_get_conn_val(int $conn, string $key): int {}

function io_generate_cqes(int $len): int {}

function io_get_cqe_by_index(int $cqes, int $index): int {}

function io_free_conn(int $conn): void {}

function io_close_fd(int $fd): void {}

function io_free_cqes(int $cqes): void {}

function io_free_params(int $params): void {}

function io_free_ring(int $ring): void {}

function io_buffer2Str(int $buffer, int $len): string {}

function io_str2Buffer(int $buffer, string $str): int {}