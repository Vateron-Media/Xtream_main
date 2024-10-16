/*
  +----------------------------------------------------------------------+
  | PHP Version 7                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Andrey Hristov <andrey@php.net>                             |
  |          Ulf Wendel <uw@php.net>                                     |
  +----------------------------------------------------------------------+
*/

#ifndef MYSQLND_VIO_H
#define MYSQLND_VIO_H

PHPAPI MYSQLND_VIO *mysqlnd_vio_init(zend_bool persistent, MYSQLND_CLASS_METHODS_TYPE(mysqlnd_object_factory) * object_factory, MYSQLND_STATS *stats, MYSQLND_ERROR_INFO *error_info);
PHPAPI void mysqlnd_vio_free(MYSQLND_VIO *const vio, MYSQLND_STATS *stats, MYSQLND_ERROR_INFO *error_info);

#endif /* MYSQLND_VIO_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
