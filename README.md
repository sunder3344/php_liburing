# PHP_liburing

The php_liburing extension provides an API for [io_uring](https://github.com/axboe/liburing). It support disk I/O and network I/O and also give some examples with this extension.


You can send comments, patches, questions [here on github](https://github.com/sunder3344/php_liburing/issues), to sunder3344@gmail.com

# environment support
This extension only support php8.*+ and Linux 5.10+


# Installing/Configuring
-----

## Installation

~~~
# go into liburing and execute the commands below
/your_php_install_file/bin/phpize

./configure --with-php-config=/your_php_install_file/bin/php-config --with-liburing=/usr/include

make && make install

# finally add the new extension "liburing.so" into your php.ini file and reload your php-fpm
~~~

# Demo
-----
## description

[io_uring-cp.php](./demo/io_uring-cp.php): the php version of the [io_uring official demo](https://github.com/axboe/liburing/blob/master/examples/io_uring-cp.c)

[link-cp.php](./demo/link-cp.php): the php version of the [io_uring official demo](https://github.com/axboe/liburing/blob/master/examples/link-cp.c)

[socket_uring_server.php](./demo/socket_uring_server.php): a demo for socket server

[socket_uring_server_pool.php](./demo/socket_uring_server_pool.php): a simple demo for socket server with memory pool(not very stable, should be improved later)

[socket_uring_web.php](./demo/socket_uring_server.php): a simple demo for web server


in order to use asynchronous I/O in easy way, this [fast_copy](https://github.com/sunder3344/fast_copy) extension can give you a new choice.

# API and methods
-----

### io_uring_queue_init

_**Description**_: setup io_uring submission and completion queues

##### *Parameters*

```
	function io_uring_queue_init(int $depth, int $flag = 0): int {}
```

*depth*: int, entries in the submission queue  
*flag*: int, will be passed through to the io_uring_setup syscall  

##### *Return value*

*INT*: `>0` on success(pointer of `ring`), `<=0`  on error~

**Note:** this function will give you an pointer of `struct io_uring *`.

-----

### io_uring_get_sqe

_**Description**_: get the next available submission queue entry from the submission queue.

##### *Parameters*
```
	function io_uring_get_sqe(int $ring): int {}
```

*ring*: int. pointer of `struct io_uring *` 

##### *Return value*

*INT*: `>0` on success(pointer of `sqe`), `<=` on error.

**Note:** return value is an pointer of `struct io_uring *`.

-----

### io_init_data(self defined)

_**Description**_: return an pointer of `struct io_data *`.

##### *Parameters*
```
	function io_init_data(): int {}
```

##### *Return value*
*INT*: `>0` on success(pointer of `struct io_data *`).

*Note*: return value is an pointer of `struct io_data *`, the structure of `struct io_data` is 

```
	struct io_data {
		int read;
		off_t first_offset, offset;
		size_t first_len;
		struct iovec iov;
	};
```

-----

### io_set_data(self defined)

_**Description**_: set the value of `struct io_data *`

##### *Parameters*

```
	function io_set_data(string $content = "", int $size): int {}
```

*content*: string, io content  
*size*: int, content length  

##### *Return value*
`INT`: `>0` on success(pointer of `struct io_data *`).

*Note*: return value is an pointer of `struct io_data *`

-----

### io_update_data(self defined)

_**Description**_:  update the value of `struct io_data *`  

##### *Parameters*  

```
	function io_update_data(int $iodata, string $key, int $val): void {}
```

*iodata*: long, the pointer of `struct io_data *`  
*key*: string, key name in `struct io_data *`, such as `read`, `first_offset`, `offset`, `first_len`, `iov_base`  
*val*: int, the value of the key

##### *Return value*  
no return value

-----

### io_query_data(self defined)

_**Description**_: query the key value of `struct io_data *`

##### *Parameters*

```
	function io_query_data(int $iodata, string $key): int {}
```

*iodata*: long, the pointer of `struct io_data *`  
*key*: string, key name in `struct io_data *`, such as `read`, `first_offset`, `offset`, `first_len`, `iov_base`  

##### *Return value*
*INT*: `>0` on success(if key = 'iov_base', the return value is the pointer of `ptr_t iov_base` in `struct iovec`).

-----

### io_free_data(self defined)

_**Description**_: free the memory of `struct io_data *` which you created by function `io_set_data`

##### *Parameters*

```
	function io_free_data(int $iodata): void {}
```

*iodata*: long, the pointer of `struct io_data *` 

##### *Return value*
no return value

-----

### io_uring_prep_timeout

_**Description**_:  prepare a timeout request.

##### *Parameters*

```
	function io_uring_prep_timeout(int $sqe, int $sec, int $nsec, int $count, int $flag): void {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*sec*: int, seconds  
*nsec*: int, nano seconds  
*count*: int, a timeout count of count completion entries  
*flag*: int, The flag argument holds modifier flags for the request.	

##### *Return value*
no return value

-----

### io_uring_prep_readv

_**Description**_:  prepare vector I/O read request.

##### *Parameters*

```
	function io_uring_prep_readv(int $sqe, object $file, int $iodata, int $nr_vecs, int $offset): int {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*file*: obj, php file descriptor  
*iodata*: long, the pointer of `struct io_data *`  
*nr_vecs*: long, The submission queue entry sqe is setup to use the file descriptor fd to start reading nr_vecs into the iovecs array at the specified offset.  	
*offset*: long, the specified offset of iovecs array.	

##### *Return value*
no return value

-----

### io_uring_sqe_set_data

_**Description**_: set user data for submission queue event.

##### *Parameters*

```
	function io_uring_sqe_set_data(int $sqe, int $iodata, int $type): void {};
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*iodata*: long, the pointer of `struct io_data *`  
*type*: int, only support 2 types: `IO_TYPE_SOCKET` for socket setup, `IO_TYPE_DISK` for disk I/O  

##### *Return value*
no return value

-----

### io_uring_prep_writev

_**Description**_: prepare vector I/O write request.

##### *Parameters*

```
	function io_uring_prep_writev(int $sqe, object $file, int $iodata, int $nr_vecs, int $offset): int {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*file*: int, php file descriptor  
*iodata*: long, he pointer of `struct io_data *`  
*nr_vecs*: long, The submission queue entry sqe is setup to use the file descriptor fd to start writing nr_vecs from the iovecs array at the specified offset.  	
*offset*: long, the specified offset of iovecs array.	

##### *Return value*
no return value

-----

### io_uring_submit

_**Description**_: submit requests to the submission queue.

##### *Parameters*

```
	function io_uring_submit(int $ring): int {}
```

*ring*: long, the pointer of `struct io_uring *`  

##### *Return value*
*INT*: `>=0` on success, `<0` on error

-----

### io_generate_cqe(self defined)

_**Description**_: create pointer of `struct io_uring_cqe *`.

##### *Parameters*

```
	function io_generate_cqe() : int {}
```

##### *Return value*
*INT*: `>=0` on success(pointer of `struct io_uring_cqe *`)

-----

### io_uring_wait_cqe

_**Description**_: wait for one io_uring completion event.

##### *Parameters*

```
	function io_uring_wait_cqe(int $ring, int $cqe) : int {}
```

*ring*: long, the pointer of `struct io_uring *`  
*cqe*: long, the pointer of `struct io_uring_cqe *`  

##### *Return value*
*INT*: `>0` on success(it will return new pointer of `struct io_uring_cqe *`, you should replace the original `struct io_uring_cqe *` with this one), `<=0` on error

-----

### io_uring_peek_cqe

_**Description**_: check if an io_uring completion event is available.

##### *Parameters*

```
	function io_uring_peek_cqe(int $ring, int $cqe) : int {}
```

*ring*: long, the pointer of `struct io_uring *`  
*cqe*: long, the pointer of `struct io_uring_cqe *`  

##### *Return value*
*INT*: `>0` on success(it will return new pointer of `struct io_uring_cqe *`, you should replace the original `struct io_uring_cqe *` with this one), `<=0` on error

-----

### io_cqe_set_flag(self defined)

_**Description**_: set the `flags` of `struct io_uring_cqe *`

##### *Parameters*

```
	function io_cqe_set_flag(int $cqe, int $flag): void {}
```

*cqe*: long, the pointer of `struct io_uring_cqe *`  
*flag*: int, the flag value, such as `IORING_CQE_F_BUFFER`(for all the flags please look at the [io_uring](https://github.com/axboe/liburing) offical doc)  

##### *Return value*
no return value

##### *example*

```
	$cqe = io_uring_get_cqe($ring);
	io_cqe_set_flag($cqe, IORING_CQE_F_BUFFER);
```

-----

### io_uring_cqe_seen

_**Description**_: mark io_uring completion event as consumed.

##### *Parameters*

```
	function io_uring_cqe_seen(int $ring, int $cqe) : int {}
```

*ring*: long, the pointer of `struct io_uring *`  
*cqe*: long, the pointer of `struct io_uring_cqe *`  

##### *Return value*
*INT*: `>0` on success(it will return pointer of `struct io_uring_cqe *`)

-----

### io_uring_cqe_get_data

_**Description**_: get user data for completion event.

##### *Parameters*

```
	function io_uring_cqe_get_data(int $cqe, int $type) : int {}
```

*cqe*: long, the pointer of `struct io_uring_cqe *`  
*type*: int, only support 2 types: `IO_TYPE_SOCKET` for socket setup, `IO_TYPE_DISK` for disk I/O    

##### *Return value*
*INT*: `>0` on success(it will return pointer of `struct io_data *`), `<=0` on error

-----

### io_get_cqe_res

_**Description**_: get the `res` value of `struct io_uring_cqe *`

##### *Parameters*

```
	function io_get_cqe_res(int $cqe): int {}
```

*cqe*: long, the pointer of `struct io_uring_cqe *`  

##### *Return value*
*INT*: return the `res` of `struct io_uring_cqe *`

-----

### io_free_cqe

_**Description**_: free the pointer of `struct io_uring_cqe *` which you created by function `io_generate_cqe`

##### *Parameters*

```
	function io_free_cqe(int $cqe): void {}
```

*cqe*: long, the pointer of `struct io_uring_cqe *`  

##### *Return value*
no return value 

-----

### io_sqe_set_flag

_**Description**_: set the `flags` of `struct io_uring_sqe *`

##### *Parameters*

```
	function io_sqe_set_flag(int $sqe, int $flag): void {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*flag*: int, the flag value, such as `IOSQE_IO_LINK`(for all the flags please look at the [io_uring](https://github.com/axboe/liburing) offical doc)    

##### *Return value*
no return value 

##### *example*

```
	$sqe = io_uring_get_sqe($ring);
	io_sqe_set_flag($sqe, IOSQE_IO_LINK);
```

-----

### io_uring_queue_exit

_**Description**_: tear down io_uring submission and completion queues.

##### *Parameters*

```
	function io_uring_queue_exit(int $ring): void {}
```

*ring*: long, the pointer of `struct io_uring *`  

##### *Return value*
no return value 

-----

### io_init_params(self defined)

_**Description**_: create the pointer of `struct io_uring_params *`

##### *Parameters*

```
	function io_init_params(): int {}
```

##### *Return value*
*INT*: return the pointer of `struct io_uring_params *`

-----

### io_setup_params(self defined)

_**Description**_: setup the attribute of `struct io_uring_params *`(`sq_entries`, `cq_entries`, `flags`, `sq_thread_cpu`, `sq_thread_idle`, `features`, `wq_fd`)

##### *Parameters*

```
	function io_setup_params(int $params, string $attr, int $val): void {}
```

*params*: long, the pointer of `struct io_uring_params *`  
*attr*: string, attribute name(`sq_entries`, `cq_entries`, `flags`, `sq_thread_cpu`, `sq_thread_idle`, `features`, `wq_fd`)  
*val*: int, the attribute value

##### *Return value*
no return value 

-----

### io_create_ring(self defined)

_**Description**_: create pointer of `struct io_uring *`  

##### *Parameters*

```
	function io_create_ring(): int {}
```

##### *Return value*
*INT*: return the pointer of `struct io_uring *` 

-----

### io_uring_queue_init_params

_**Description**_: setup io_uring submission and completion queues

##### *Parameters*

```
	function io_uring_queue_init_params(int $depth, int $ring, int $params): int {}
```

*depth*: int, entries in the submission queue  
*ring*: long, the pointer of `struct io_uring *`  
*params*: long, the pointer of `struct io_uring_params *`  

##### *Return value*
*INT*: `>0` on success, `<=0` on error 

-----

### io_create_socket_len(self defined)

_**Description**_: create pointer of `socklen_t *`(only for socket scene)

##### *Parameters*

```
	function io_create_socket_len(): int {}
```

##### *Return value*
*INT*: `>0` on success(return the pointer of `socklen_t *`)

-----

### io_free_socket_len(self defined)

_**Description**_: free the pointer of `socklen_t *`(only for socket scene) which you created by function `io_create_socket_len`

##### *Parameters*

```
	function io_free_socket_len(int $len): void {}
```

*depth*: long, the pointer of `socklen_t *`  

##### *Return value*
no return value

-----

### io_create_socket_addr(self defined)

_**Description**_: create the pointer of `struct sockaddr_in *`(only for socket scene)

##### *Parameters*

```
	function io_create_socket_addr(): int {}
```

##### *Return value*
*INT*: `>0` on success(return the pointer of `sockaddr_in *`)

-----

### io_free_socket_addr(self defined)

_**Description**_: free the pointer of `sockaddr_in *`(only for socket scene) which you created by function `io_create_socket_addr`

##### *Parameters*

```
	function io_free_socket_addr(int $addr): void {}
```

*addr*: long, the pointer of `sockaddr_in *`  

##### *Return value*
no return value

-----

### io_uring_prep_accept

_**Description**_: prepare an accept request.

##### *Parameters*

```
	function io_uring_prep_accept(int $sqe, int $fd, int $clientAddr, int $clilen, int $flags): void {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*fd*: long, file discriptor(you should convert the php fd resource to int)  
*clientAddr*: long, the pointer of `struct sockaddr_in *`  
*clilen*: long, the pointer of `struct socklen_t *` 
*flags*: long, the flags argument holds modifier flags for the request.

##### *Return value*
no return value

-----

### io_uring_prep_recv

_**Description**_: prepare a recv request.

##### *Parameters*

```
	function io_uring_prep_recv(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*fd*: long, file discriptor(you should convert the php fd resource to int)  
*buffer*: long, the pointer of `char *`  
*length*: long, the length of the string buffer 
*flags*: long, the flags argument holds modifier flags for the request.

##### *Return value*
no return value

-----

### io_uring_prep_send

_**Description**_: prepare a send request.

##### *Parameters*

```
	function io_uring_prep_send(int $sqe, int $fd, int $buffer, int $length, int $flags): void {}
```

*sqe*: long, the pointer of `struct io_uring_sqe *`  
*fd*: long, file discriptor(you should convert the php fd resource to int)  
*buffer*: long, the pointer of `char *`  
*length*: long, the length of the string buffer 
*flags*: long, the flags argument holds modifier flags for the request.

##### *Return value*
no return value

-----

### io_uring_peek_batch_cqe

_**Description**_: check if some io_uring completion events are available.

##### *Parameters*

```
	function io_uring_peek_batch_cqe(int $ring, int $cqes, int $cqe_len): int {}
```

*ring*: long, the pointer of `struct io_uring *`  
*cqes*: long, the pointer of `struct io_uring_cqe **`  
*cqe_len*: int, the queue number of cqes  

##### *Return value*
*INT*: `>=0` on success(return the successful available number of cqes)

-----

### io_uring_cq_advance

_**Description**_: mark one or more io_uring completion events as consumed.

##### *Parameters*

```
	function io_uring_cq_advance(int $ring, int $count): void {}
```

*ring*: long, the pointer of `struct io_uring *`  
*count*: long, mark the IO completions belonging to the ring as consumed.  

##### *Return value*
no return value

-----

### io_get_conn_info(self defined)

_**Description**_: return the pointer of `struct ConnInfo *`

##### *Parameters*

```
	function io_get_conn_info(): int {}
```

##### *Return value*
*INT*: `>=0` on success(return the pointer of `struct ConnInfo *`)

*Note*: the structure of `struct ConnInfo` is

```
	struct ConnInfo {
		int connfd;
		int event;
		char buffer[MAXLINE];
		size_t buffer_length;
		struct ConnInfo *next;
	} __attribute__((aligned(64)));
```


-----

### io_set_conn_val(self defined)

_**Description**_: setup the value of `struct ConnInfo *`

##### *Parameters*

```
	function io_set_conn_val(int $conn, string $key, int $val): void {}
```

*conn*: long, the pointer of `struct ConnInfo *`  
*key*: long, the attribute of `struct ConnInfo *`(`connfd`, `event`, `buffer`, `buffer_length`)  
*val*: long, the value of the key(for `buffer`, you should convert string to `char *`, use function `io_str2Buffer` to do it).  

##### *Return value*
no return value

-----

### io_get_conn_val(self defined)

_**Description**_: get the value of `struct ConnInfo *`

##### *Parameters*

```
	function io_get_conn_val(int $conn, string $key): int {}
```

*conn*: long, the pointer of `struct ConnInfo *`  
*key*: long, the attribute of `struct ConnInfo *`(`connfd`, `event`, `buffer`, `buffer_length`)  

##### *Return value*
*INT*: return int value

-----

### io_generate_cqes(self defined)

_**Description**_: return the pointer of `struct io_uring_cqe **`

##### *Parameters*

```
	function io_generate_cqes(int $len): int {}
```

*len*: int, the number of cqe array  

##### *Return value*
*INT*: return the pointer of `struct io_uring_cqe **`

-----

### io_get_cqe_by_index(self defined)

_**Description**_: return the specified index value in cqes array(`struct io_uring_cqe *`)

##### *Parameters*

```
	function io_get_cqe_by_index(int $cqes, int $index): int {}
```

*cqes*: long, the pointer of `struct io_uring_cqe **`  
*index*: long, the index of cqes array

##### *Return value*
*INT*: return the pointer of `struct io_uring_cqe *`

-----

### io_free_conn(self defined)

_**Description**_: free the pointer of `struct ConnInfo *` which you created by function `io_get_conn_info`

##### *Parameters*

```
	function io_free_conn(int $conn): void {}
```

*conn*: long, the pointer of `struct ConnInfo *`  

##### *Return value*
no return value

-----

### io_close_fd(self defined)

_**Description**_: close the socket fd which you created  

##### *Parameters*

```
	function io_close_fd(int $fd): void {}
```

*fd*: long, file descriptor in linux  

##### *Return value*
no return value

-----

### io_free_cqes(self defined)

_**Description**_: free the pointer of `struct io_uring_cqe **` which you created by function `io_generate_cqes`

##### *Parameters*

```
	function io_free_cqes(int $cqes): void {}
```

*cqes*: long, pointer of `struct io_uring_cqe **`  

##### *Return value*
no return value

-----

### io_free_params(self defined)

_**Description**_: free the pointer of `struct io_uring_params *` which you created by function `io_init_params`  

##### *Parameters*

```
	function io_free_params(int $params): void {}
```

*params*: long, pointer of `struct io_uring_params *`  

##### *Return value*

-----

### io_free_ring(self defined)

_**Description**_: free the pointer of `struct io_uring *` which you created by function `io_create_ring`  

##### *Parameters*

```
	function io_free_ring(int $ring): void {}
```

*ring*: long, pointer of `struct io_uring *`  

##### *Return value*
no return value

-----

### io_buffer2Str(self defined)

_**Description**_: convert pointer `char *` to php string type.  

##### *Parameters*

```
	function io_buffer2Str(int $buffer, int $len): string {}
```

*buffer*: long, pointer of `struct char *`  
*len*: long, string buffer length  

##### *Return value*
*STRING*: return the php string type

-----

### io_str2Buffer(self defined)

_**Description**_: convert php string type to pointer `char *`  

##### *Parameters*

```
	function io_str2Buffer(int $buffer, string $str): int {}
```

*buffer*: long, pointer of `struct char *`  
*str*: string, php string whatever you want  

##### *Return value*
*INT*: return the pointer of `char *`