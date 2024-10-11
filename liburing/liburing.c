/**
 * Copyright (C) Derek Sunder
 *  ____                _      ____                  _
 * |  _ \  ___ _ __ ___| | __ / ___| _   _ _ __   __| | ___ _ __
 * | | | |/ _ \ '__/ _ \ |/ / \___ \| | | | '_ \ / _` |/ _ \ '__|
 * | |_| |  __/ | |  __/   <   ___) | |_| | | | | (_| |  __/ |
 * |____/ \___|_|  \___|_|\_\ |____/ \__,_|_| |_|\__,_|\___|_|
 */

/* liburing extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_liburing.h"
#include "liburing_arginfo.h"
#include <liburing.h>
//#include "liburing_head.h"
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <netinet/in.h>

#define QD	64
#define BS	(32*1024)
#define MAXLINE 4096
#define MAX_CONN 2048			//connection pool num

#define IO_TYPE_DISK 1
#define IO_TYPE_SOCKET 2

struct io_uring ring;
int conn_pool_count = 0;

struct io_data {
	int read;
	off_t first_offset, offset;
	size_t first_len;
	struct iovec iov;
};

struct ConnInfo {
	int connfd;
	int event;
	char buffer[MAXLINE];
	size_t buffer_length;
	struct ConnInfo *next;
} __attribute__((aligned(64)));

/* For compatibility with older PHP versions */
#ifndef ZEND_PARSE_PARAMETERS_NONE
#define ZEND_PARSE_PARAMETERS_NONE() \
	ZEND_PARSE_PARAMETERS_START(0, 0) \
	ZEND_PARSE_PARAMETERS_END()
#endif

/* {{{ string test_echo2( [ string $var ] ) */
PHP_FUNCTION(test_echo)
{
	char *var = "World";
	size_t var_len = sizeof("World") - 1;
	zend_string *retval;

	ZEND_PARSE_PARAMETERS_START(0, 1)
		Z_PARAM_OPTIONAL
		Z_PARAM_STRING(var, var_len)
	ZEND_PARSE_PARAMETERS_END();

	retval = strpprintf(0, "Hello %s", var);

	RETURN_STR(retval);
}
/* }}}*/

/* {{{ int void init_conn_pool( [ int $size ] ) */
PHP_FUNCTION(init_conn_pool)
{
	int i;
	unsigned long size = 0;

	ZEND_PARSE_PARAMETERS_START(0, 1)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(size)
	ZEND_PARSE_PARAMETERS_END();

	if (size == 0)
		size = MAX_CONN;

	conn_pool_count = size;
	struct ConnInfo *conn_pool = malloc(sizeof(struct ConnInfo));
	conn_pool = NULL;

	for (i = 0; i < size; i++) {
		struct ConnInfo *conn = malloc(sizeof(struct ConnInfo));
		conn->next = conn_pool;
		conn_pool = conn;
	}
	RETURN_LONG((unsigned long)conn_pool);
}
/* }}}*/

/* {{{ void test_pass( [ int $num ] ) */
PHP_FUNCTION(test_pass)
{
	int result = 0;
	zval *num;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "z", &num) == FAILURE) {
		result = 0;
	}

	ZVAL_DEREF(num);
	ZVAL_LONG(num, 12345);
	php_printf("num=%ld\n", *num);
	RETURN_LONG(Z_LVAL_P(num));
}

/* {{{ int get_conn_info( [ int $pool ] ) */
PHP_FUNCTION(get_conn_info)
{
	int i;
	unsigned long result = 0;
	zval *pool;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_ZVAL(pool)
	ZEND_PARSE_PARAMETERS_END();

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "z", &pool) == FAILURE) {
		result = 0;
	}

	ZVAL_DEREF(pool);
	struct ConnInfo *conn_pool = (struct ConnInfo *)Z_PTR_P(pool);
	if (!conn_pool)
		result = 0;
	//php_printf("get_conn_info: conn_pool=%p, %lu\n", conn_pool, pool);

	struct ConnInfo *conn = conn_pool;
	conn_pool = conn_pool->next;
	conn_pool_count--;
	result = (unsigned long)conn;
	ZVAL_LONG(pool, (unsigned long)conn_pool);
	//php_printf("get_conn_info: conn=%p, %lu\n", conn, result);
	//php_printf("get_conn_info: pool=%p, %lu\n", conn_pool, *pool);
	RETURN_LONG(result);
}
/* }}}*/

