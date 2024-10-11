/* liburing extension for PHP */

#ifndef PHP_LIBURING_H
# define PHP_LIBURING_H

extern zend_module_entry liburing_module_entry;
# define phpext_liburing_ptr &liburing_module_entry

# define PHP_LIBURING_VERSION "0.1.0"

# if defined(ZTS) && defined(COMPILE_DL_LIBURING)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

#endif	/* PHP_LIBURING_H */
