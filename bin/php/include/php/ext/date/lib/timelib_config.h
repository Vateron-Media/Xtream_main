#ifdef PHP_WIN32
#include "config.w32.h"
#else
#include <php_config.h>
#endif
#include <inttypes.h>
#include <stdint.h>

#include "zend.h"

#define timelib_malloc emalloc
#define timelib_realloc erealloc
#define timelib_calloc ecalloc
#define timelib_strdup estrdup
#define timelib_strndup estrndup
#define timelib_free efree