/* {{{ int return_conn_info( [ int $pool, int $conn ] ) */
PHP_FUNCTION(return_conn_info)
{
	unsigned long conn = 0;
	unsigned long pool = 0;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(pool)
		Z_PARAM_LONG(conn)
	ZEND_PARSE_PARAMETERS_END();

	struct ConnInfo *conn_ptr = (struct ConnInfo *)conn;
	struct ConnInfo *pool_ptr = (struct ConnInfo *)pool;
	conn_ptr->next = pool_ptr;
	pool_ptr = conn_ptr;
	conn_pool_count++;
	RETURN_LONG((unsigned long)pool_ptr);
}
/* }}}*/

/* {{{ void release_conn_info( [ int $pool ] ) */
PHP_FUNCTION(release_conn_info)
{
	unsigned long pool = 0;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(pool)
	ZEND_PARSE_PARAMETERS_END();

	struct ConnInfo *current = (struct ConnInfo *)pool;
	while (current) {
		struct ConnInfo *next = current->next;
		free(current);
		current = next;
	}
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_queue_init( [ int $depth, int flag ] ) */
PHP_FUNCTION(io_uring_queue_init)
{
	unsigned long depth = QD;
	unsigned long flag = 0;
	unsigned long result = 0;
	int ret;

	ZEND_PARSE_PARAMETERS_START(1, 2)
		Z_PARAM_LONG(depth)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(flag)
	ZEND_PARSE_PARAMETERS_END();

	ret = io_uring_queue_init(depth, &ring, flag);
	if (ret < 0) {
		php_printf("io_uring_init error: %s\n", strerror(-ret));
		RETURN_LONG(-1);
	}
	result = (unsigned long)&ring;
	RETURN_LONG(result);
}
/* }}}*/

/* {{{ long io_uring_get_sqe( [ int $ring ] ) */
PHP_FUNCTION(io_uring_get_sqe)
{
	unsigned long ring = 0;
	struct io_uring_sqe *sqe;
	unsigned long sqe_l;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(ring)
	ZEND_PARSE_PARAMETERS_END();

	sqe = io_uring_get_sqe((struct io_uring *)ring);
	sqe_l = (unsigned long)sqe;
	RETURN_LONG(sqe_l);
}
/* }}}*/

/* {{{ long io_init_data( [ ] ) */
PHP_FUNCTION(io_init_data)
{
	struct io_data *data;

	RETURN_LONG((unsigned long)data);
}
/* }}}*/

/* {{{ long io_set_data( [ string $content, int $size ] ) */
PHP_FUNCTION(io_set_data)
{
	int result = 0;
	char *content;
	size_t content_len;
	unsigned long size;
	//unsigned long offset;
	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_STRING(content, content_len)
		Z_PARAM_LONG(size)
	ZEND_PARSE_PARAMETERS_END();
	struct io_data *data;
	data = malloc(size + sizeof(*data));
	if (!data)
		RETURN_LONG(-1);

	/*data->read = 1;
	data->offset = data->first_offset = offset;

	data->iov.iov_base = data + 1;
	data->iov.iov_len = size;
	data->first_len = size;*/

	RETURN_LONG((unsigned long)data);
}
/* }}}*/

/* {{{ long io_update_data( [ int $iodata, string $key, int $val ] ) */
PHP_FUNCTION(io_update_data)
{
	int result = 0;
	char *key;
	size_t key_len;
	unsigned long val;
	unsigned long iodata;
	ZEND_PARSE_PARAMETERS_START(3, 3)
		Z_PARAM_LONG(iodata)
		Z_PARAM_STRING(key, key_len)
		Z_PARAM_LONG(val)
	ZEND_PARSE_PARAMETERS_END();

	struct io_data *data = (struct io_data *)iodata;
	if (!data)
		RETURN_LONG(-1);

	if (strcmp(key, "read") == 0) {
		data->read = val;
	}
	if (strcmp(key, "offset") == 0) {
		data->offset = val;
	}
	if (strcmp(key, "first_offset") == 0) {
		data->first_offset = val;
	}
	if (strcmp(key, "iov_base") == 0) {
		//struct io_data *data_base = (struct io_data *)val;
		data->iov.iov_base = (struct io_data *)val + 1;
	}
	if (strcmp(key, "iov_len") == 0) {
		data->iov.iov_len = val;
	}
	if (strcmp(key, "first_len") == 0) {
		data->first_len = val;
	}

	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_query_data( [ int $iodata, string $key ] ) */
PHP_FUNCTION(io_query_data)
{
	unsigned long result = 0;
	char *key;
	size_t key_len;
	unsigned long val;
	unsigned long iodata;
	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(iodata)
		Z_PARAM_STRING(key, key_len)
	ZEND_PARSE_PARAMETERS_END();

	struct io_data *data = (struct io_data *)iodata;
	if (!data)
		RETURN_LONG(-1);

	if (strcmp(key, "read") == 0) {
		result = data->read;
	}
	if (strcmp(key, "offset") == 0) {
		result = data->offset;
	}
	if (strcmp(key, "first_offset") == 0) {
		result = data->first_offset;
	}
	if (strcmp(key, "iov_base") == 0) {
		result = (unsigned long)data->iov.iov_base;
	}
	if (strcmp(key, "iov_len") == 0) {
		result = data->iov.iov_len;
	}
	if (strcmp(key, "first_len") == 0) {
		result = data->first_len;
	}

	RETURN_LONG(result);
}
/* }}}*/

/* {{{ void io_free_data( [ int $iodata ] ) */
PHP_FUNCTION(io_free_data)
{
	unsigned long iodata;
	unsigned long offset;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(iodata)
	ZEND_PARSE_PARAMETERS_END();

	struct io_data *data = (struct io_data *)iodata;

	if (data != NULL) {
		free(data);
		data = NULL;
	}

	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_prep_readv( [ int $sqe, object $file, int $iodata, int $nr_vecs, int offset ] ) */
PHP_FUNCTION(io_uring_prep_readv)
{
	int result = 0;
	unsigned long sqe;
	unsigned long fd;
	unsigned long iodata;
	unsigned long nr_vecs;
	unsigned long offset;
	zval *file;
	php_stream *stream = NULL;

	ZEND_PARSE_PARAMETERS_START(5, 5)
		Z_PARAM_LONG(sqe)
		Z_PARAM_RESOURCE(file)
		Z_PARAM_LONG(iodata)
		Z_PARAM_LONG(nr_vecs)
		Z_PARAM_LONG(offset)
	ZEND_PARSE_PARAMETERS_END();

	php_stream_from_zval(stream, file);
	if (FAILURE == php_stream_cast(stream, PHP_STREAM_AS_FD, (void *) &fd, REPORT_ERRORS)) {
		php_error_docref(NULL, E_WARNING, "Failed to retrieve file descriptor");
		RETURN_LONG(0);
	}
	struct io_data *data = (struct io_data *)iodata;

	io_uring_prep_readv((struct io_uring_sqe *)sqe, fd, &data->iov, nr_vecs, data->offset);
	RETURN_LONG(result);
}
/* }}}*/

/* {{{ long io_uring_sqe_set_data( [ int $sqe, int $iodata/$conn, int $type ] ) */
PHP_FUNCTION(io_uring_sqe_set_data)
{
	unsigned long sqe;
	unsigned long iodata;
	unsigned long nr_vecs;
	unsigned long offset;
	unsigned long io_type;
	ZEND_PARSE_PARAMETERS_START(2, 3)
		Z_PARAM_LONG(sqe)
		Z_PARAM_LONG(iodata)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(io_type)
	ZEND_PARSE_PARAMETERS_END();

	//php_printf("io_uring_sqe_set_data:=%d, %d, %d", io_type, IO_TYPE_SOCKET, IO_TYPE_DISK);
	if (io_type == IO_TYPE_SOCKET) {
		struct ConnInfo *data = (struct ConnInfo *)iodata;
		io_uring_sqe_set_data((struct io_uring_sqe *)sqe, data);
	} else {
		struct io_data *data = (struct io_data *)iodata;
		io_uring_sqe_set_data((struct io_uring_sqe *)sqe, data);
	}
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_prep_writev( [ int $sqe, object $file, int $iodata, int $nr_vecs, int offset ] ) */
PHP_FUNCTION(io_uring_prep_writev)
{
	int result = 0;
	unsigned long sqe;
	unsigned long fd;
	unsigned long iodata;
	unsigned long nr_vecs;
	unsigned long offset;
	zval *file;
	php_stream *stream = NULL;

	ZEND_PARSE_PARAMETERS_START(5, 5)
		Z_PARAM_LONG(sqe)
		Z_PARAM_RESOURCE(file)
		Z_PARAM_LONG(iodata)
		Z_PARAM_LONG(nr_vecs)
		Z_PARAM_LONG(offset)
	ZEND_PARSE_PARAMETERS_END();

	php_stream_from_zval(stream, file);
	if (FAILURE == php_stream_cast(stream, PHP_STREAM_AS_FD, (void *) &fd, REPORT_ERRORS)) {
		php_error_docref(NULL, E_WARNING, "Failed to retrieve file descriptor");
		RETURN_LONG(0);
	}
	struct io_data *data = (struct io_data *)iodata;

	io_uring_prep_writev((struct io_uring_sqe *)sqe, fd, &data->iov, nr_vecs, data->offset);
	RETURN_LONG(result);
}
/* }}}*/

/* {{{ long io_uring_submit( [ int $ring ] ) */
PHP_FUNCTION(io_uring_submit)
{
	unsigned long ring;
	int ret;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(ring)
	ZEND_PARSE_PARAMETERS_END();

	ret = io_uring_submit((struct io_uring *)ring);
	RETURN_LONG(ret);
}
/* }}}*/

/* {{{ long io_generate_cqe( [ ] ) */
PHP_FUNCTION(io_generate_cqe)
{
	ZEND_PARSE_PARAMETERS_START(0, 0)

	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_cqe *cqe;
	cqe = (struct io_uring_cqe *)malloc(sizeof(struct io_uring_cqe));
	memset(cqe, 0, sizeof(struct io_uring_cqe));
	RETURN_LONG((unsigned long)cqe);
}
/* }}}*/

/* {{{ io_free_cqe( [ int $cqe ] ) */
PHP_FUNCTION(io_free_cqe)
{
	unsigned long cqe;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(cqe)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_cqe *cqe_ptr = (struct io_uring_cqe *)cqe;
	if (cqe_ptr != NULL) {
		free(cqe_ptr);
		cqe_ptr = NULL;
	}
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_sqe_set_flag( [ int $sqe, int $flag ] ) */
PHP_FUNCTION(io_sqe_set_flag)
{
	unsigned long sqe;
	unsigned long flag;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(sqe)
		Z_PARAM_LONG(flag)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_sqe *sqe_ptr = (struct io_uring_sqe *)sqe;
	sqe_ptr->flags |= flag;
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_cqe_seen( [ int $ring, int $cqe ] ) */
PHP_FUNCTION(io_uring_cqe_seen)
{
	int ret;
	unsigned long ring;
	unsigned long cqe;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(cqe)
	ZEND_PARSE_PARAMETERS_END();

	io_uring_cqe_seen((struct io_uring *)ring, (struct io_uring_cqe *)cqe);
	RETURN_LONG(cqe);
}
/* }}}*/

/* {{{ long io_uring_wait_cqe( [ int $ring, int $cqe ] ) */
PHP_FUNCTION(io_uring_wait_cqe)
{
	int ret;
	unsigned long ring;
	unsigned long cqe;
	struct io_uring_cqe *cqe_ptr;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(cqe)
	ZEND_PARSE_PARAMETERS_END();

	if (cqe != 0) {
		cqe_ptr = (struct io_uring_cqe *)cqe;
	}
	ret = io_uring_wait_cqe((struct io_uring *)ring, &cqe_ptr);

	if (ret < 0) {
		//php_printf("wait_ret:=%s, %s\n", strerror(-ret), strerror(errno));
		RETURN_LONG(ret); // return error
	}
	RETURN_LONG((unsigned long)cqe_ptr);
}
/* }}}*/

/* {{{ long io_uring_peek_cqe( [ int $ring, int $cqe ] ) */
PHP_FUNCTION(io_uring_peek_cqe)
{
	int ret;
	unsigned long ring;
	unsigned long cqe;
	struct io_uring_cqe *cqe_ptr;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(cqe)
	ZEND_PARSE_PARAMETERS_END();

	if (cqe != 0) {
		cqe_ptr = (struct io_uring_cqe *)cqe;
	}
	ret = io_uring_peek_cqe((struct io_uring *)ring, &cqe_ptr);

	if (ret < 0) {
		//php_printf("peek_ret:=%s, %s, %d\n", strerror(-ret), strerror(errno), ret);
		RETURN_LONG(ret); // 返回错误码
	}
	RETURN_LONG((unsigned long)cqe_ptr);
}
/* }}}*/

/* {{{ io_cqe_set_flag( [ int $cqe, int $flag ] ) */
PHP_FUNCTION(io_cqe_set_flag)
{
	int ret;
	unsigned long cqe;
	unsigned long flag;
	struct io_uring_cqe *cqe_ptr;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(cqe)
		Z_PARAM_LONG(flag)
	ZEND_PARSE_PARAMETERS_END();

	if (cqe != 0) {
		cqe_ptr = (struct io_uring_cqe *)cqe;
	}
	cqe_ptr->flags |= flag;

	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_cqe_get_data( [ int $cqe, int $type ] ) */
PHP_FUNCTION(io_uring_cqe_get_data)
{
	unsigned long cqe;
	unsigned long io_type;

	ZEND_PARSE_PARAMETERS_START(1, 2)
		Z_PARAM_LONG(cqe)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(io_type)
	ZEND_PARSE_PARAMETERS_END();

	//php_printf("io_uring_cqe_get_data:=%d, %d, %d", io_type, IO_TYPE_SOCKET, IO_TYPE_DISK);
	if (io_type == IO_TYPE_SOCKET) {
		struct ConnInfo *data = (struct ConnInfo *)io_uring_cqe_get_data((struct io_uring_cqe *)cqe);
		if (!data) {
			RETURN_LONG(-1);
		}
		RETURN_LONG((unsigned long)data);
	} else {
		struct io_data *data = io_uring_cqe_get_data((struct io_uring_cqe *)cqe);
		//php_printf("cqe:=%lu, cqe_res:=%d, data:=%lu, data:=%s\n", (unsigned long)tmp, tmp->res, data+1, data+1);
		RETURN_LONG((unsigned long)data);
	}
}
/* }}}*/

/* {{{ io_uring_queue_exit( [ int $ring ] ) */
PHP_FUNCTION(io_uring_queue_exit)
{
	unsigned long ring = 0;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(ring)
	ZEND_PARSE_PARAMETERS_END();

	io_uring_queue_exit((struct io_uring *)ring);
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_get_cqe_res( [ int $cqe ] ) */
PHP_FUNCTION(io_get_cqe_res)
{
	unsigned long cqe;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(cqe)
	ZEND_PARSE_PARAMETERS_END();
	struct io_uring_cqe *cqe_ptr = (struct io_uring_cqe *)cqe;
	//php_printf("io_get_cqe_res, cqe=%lu, res=%d\n", cqe, cqe_ptr->res);

	RETURN_LONG((long)cqe_ptr->res);
}
/* }}}*/

/* {{{ io_init_params( [ ] ) */
PHP_FUNCTION(io_init_params)
{
	struct io_uring_params *params = malloc(sizeof(struct io_uring_params));
	memset(params, 0, sizeof (struct io_uring_params));

	RETURN_LONG((unsigned long)params);
}
/* }}}*/

/* {{{ io_setup_params( [ int $params, string $attr, int $val ] ) */
PHP_FUNCTION(io_setup_params)
{
	unsigned long params;
	char *attr;
	size_t attr_len;
	unsigned long val;
	int ret;

	ZEND_PARSE_PARAMETERS_START(3, 3)
		Z_PARAM_LONG(params)
		Z_PARAM_STRING(attr, attr_len)
		Z_PARAM_LONG(val)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_params *ptr_param = (struct io_uring_params *)params;
	if (strcmp(attr, "sq_entries") == 0) {
		ptr_param->sq_entries = (__u32)val;
	}
	if (strcmp(attr, "cq_entries") == 0) {
		ptr_param->cq_entries = (__u32)val;
	}
	if (strcmp(attr, "flags") == 0) {
		ptr_param->flags = (__u32)val;
	}
	if (strcmp(attr, "sq_thread_cpu") == 0) {
		ptr_param->sq_thread_cpu = (__u32)val;
	}
	if (strcmp(attr, "sq_thread_idle") == 0) {
		ptr_param->sq_thread_idle = (__u32)val;
	}
	if (strcmp(attr, "features") == 0) {
		ptr_param->features = (__u32)val;
	}
	if (strcmp(attr, "wq_fd") == 0) {
		ptr_param->wq_fd = (__u32)val;
	}
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_create_ring( [ ] ) */
PHP_FUNCTION(io_create_ring)
{
	struct io_uring *ring = malloc(sizeof(struct io_uring));
	memset(ring, 0, sizeof (struct io_uring));

	RETURN_LONG((unsigned long)ring);
}
/* }}}*/

/* {{{ long io_uring_queue_init_params( [ int $depth, int $ring, int $params ] ) */
PHP_FUNCTION(io_uring_queue_init_params)
{
	unsigned long depth = QD;
	unsigned long ring;
	unsigned long params;
	int ret;

	ZEND_PARSE_PARAMETERS_START(3, 3)
		Z_PARAM_LONG(depth)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(params)
	ZEND_PARSE_PARAMETERS_END();

	ret = io_uring_queue_init_params(depth, (struct io_uring *)ring, (struct io_uring_params *)params);
	if (ret < 0) {
		php_printf("io_uring_init_params error: %s\n", strerror(-ret));
	}
	RETURN_LONG(ret);
}
/* }}}*/

/* {{{ long io_create_socket_len( [ ] ) */
PHP_FUNCTION(io_create_socket_len)
{
	socklen_t clilen = sizeof(struct sockaddr);
	RETURN_LONG((unsigned long)&clilen);
}
/* }}}*/

/* {{{ long io_create_socket_addr( [ ] ) */
PHP_FUNCTION(io_create_socket_addr)
{
	struct sockaddr_in *clientaddr = malloc(sizeof(struct sockaddr_in));
	RETURN_LONG((unsigned long)clientaddr);
}
/* }}}*/

/* {{{ long io_free_socket_addr( [ ] ) */
PHP_FUNCTION(io_free_socket_addr)
{
	unsigned long addr;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(addr)
	ZEND_PARSE_PARAMETERS_END();
	struct sockaddr_in *clientAddr = (struct sockaddr_in *)addr;
	free(clientAddr);
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_uring_prep_accept( [ int $sqe, int $fd, int $clientAddr, int clilen, int $flags ] ) */
PHP_FUNCTION(io_uring_prep_accept)
{
	unsigned long sqe;
	unsigned long fd;
	unsigned long clientAddr;
	unsigned long clilen;
	unsigned long flags;
	struct sockaddr_in clientaddr;

	ZEND_PARSE_PARAMETERS_START(5, 5)
		Z_PARAM_LONG(sqe)
		Z_PARAM_LONG(fd)
		Z_PARAM_LONG(clientAddr)
		Z_PARAM_LONG(clilen)
		Z_PARAM_LONG(flags)
	ZEND_PARSE_PARAMETERS_END();

	socklen_t clilen2 = sizeof(struct sockaddr);
	io_uring_prep_accept((struct io_uring_sqe *)sqe, fd, (struct sockaddr *)clientAddr, &clilen2, flags);
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_uring_prep_recv( [ int $sqe, int $fd, int $buffer, int $length, int $flags ] ) */
PHP_FUNCTION(io_uring_prep_recv)
{
	unsigned long sqe;
	unsigned long fd;
	unsigned long buffer;
	unsigned long length;
	unsigned long flags;
	int ret;

	ZEND_PARSE_PARAMETERS_START(5, 5)
		Z_PARAM_LONG(sqe)
		Z_PARAM_LONG(fd)
		Z_PARAM_LONG(buffer)
		Z_PARAM_LONG(length)
		Z_PARAM_LONG(flags)
	ZEND_PARSE_PARAMETERS_END();

	io_uring_prep_recv((struct io_uring_sqe *)sqe, fd, (char *)buffer, length, flags);
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_uring_prep_send( [ int $sqe, int $fd, int $buffer, int $length, int $flags ] ) */
PHP_FUNCTION(io_uring_prep_send)
{
	unsigned long sqe;
	unsigned long fd;
	unsigned long buffer;
	unsigned long length;
	unsigned long flags;
	int ret;

	ZEND_PARSE_PARAMETERS_START(5, 5)
		Z_PARAM_LONG(sqe)
		Z_PARAM_LONG(fd)
		Z_PARAM_LONG(buffer)
		Z_PARAM_LONG(length)
		Z_PARAM_LONG(flags)
	ZEND_PARSE_PARAMETERS_END();

	io_uring_prep_send((struct io_uring_sqe *)sqe, fd, (char *)buffer, length, flags);
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_uring_peek_batch_cqe( [ int $ring, int $cqes, int $cqe_len ] ) */
PHP_FUNCTION(io_uring_peek_batch_cqe)
{
	unsigned long ring;
	unsigned long cqes;
	unsigned long cqe_len;
	int ret;

	ZEND_PARSE_PARAMETERS_START(3, 3)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(cqes)
		Z_PARAM_LONG(cqe_len)
	ZEND_PARSE_PARAMETERS_END();

	ret = io_uring_peek_batch_cqe((struct io_uring *)ring, (struct io_uring_cqe **)cqes, cqe_len);
	RETURN_LONG(ret);
}
/* }}}*/

/* {{{ io_uring_cq_advance( [ int $ring, int $count ] ) */
PHP_FUNCTION(io_uring_cq_advance)
{
	unsigned long ring;
	unsigned long count;
	int ret;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(ring)
		Z_PARAM_LONG(count)
	ZEND_PARSE_PARAMETERS_END();

	io_uring_cq_advance((struct io_uring *)ring, count);
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_get_conn_info( [ ] ) */
PHP_FUNCTION(io_get_conn_info)
{
	struct ConnInfo *info = malloc(sizeof(struct ConnInfo));
	memset(info, 0, sizeof(struct ConnInfo));
	if (!info) {
		RETURN_LONG(-1);
	}
	RETURN_LONG((unsigned long)info);
}
/* }}}*/

/* {{{ io_set_conn_val( [ int $conn, string $key, int $val ] ) */
PHP_FUNCTION(io_set_conn_val)
{
	unsigned long conn;
	char *key;
	size_t key_len;
	unsigned long val;

	ZEND_PARSE_PARAMETERS_START(3, 3)
		Z_PARAM_LONG(conn)
		Z_PARAM_STRING(key, key_len)
		Z_PARAM_LONG(val)
	ZEND_PARSE_PARAMETERS_END();

	struct ConnInfo *info_ptr = (struct ConnInfo *)conn;
	if (info_ptr == NULL)
		RETURN_NULL();

	if (strcmp(key, "connfd") == 0) {
		info_ptr->connfd = val;
	}
	if (strcmp(key, "event") == 0) {
		info_ptr->event = val;
	}
	if (strcmp(key, "buffer") == 0) {
		memmove(info_ptr->buffer, (char *)val, strlen((char *)val) + 1);
	}
	if (strcmp(key, "buffer_length") == 0) {
		info_ptr->buffer_length = val;
		info_ptr->buffer[val] = '\0';
	}
	if (strcmp(key, "next") == 0) {
		info_ptr->next = (struct ConnInfo *)val;
	}
	RETURN_NULL();
}
/* }}}*/

/* {{{ long io_get_conn_val( [ int $conn, string $key ] ) */
PHP_FUNCTION(io_get_conn_val)
{
	unsigned long conn;
	char *key;
	size_t key_len;
	unsigned long result = 0;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(conn)
		Z_PARAM_STRING(key, key_len)
	ZEND_PARSE_PARAMETERS_END();

	struct ConnInfo *info_ptr = (struct ConnInfo *)conn;
	if (info_ptr == NULL)
		RETURN_LONG(-1);

	if (strcmp(key, "connfd") == 0) {
		result = info_ptr->connfd;
	}
	if (strcmp(key, "event") == 0) {
		result = info_ptr->event;
	}
	if (strcmp(key, "buffer") == 0) {
		result = (unsigned long)info_ptr->buffer;
	}
	if (strcmp(key, "buffer_length") == 0) {
		result = info_ptr->buffer_length;
	}
	if (strcmp(key, "next") == 0) {
		result = (unsigned long)info_ptr->next;
	}
	RETURN_LONG(result);
}
/* }}}*/

/* {{{ long io_generate_cqes( [ int $len ] ) */
PHP_FUNCTION(io_generate_cqes)
{
	int i;
	unsigned long len;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(len)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_cqe **cqes = malloc(sizeof(struct io_uring_cqe *) * len);

	if (cqes == NULL) {
		RETURN_LONG(-1);
	}
	memset(cqes, 0, sizeof(struct io_uring_cqe *) * len);

	RETURN_LONG((unsigned long)cqes);
}
/* }}}*/

/* {{{ long io_get_cqe_by_index( [ int $cqes, int $index ] ) */
PHP_FUNCTION(io_get_cqe_by_index)
{
	unsigned long cqes;
	unsigned long index;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(cqes)
		Z_PARAM_LONG(index)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_cqe **cqe_array = (struct io_uring_cqe **)cqes;
	if (!cqe_array) {
		RETURN_LONG(-1);
	}

	struct io_uring_cqe *cqe = cqe_array[index];
	if (!cqe) {
		RETURN_LONG(-2);
	}
	RETURN_LONG((unsigned long)cqe);
	//RETURN_LONG((unsigned long)cqe_array[index]);
}
/* }}}*/

/* {{{ io_free_conn( [ int $conn ] ) */
PHP_FUNCTION(io_free_conn)
{
	unsigned long conn;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(conn)
	ZEND_PARSE_PARAMETERS_END();

	struct ConnInfo *data = (struct ConnInfo *)conn;

	if (data != NULL) {
		free(data);
		data = NULL;
	}

	RETURN_NULL();
}
/* }}}*/

/* {{{ io_close_fd( [ int $fd ] ) */
PHP_FUNCTION(io_close_fd)
{
	unsigned long fd;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(fd)
	ZEND_PARSE_PARAMETERS_END();

	if (close(fd) == -1) {
		php_printf("close fd error!\n");
	}

	RETURN_NULL();
}
/* }}}*/

/* {{{ io_free_cqes( [ int $cqes ] ) */
PHP_FUNCTION(io_free_cqes)
{
	unsigned long cqes;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(cqes)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_cqe **cqe_array = (struct io_uring_cqe **)cqes;
	free(cqe_array);
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_free_params( [ int $params ] ) */
PHP_FUNCTION(io_free_params)
{
	unsigned long params;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(params)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring_params *params_ptr = (struct io_uring_params *)params;
	free(params_ptr);
	RETURN_NULL();
}
/* }}}*/

/* {{{ io_free_ring( [ int $ring ] ) */
PHP_FUNCTION(io_free_ring)
{
	unsigned long ring;
	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_LONG(ring)
	ZEND_PARSE_PARAMETERS_END();

	struct io_uring *ring_ptr = (struct io_uring *)ring;
	free(ring_ptr);
	RETURN_NULL();
}
/* }}}*/

/* {{{ string io_buffer2Str( [ int $buffer, int $len ] ) */
PHP_FUNCTION(io_buffer2Str)
{
	unsigned long buffer;
	unsigned long len;
	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(buffer)
		Z_PARAM_LONG(len)
	ZEND_PARSE_PARAMETERS_END();

	char *buff_str = (char *)buffer;
	size_t buffer_len = strnlen(buff_str, len); // 安全地获取实际的字符串长度

	if (len > buffer_len) {
		len = buffer_len;
	}
	buff_str[len] = '\0';
	zend_string *str = zend_string_init(buff_str, len, 0);
	RETURN_STR(str);
}
/* }}}*/

/* {{{ long io_str2Buffer( [ int $buffer, string $str ] ) */
PHP_FUNCTION(io_str2Buffer)
{
	unsigned long buffer;
	char *str;
	size_t str_len;
	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_LONG(buffer)
		Z_PARAM_STRING(str, str_len)
	ZEND_PARSE_PARAMETERS_END();

	char *buff_str = (char *)buffer;
	strncpy(buff_str, str, str_len);
	buff_str[str_len] = '\0';
	RETURN_LONG((unsigned long)buff_str);
}
/* }}}*/

/* {{{ PHP_RINIT_FUNCTION */
PHP_RINIT_FUNCTION(liburing)
{
#if defined(ZTS) && defined(COMPILE_DL_LIBURING)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION */
PHP_MINFO_FUNCTION(liburing)
{
	php_info_print_table_start();
	php_info_print_table_row(2, "liburing support", "enabled");
	php_info_print_table_end();
}
/* }}} */

/* {{{ PHP_MINIT_FUNCTION */
PHP_MINIT_FUNCTION(liburing)
{
	//add_liburing_constants(module_number);
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION */
/*PHP_MSHUTDOWN_FUNCTION(liburing)
{

	return SUCCESS;
}*/
/* }}} */

/* {{{ liburing_module_entry */
zend_module_entry liburing_module_entry = {
	STANDARD_MODULE_HEADER,
	"liburing",					/* Extension name */
	ext_functions,					/* zend_function_entry */
	PHP_MINIT(liburing),			/* PHP_MINIT - Module initialization */
	NULL,							/* PHP_MSHUTDOWN - Module shutdown */
	PHP_RINIT(liburing),			/* PHP_RINIT - Request initialization */
	NULL,							/* PHP_RSHUTDOWN - Request shutdown */
	PHP_MINFO(liburing),			/* PHP_MINFO - Module info */
	PHP_LIBURING_VERSION,		/* Version */
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_LIBURING
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(liburing)
#endif
