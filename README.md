# PHP_liburing

The php_liburing extension provides an API for [io_uring](https://github.com/axboe/liburing). It support disk I/O and network I/O and also give some examples with this extension.


You can send comments, patches, questions [here on github](https://github.com/sunder3344/php_liburing/issues), to sunder3344@gmail.com

# environmenet support
This extension only support php8.*+ and linux 5.10+


# Installing/Configuring
-----

## Installation

~~~
# go into liburing and execute the commands belowd
/your_php_install_file/bin/phpize

./configure --with-php-config=/your_php_install_file/bin/php-config --with-liburing=/usr/include

make && make install

# finally add the new extension "morse.so" into your php.ini file and reload your php-fpm
~~~

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

*ring*: int. pointer of `ring` 

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

*Note*: return value is an pointer of `struct io_data *`

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

*iodata*: the pointer of `struct io_data *`  
*key*: key name in `struct io_data *`, such as `read`, `first_offset`, `offset`, `first_len`, `iov_base`  
*val*: the value of the key

##### *Return value*  
no return value

-----

### io_query_data(self defined)

_**Description**_: query the key value of `struct io_data *`

##### *Parameters*

```
	function io_query_data(int $iodata, string $key): int {}
```

*iodata*: the pointer of `struct io_data *`  
*key*: key name in `struct io_data *`, such as `read`, `first_offset`, `offset`, `first_len`, `iov_base`  

##### *Return value*
*INT*: `>0` on success(if key = 'iov_base', the return value is the pointer of `ptr_t iov_base` in `struct iovec`).

-----

### io_free_data(self defined)

_**Description**_: free the memory of `struct io_data *`.

##### *Parameters*

```
	function io_free_data(int $iodata): void {}
```

*iodata*: the pointer of `struct io_data *` 

##### *Return value*
no return value

-----

### io_uring_prep_timeout

_**Description**_:  prepare a timeout request.

##### *Parameters*

```
	function io_uring_prep_timeout(int $sqe, int $sec, int $nsec, int $count, int $flag): void {}
```

*sqe*: the pointer of `struct io_uring_sqe *`  
*sec*: seconds  
*nsec*: nano seconds  
*count*: a timeout count of count completion entries  
*flag*: The flags argument holds modifier flags for the request.	

##### *Return value*
no return value

-----

### io_uring_prep_readv

_**Description**_:  prepare vector I/O read request.

##### *Parameters*

```
	function io_uring_prep_readv(int $sqe, object $file, int $iodata, int $nr_vecs, int $offset): int {}
```

*sqe*: the pointer of `struct io_uring_sqe *`  
*file*: php file descriptor  
*iodata*: the pointer of `struct io_data *`  
*nr_vecs*: The submission queue entry sqe is setup to use the file descriptor fd to start reading nr_vecs into the iovecs array at the specified offset.  	
*offset*: the specified offset of iovecs array.	

##### *Return value*
*INT*:  This method returns `TRUE` on success, or the passed string if called with an argument.

-----

### io_uring_sqe_set_data

_**Description**_: set user data for submission queue event.

##### *Parameters*

```
	function io_uring_sqe_set_data(int $sqe, int $iodata, int $type): void {};
```

*sqe*: the pointer of `struct io_uring_sqe *`  
*iodata*: the pointer of `struct io_data *`  
*type*: `IO_TYPE_SOCKET` for socket setup, `IO_TYPE_DISK` for disk I/O  

##### *Return value*
no return value
