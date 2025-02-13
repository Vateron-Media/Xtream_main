/* main/php_config.h.  Generated from php_config.h.in by configure.  */
/* main/php_config.h.in.  Generated from configure.ac by autoheader.  */

#ifndef PHP_CONFIG_H
#define PHP_CONFIG_H

#if defined(__GNUC__) && __GNUC__ >= 4
#define ZEND_API __attribute__((visibility("default")))
#define ZEND_DLEXPORT __attribute__((visibility("default")))
#else
#define ZEND_API
#define ZEND_DLEXPORT
#endif

#define ZEND_DLIMPORT

/* The cdb handler header file. */
/* #undef CDB_INCLUDE_FILE */

/* Define to 1 if the PHP extension 'bcmath' is built as a dynamic module. */
/* #undef COMPILE_DL_BCMATH */

/* Define to 1 if the PHP extension 'bz2' is built as a dynamic module. */
/* #undef COMPILE_DL_BZ2 */

/* Define to 1 if the PHP extension 'calendar' is built as a dynamic module.
 */
/* #undef COMPILE_DL_CALENDAR */

/* Define to 1 if the PHP extension 'ctype' is built as a dynamic module. */
/* #undef COMPILE_DL_CTYPE */

/* Define to 1 if the PHP extension 'curl' is built as a dynamic module. */
/* #undef COMPILE_DL_CURL */

/* Define to 1 if the PHP extension 'date' is built as a dynamic module. */
/* #undef COMPILE_DL_DATE */

/* Define to 1 if the PHP extension 'dba' is built as a dynamic module. */
/* #undef COMPILE_DL_DBA */

/* Define to 1 if the PHP extension 'dl_test' is built as a dynamic module. */
/* #undef COMPILE_DL_DL_TEST */

/* Define to 1 if the PHP extension 'dom' is built as a dynamic module. */
/* #undef COMPILE_DL_DOM */

/* Define to 1 if the PHP extension 'enchant' is built as a dynamic module. */
/* #undef COMPILE_DL_ENCHANT */

/* Define to 1 if the PHP extension 'exif' is built as a dynamic module. */
/* #undef COMPILE_DL_EXIF */

/* Define to 1 if the PHP extension 'ffi' is built as a dynamic module. */
/* #undef COMPILE_DL_FFI */

/* Define to 1 if the PHP extension 'fileinfo' is built as a dynamic module.
 */
/* #undef COMPILE_DL_FILEINFO */

/* Define to 1 if the PHP extension 'filter' is built as a dynamic module. */
/* #undef COMPILE_DL_FILTER */

/* Define to 1 if the PHP extension 'ftp' is built as a dynamic module. */
/* #undef COMPILE_DL_FTP */

/* Define to 1 if the PHP extension 'gd' is built as a dynamic module. */
/* #undef COMPILE_DL_GD */

/* Define to 1 if the PHP extension 'gettext' is built as a dynamic module. */
/* #undef COMPILE_DL_GETTEXT */

/* Define to 1 if the PHP extension 'gmp' is built as a dynamic module. */
/* #undef COMPILE_DL_GMP */

/* Define to 1 if the PHP extension 'hash' is built as a dynamic module. */
/* #undef COMPILE_DL_HASH */

/* Define to 1 if the PHP extension 'iconv' is built as a dynamic module. */
/* #undef COMPILE_DL_ICONV */

/* Define to 1 if the PHP extension 'intl' is built as a dynamic module. */
/* #undef COMPILE_DL_INTL */

/* Define to 1 if the PHP extension 'json' is built as a dynamic module. */
/* #undef COMPILE_DL_JSON */

/* Define to 1 if the PHP extension 'ldap' is built as a dynamic module. */
/* #undef COMPILE_DL_LDAP */

/* Define to 1 if the PHP extension 'libxml' is built as a dynamic module. */
/* #undef COMPILE_DL_LIBXML */

/* Define to 1 if the PHP extension 'mbstring' is built as a dynamic module.
 */
/* #undef COMPILE_DL_MBSTRING */

/* Define to 1 if the PHP extension 'mysqli' is built as a dynamic module. */
/* #undef COMPILE_DL_MYSQLI */

/* Define to 1 if the PHP extension 'mysqlnd' is built as a dynamic module. */
/* #undef COMPILE_DL_MYSQLND */

/* Define to 1 if the PHP extension 'odbc' is built as a dynamic module. */
/* #undef COMPILE_DL_ODBC */

/* Define to 1 if the PHP extension 'opcache' is built as a dynamic module. */
#define COMPILE_DL_OPCACHE 1

/* Define to 1 if the PHP extension 'openssl' is built as a dynamic module. */
/* #undef COMPILE_DL_OPENSSL */

/* Define to 1 if the PHP extension 'pcntl' is built as a dynamic module. */
/* #undef COMPILE_DL_PCNTL */

/* Define to 1 if the PHP extension 'pcre' is built as a dynamic module. */
/* #undef COMPILE_DL_PCRE */

/* Define to 1 if the PHP extension 'pdo' is built as a dynamic module. */
/* #undef COMPILE_DL_PDO */

/* Define to 1 if the PHP extension 'pdo_dblib' is built as a dynamic module.
 */
/* #undef COMPILE_DL_PDO_DBLIB */

/* Define to 1 if the PHP extension 'pdo_firebird' is built as a dynamic
   module. */
/* #undef COMPILE_DL_PDO_FIREBIRD */

/* Define to 1 if the PHP extension 'pdo_mysql' is built as a dynamic module.
 */
/* #undef COMPILE_DL_PDO_MYSQL */

/* Define to 1 if the PHP extension 'pdo_odbc' is built as a dynamic module.
 */
/* #undef COMPILE_DL_PDO_ODBC */

/* Define to 1 if the PHP extension 'pdo_pgsql' is built as a dynamic module.
 */
/* #undef COMPILE_DL_PDO_PGSQL */

/* Define to 1 if the PHP extension 'pdo_sqlite' is built as a dynamic module.
 */
/* #undef COMPILE_DL_PDO_SQLITE */

/* Define to 1 if the PHP extension 'pgsql' is built as a dynamic module. */
/* #undef COMPILE_DL_PGSQL */

/* Define to 1 if the PHP extension 'phar' is built as a dynamic module. */
/* #undef COMPILE_DL_PHAR */

/* Define to 1 if the PHP extension 'posix' is built as a dynamic module. */
/* #undef COMPILE_DL_POSIX */

/* Define to 1 if the PHP extension 'random' is built as a dynamic module. */
/* #undef COMPILE_DL_RANDOM */

/* Define to 1 if the PHP extension 'readline' is built as a dynamic module.
 */
/* #undef COMPILE_DL_READLINE */

/* Define to 1 if the PHP extension 'reflection' is built as a dynamic module.
 */
/* #undef COMPILE_DL_REFLECTION */

/* Define to 1 if the PHP extension 'session' is built as a dynamic module. */
/* #undef COMPILE_DL_SESSION */

/* Define to 1 if the PHP extension 'shmop' is built as a dynamic module. */
/* #undef COMPILE_DL_SHMOP */

/* Define to 1 if the PHP extension 'simplexml' is built as a dynamic module.
 */
/* #undef COMPILE_DL_SIMPLEXML */

/* Define to 1 if the PHP extension 'snmp' is built as a dynamic module. */
/* #undef COMPILE_DL_SNMP */

/* Define to 1 if the PHP extension 'soap' is built as a dynamic module. */
/* #undef COMPILE_DL_SOAP */

/* Define to 1 if the PHP extension 'sockets' is built as a dynamic module. */
/* #undef COMPILE_DL_SOCKETS */

/* Define to 1 if the PHP extension 'sodium' is built as a dynamic module. */
/* #undef COMPILE_DL_SODIUM */

/* Define to 1 if the PHP extension 'spl' is built as a dynamic module. */
/* #undef COMPILE_DL_SPL */

/* Define to 1 if the PHP extension 'sqlite3' is built as a dynamic module. */
/* #undef COMPILE_DL_SQLITE3 */

/* Define to 1 if the PHP extension 'standard' is built as a dynamic module.
 */
/* #undef COMPILE_DL_STANDARD */

/* Define to 1 if the PHP extension 'sysvmsg' is built as a dynamic module. */
/* #undef COMPILE_DL_SYSVMSG */

/* Define to 1 if the PHP extension 'sysvsem' is built as a dynamic module. */
/* #undef COMPILE_DL_SYSVSEM */

/* Define to 1 if the PHP extension 'sysvshm' is built as a dynamic module. */
/* #undef COMPILE_DL_SYSVSHM */

/* Define to 1 if the PHP extension 'tidy' is built as a dynamic module. */
/* #undef COMPILE_DL_TIDY */

/* Define to 1 if the PHP extension 'tokenizer' is built as a dynamic module.
 */
/* #undef COMPILE_DL_TOKENIZER */

/* Define to 1 if the PHP extension 'xml' is built as a dynamic module. */
/* #undef COMPILE_DL_XML */

/* Define to 1 if the PHP extension 'xmlreader' is built as a dynamic module.
 */
/* #undef COMPILE_DL_XMLREADER */

/* Define to 1 if the PHP extension 'xmlwriter' is built as a dynamic module.
 */
/* #undef COMPILE_DL_XMLWRITER */

/* Define to 1 if the PHP extension 'xsl' is built as a dynamic module. */
/* #undef COMPILE_DL_XSL */

/* Define to 1 if the PHP extension 'zend_test' is built as a dynamic module.
 */
/* #undef COMPILE_DL_ZEND_TEST */

/* Define to 1 if the PHP extension 'zip' is built as a dynamic module. */
/* #undef COMPILE_DL_ZIP */

/* Define to 1 if the PHP extension 'zlib' is built as a dynamic module. */
/* #undef COMPILE_DL_ZLIB */

/* Define to 1 if fopencookie seeker uses off64_t. */
#define COOKIE_SEEKER_USES_OFF64_T 1

/* Define to 1 if crypt_r uses CRYPTD. */
/* #undef CRYPT_R_CRYPTD */

/* Define to 1 if struct crypt_data requires _GNU_SOURCE. */
/* #undef CRYPT_R_GNU_SOURCE */

/* Define to 1 if crypt_r uses struct crypt_data. */
/* #undef CRYPT_R_STRUCT_CRYPT_DATA */

/* Define to 1 if using 'alloca.c'. */
/* #undef C_ALLOCA */

/* The DB1 handler header file. */
/* #undef DB1_INCLUDE_FILE */

/* The DB1 handler version information. */
/* #undef DB1_VERSION */

/* The DB2 handler header file. */
/* #undef DB2_INCLUDE_FILE */

/* The DB3 handler header file. */
/* #undef DB3_INCLUDE_FILE */

/* The DB4 handler header file. */
/* #undef DB4_INCLUDE_FILE */

/* Define to 1 if the dba extension uses the cdb handler. */
/* #undef DBA_CDB */

/* Define to 1 if the dba extension uses the PHP built-in cdb handler. */
/* #undef DBA_CDB_BUILTIN */

/* Define to 1 if the dba extension uses the Berkeley DB version 1 (DB1)
   handler. */
/* #undef DBA_DB1 */

/* Define to 1 if the dba extension uses the Berkeley DB version 2 (DB2)
   handler. */
/* #undef DBA_DB2 */

/* Define to 1 if the dba extension uses the Berkeley DB version 3 (DB3)
   handler. */
/* #undef DBA_DB3 */

/* Define to 1 if the dba extension uses the Berkeley DB version 4 (DB4)
   handler. */
/* #undef DBA_DB4 */

/* Define to 1 if the dba extension uses the DBM handler. */
/* #undef DBA_DBM */

/* Define to 1 if the dba extension uses the bundled flatfile handler. */
/* #undef DBA_FLATFILE */

/* Define to 1 if the dba extension uses the GDBM handler. */
/* #undef DBA_GDBM */

/* Define to 1 if the dba extension uses the bundled inifile handler. */
/* #undef DBA_INIFILE */

/* Define to 1 if the dba extension uses the LMDB handler. */
/* #undef DBA_LMDB */

/* Define to 1 if the dba extension uses the NDBM handler. */
/* #undef DBA_NDBM */

/* Define to 1 if the dba extension uses the QDBM handler. */
/* #undef DBA_QDBM */

/* Define to 1 if the dba extension uses the Tokyo Cabinet handler. */
/* #undef DBA_TCADB */

/* The DBM handler include file. */
/* #undef DBM_INCLUDE_FILE */

/* The DBM handler version information. */
/* #undef DBM_VERSION */

/* Define to string "1" if PHP short open tags '<?' are enabled by default,
   and to string "0" if they are not. */
#define DEFAULT_SHORT_OPEN_TAG "1"

/* Define to 1 if 'dlsym()' requires a leading underscore in symbol names. */
/* #undef DLSYM_NEEDS_UNDERSCORE */

/* Define to 1 to enable the 'chroot' function. */
/* #undef ENABLE_CHROOT_FUNC */

/* The GDBM handler header file. */
/* #undef GDBM_INCLUDE_FILE */

/* Define to 1 if 'TIOCGWINSZ' requires <sys/ioctl.h>. */
#define GWINSZ_IN_SYS_IOCTL 1

/* Define to 1 if 'ldap_set_rebind_proc' has 3 arguments. */
/* #undef HAVE_3ARG_SETREBINDPROC */

/* Define to 1 when aarch64 CRC32 API is available. */
/* #undef HAVE_AARCH64_CRC32 */

/* Define to 1 if the odbc extension uses the Adabas D. */
/* #undef HAVE_ADABAS */

/* Define to 1 if the compiler supports '__alignof__'. */
#define HAVE_ALIGNOF 1

/* Define to 1 if you have 'alloca', as a function or macro. */
#define HAVE_ALLOCA 1

/* Define to 1 if <alloca.h> works. */
#define HAVE_ALLOCA_H 1

/* Define to 1 if you have the 'alphasort' function. */
#define HAVE_ALPHASORT 1

/* Define to 1 if AppArmor confinement is available for PHP-FPM. */
/* #undef HAVE_APPARMOR */

/* Define to 1 if you have the 'arc4random_buf' function. */
/* #undef HAVE_ARC4RANDOM_BUF */

/* Define to 1 if the system has the 'libargon2' library. */
/* #undef HAVE_ARGON2LIB */

/* Define to 1 if you have the <arpa/inet.h> header file. */
#define HAVE_ARPA_INET_H 1

/* Define to 1 if you have the <arpa/nameser.h> header file. */
#define HAVE_ARPA_NAMESER_H 1

/* Define to 1 if you have the 'asctime_r' function. */
#define HAVE_ASCTIME_R 1

/* Define to 1 if asm goto support is available. */
#define HAVE_ASM_GOTO 1

/* Define to 1 if you have the 'asprintf' function. */
#define HAVE_ASPRINTF 1

/* Define to 1 if you have the 'atoll' function. */
#define HAVE_ATOLL 1

/* Define to 1 if the compiler supports the 'aligned' variable attribute. */
#define HAVE_ATTRIBUTE_ALIGNED 1

/* Define to 1 if the PHP extension 'bcmath' is available. */
#define HAVE_BCMATH 1

/* Define to 1 if you have the 'bind_textdomain_codeset' function. */
#define HAVE_BIND_TEXTDOMAIN_CODESET 1

/* Define to 1 if system has a broken 'getcwd'. */
/* #undef HAVE_BROKEN_GETCWD */

/* Define to 1 if PHP has the <main/build-defs.h> header file. */
#define HAVE_BUILD_DEFS_H 1

/* Define to 1 if compiler supports __sync_bool_compare_and_swap() a.o. */
#define HAVE_BUILTIN_ATOMIC 1

/* Define to 1 if PHP uses the bundled PCRE library. */
#define HAVE_BUNDLED_PCRE 1

/* Define to 1 if the PHP extension 'bz2' is available. */
/* #undef HAVE_BZ2 */

/* Define to 1 if the PHP extension 'calendar' is available. */
#define HAVE_CALENDAR 1

/* Define to 1 if libzip library has the
   'zip_register_cancel_callback_with_state' function (available since 1.6.0).
   */
/* #undef HAVE_CANCEL_CALLBACK */

/* Define to 1 if Capstone is available. */
/* #undef HAVE_CAPSTONE */

/* Define to 1 if you have the 'chroot' function. */
#define HAVE_CHROOT 1

/* Define to 1 if you have the 'clearenv' function. */
#define HAVE_CLEARENV 1

/* Define to 1 if you have the <cli0cli.h> header file. */
/* #undef HAVE_CLI0CLI_H */

/* Define to 1 if you have the <cli0core.h> header file. */
/* #undef HAVE_CLI0CORE_H */

/* Define to 1 if you have the <cli0defs.h> header file. */
/* #undef HAVE_CLI0DEFS_H */

/* Define to 1 if you have the <cli0env.h> header file. */
/* #undef HAVE_CLI0ENV_H */

/* Define to 1 if you have the <cli0ext.h> header file. */
/* #undef HAVE_CLI0EXT_H */

/* Define to 1 if you have the 'clock_gettime' function. */
#define HAVE_CLOCK_GETTIME 1

/* Define to 1 if you have the 'clock_get_time' function. */
/* #undef HAVE_CLOCK_GET_TIME */

/* Define to 1 if the odbc extension uses custom ODBC installation. */
/* #undef HAVE_CODBC */

/* Define to 1 if you have the <CommonCrypto/CommonRandom.h> header file. */
/* #undef HAVE_COMMONCRYPTO_COMMONRANDOM_H */

/* Define to 1 if you have the 'copy_file_range' function. */
#define HAVE_COPY_FILE_RANGE 1

/* Define to 1 if '__cpuid_count' is available. */
#define HAVE_CPUID_COUNT 1

/* Define to 1 if you have the <cpuid.h> header file. */
#define HAVE_CPUID_H 1

/* Define to 1 if you have the 'CreateProcess' function. */
/* #undef HAVE_CREATEPROCESS */

/* Define to 1 if you have the 'crypt' function. */
/* #undef HAVE_CRYPT */

/* Define to 1 if you have the <crypt.h> header file. */
/* #undef HAVE_CRYPT_H */

/* Define to 1 if you have the 'crypt_r' function. */
/* #undef HAVE_CRYPT_R */

/* Define to 1 if you have the 'ctermid' function. */
#define HAVE_CTERMID 1

/* Define to 1 if you have the 'ctime_r' function. */
#define HAVE_CTIME_R 1

/* Define to 1 if the PHP extension 'ctype' is available. */
#define HAVE_CTYPE 1

/* Define to 1 if the PHP extension 'curl' is available. */
#define HAVE_CURL 1

/* Define to 1 if libcurl is linked against old OpenSSL < 1.1. */
/* #undef HAVE_CURL_OLD_OPENSSL */

/* Define to 1 if the PHP extension 'dba' is available. */
/* #undef HAVE_DBA */

/* Define to 1 if the odbc extension uses the DBMaker. */
/* #undef HAVE_DBMAKER */

/* Define to 1 if you have the 'dcngettext' function. */
#define HAVE_DCNGETTEXT 1

/* Define to 1 if you have the declaration of 'P_ALL', and to 0 if you don't.
 */
#define HAVE_DECL_P_ALL 1

/* Define to 1 if you have the declaration of 'P_JAILID', and to 0 if you
   don't. */
#define HAVE_DECL_P_JAILID 0

/* Define to 1 if you have the declaration of 'P_PIDFD', and to 0 if you
   don't. */
#define HAVE_DECL_P_PIDFD 0

/* Define to 1 if you have the declaration of 'P_UID', and to 0 if you don't.
 */
#define HAVE_DECL_P_UID 0

/* Define to 1 if you have the declaration of 'strerror_r', and to 0 if you
   don't. */
#define HAVE_DECL_STRERROR_R 1

/* Define to 1 if you have the declaration of 'strptime'. */
#define HAVE_DECL_STRPTIME 1

/* Define to 1 if you have the declaration of 'tzname', and to 0 if you don't.
 */
/* #undef HAVE_DECL_TZNAME */

/* Define to 1 if you have the declaration of 'WCONTINUED', and to 0 if you
   don't. */
#define HAVE_DECL_WCONTINUED 1

/* Define to 1 if you have the declaration of 'WEXITED', and to 0 if you
   don't. */
#define HAVE_DECL_WEXITED 1

/* Define to 1 if you have the declaration of 'WNOWAIT', and to 0 if you
   don't. */
#define HAVE_DECL_WNOWAIT 1

/* Define to 1 if you have the declaration of 'WSTOPPED', and to 0 if you
   don't. */
#define HAVE_DECL_WSTOPPED 1

/* Define to 1 if you have the <dirent.h> header file. */
#define HAVE_DIRENT_H 1

/* Define to 1 if you have the <dlfcn.h> header file. */
#define HAVE_DLFCN_H 1

/* Define to 1 if you have dmalloc. */
/* #undef HAVE_DMALLOC */

/* Define to 1 if you have the 'dngettext' function. */
#define HAVE_DNGETTEXT 1

/* Define to 1 if you have the <dns.h> header file. */
/* #undef HAVE_DNS_H */

/* Define to 1 if you have the 'dns_search' function. */
/* #undef HAVE_DNS_SEARCH */

/* Define to 1 if you have the 'dn_expand' function. */
#define HAVE_DN_EXPAND 1

/* Define to 1 if you have the 'dn_skipname' function. */
#define HAVE_DN_SKIPNAME 1

/* Define to 1 if the PHP extension 'dom' is available. */
#define HAVE_DOM 1

/* Define to 1 if DTrace support is enabled. */
/* #undef HAVE_DTRACE */

/* Define to 1 if you have the 'eaccess' function. */
#define HAVE_EACCESS 1

/* Define to 1 if you have the 'elf_aux_info' function. */
/* #undef HAVE_ELF_AUX_INFO */

/* Define to 1 if the odbc extension uses the Empress. */
/* #undef HAVE_EMPRESS */

/* Define to 1 if the PHP extension 'enchant' is available. */
/* #undef HAVE_ENCHANT */

/* Define to 1 if Enchant library has the 'enchant_broker_set_param' function
   (available since 1.5.0 and removed in 2.x). */
/* #undef HAVE_ENCHANT_BROKER_SET_PARAM */

/* Define to 1 if Enchant library has the 'enchant_get_version' function
   (available since 1.6.0). */
/* #undef HAVE_ENCHANT_GET_VERSION */

/* Define to 1 if libzip library has encryption support (available since
   1.2.0). */
/* #undef HAVE_ENCRYPTION */

/* Define to 1 if system has a working epoll. */
#define HAVE_EPOLL 1

/* Define to 1 if edit/readline library has the 'rl_erase_empty_line' global
   variable. */
/* #undef HAVE_ERASE_EMPTY_LINE */

/* Define to 1 if the odbc extension uses the Easysoft OOB. */
/* #undef HAVE_ESOOB */

/* Define to 1 if the PHP extension 'exif' is available. */
#define HAVE_EXIF 1

/* Define to 1 if you have the 'explicit_bzero' function. */
#define HAVE_EXPLICIT_BZERO 1

/* Define to 1 if you have the 'explicit_memset' function. */
/* #undef HAVE_EXPLICIT_MEMSET */

/* Define to 1 if you have the <fcntl.h> header file. */
#define HAVE_FCNTL_H 1

/* Define to 1 if you have the 'fdatasync' function. */
#define HAVE_FDATASYNC 1

/* Define to 1 if the PHP extension 'ffi' is available. */
/* #undef HAVE_FFI */

/* Define to 1 if libffi supports the 'FFI_FASTCALL' calling convention. */
/* #undef HAVE_FFI_FASTCALL */

/* Define to 1 if libffi supports the 'FFI_MS_CDECL' calling convention. */
/* #undef HAVE_FFI_MS_CDECL */

/* Define to 1 if libffi supports the 'FFI_PASCAL' calling convention. */
/* #undef HAVE_FFI_PASCAL */

/* Define to 1 if libffi supports the 'FFI_REGISTER' calling convention. */
/* #undef HAVE_FFI_REGISTER */

/* Define to 1 if libffi supports the 'FFI_STDCALL' calling convention. */
/* #undef HAVE_FFI_STDCALL */

/* Define to 1 if libffi supports the 'FFI_SYSV' calling convention. */
/* #undef HAVE_FFI_SYSV */

/* Define to 1 if libffi supports the 'FFI_THISCALL' calling convention. */
/* #undef HAVE_FFI_THISCALL */

/* Define to 1 if you have the 'flock' function. */
#define HAVE_FLOCK 1

/* Define to 1 if flush should be called explicitly after a buffered io. */
/* #undef HAVE_FLUSHIO */

/* Define to 1 if your system has a working POSIX 'fnmatch' function. */
#define HAVE_FNMATCH 1

/* Define to 1 if you have the 'fopencookie' function. */
#define HAVE_FOPENCOOKIE 1

/* Define to 1 if you have the 'fork' function. */
#define HAVE_FORK 1

/* Define to 1 if you have the 'forkx' function. */
/* #undef HAVE_FORKX */

/* Define to 1 if you have the 'fpathconf' function. */
#define HAVE_FPATHCONF 1

/* Define to 1 if PHP-FPM has ACL support. */
/* #undef HAVE_FPM_ACL */

/* Define to 1 if fpsetprec is present and usable. */
/* #undef HAVE_FPSETPREC */

/* Define to 1 if FPU control word can be manipulated by inline assembler. */
#define HAVE_FPU_INLINE_ASM_X86 1

/* Define to 1 if you have the 'ftok' function. */
#define HAVE_FTOK 1

/* Define to 1 if the PHP extension 'ftp' is available. */
/* #undef HAVE_FTP */

/* Define to 1 if FTP over SSL is enabled. */
/* #undef HAVE_FTP_SSL */

/* Define to 1 if the system has the `ifunc' function attribute */
#define HAVE_FUNC_ATTRIBUTE_IFUNC 1

/* Define to 1 if the system has the `target' function attribute */
#define HAVE_FUNC_ATTRIBUTE_TARGET 1

/* Define to 1 if the system has the `visibility' function attribute */
#define HAVE_FUNC_ATTRIBUTE_VISIBILITY 1

/* Define to 1 if you have the three-argument form of gethostbyname_r(). */
/* #undef HAVE_FUNC_GETHOSTBYNAME_R_3 */

/* Define to 1 if you have the five-argument form of gethostbyname_r(). */
/* #undef HAVE_FUNC_GETHOSTBYNAME_R_5 */

/* Define to 1 if you have the six-argument form of gethostbyname_r(). */
#define HAVE_FUNC_GETHOSTBYNAME_R_6 1

/* Define to 1 if you have the 'funopen' function. */
/* #undef HAVE_FUNOPEN */

/* Define to 1 if you have the 'gai_strerror' function. */
#define HAVE_GAI_STRERROR 1

/* Define to 1 if the target system has support for global register variables.
 */
#define HAVE_GCC_GLOBAL_REGS 1

/* Define to 1 if GCOV code coverage is enabled. */
/* #undef HAVE_GCOV */

/* Define to 1 if gd extension has AVIF support. */
/* #undef HAVE_GD_AVIF */

/* Define to 1 if gd extension has BMP support. */
#define HAVE_GD_BMP 1

/* Define to 1 if gd extension uses GD library bundled in PHP. */
#define HAVE_GD_BUNDLED 1

/* Define to 1 if gd extension has FreeType support. */
#define HAVE_GD_FREETYPE 1

/* Define to 1 if GD library has the 'gdImageGetInterpolationMethod' function.
 */
/* #undef HAVE_GD_GET_INTERPOLATION */

/* Define to 1 if gd extension has JPEG support. */
#define HAVE_GD_JPG 1

/* Define to 1 if GD library has the 'gdVersionString' function. */
/* #undef HAVE_GD_LIBVERSION */

/* Define to 1 if gd extension has PNG support. */
#define HAVE_GD_PNG 1

/* Define to 1 if gd extension has TGA support. */
#define HAVE_GD_TGA 1

/* Define to 1 if gd extension has WebP support. */
/* #undef HAVE_GD_WEBP */

/* Define to 1 if gd extension has XPM support. */
/* #undef HAVE_GD_XPM */

/* Define to 1 if you have the 'getaddrinfo' function. */
#define HAVE_GETADDRINFO 1

/* Define to 1 if you have the 'getcpuid' function. */
/* #undef HAVE_GETCPUID */

/* Define to 1 if you have the 'getcwd' function. */
#define HAVE_GETCWD 1

/* Define to 1 if you have the 'getgrgid_r' function. */
#define HAVE_GETGRGID_R 1

/* Define to 1 if you have the 'getgrnam_r' function. */
#define HAVE_GETGRNAM_R 1

/* Define to 1 if you have the 'getgroups' function. */
#define HAVE_GETGROUPS 1

/* Define to 1 if you have some form of gethostbyname_r(). */
#define HAVE_GETHOSTBYNAME_R 1

/* Define to 1 if you have the 'gethostname' function. */
#define HAVE_GETHOSTNAME 1

/* Define to 1 if you have the 'getifaddrs' function. */
#define HAVE_GETIFADDRS 1

/* Define to 1 if you have the 'getloadavg' function. */
#define HAVE_GETLOADAVG 1

/* Define to 1 if you have the 'getlogin' function. */
#define HAVE_GETLOGIN 1

/* Define to 1 if you have the 'getopt' function. */
#define HAVE_GETOPT 1

/* Define to 1 if you have the 'getpgid' function. */
#define HAVE_GETPGID 1

/* Define to 1 if you have the 'getpid' function. */
#define HAVE_GETPID 1

/* Define to 1 if you have the 'getpriority' function. */
#define HAVE_GETPRIORITY 1

/* Define to 1 if you have the 'getprotobyname' function. */
#define HAVE_GETPROTOBYNAME 1

/* Define to 1 if you have the 'getprotobynumber' function. */
#define HAVE_GETPROTOBYNUMBER 1

/* Define to 1 if you have the 'getpwnam_r' function. */
#define HAVE_GETPWNAM_R 1

/* Define to 1 if you have the 'getpwuid_r' function. */
#define HAVE_GETPWUID_R 1

/* Define to 1 if you have the 'getrandom' function. */
#define HAVE_GETRANDOM 1

/* Define to 1 if you have the 'getrlimit' function. */
#define HAVE_GETRLIMIT 1

/* Define to 1 if you have the 'getrusage' function. */
#define HAVE_GETRUSAGE 1

/* Define to 1 if you have the 'getservbyname' function. */
#define HAVE_GETSERVBYNAME 1

/* Define to 1 if you have the 'getservbyport' function. */
#define HAVE_GETSERVBYPORT 1

/* Define to 1 if you have the 'getsid' function. */
#define HAVE_GETSID 1

/* Define to 1 if you have the 'gettid' function. */
#define HAVE_GETTID 1

/* Define to 1 if you have the 'gettimeofday' function. */
#define HAVE_GETTIMEOFDAY 1

/* Define to 1 if you have the 'getwd' function. */
#define HAVE_GETWD 1

/* Define to 1 if iconv implementation is glibc. */
#define HAVE_GLIBC_ICONV 1

/* Define to 1 if you have the 'glob' function. */
#define HAVE_GLOB 1

/* Define to 1 if the PHP extension 'gmp' is available. */
/* #undef HAVE_GMP */

/* Define to 1 if you have the 'gmtime_r' function. */
#define HAVE_GMTIME_R 1

/* Define to 1 if you have the <grp.h> header file. */
#define HAVE_GRP_H 1

/* Define to 1 if edit/readline library has the 'history_list' function. */
/* #undef HAVE_HISTORY_LIST */

/* Define to 1 if you have the 'hstrerror' function. */
#define HAVE_HSTRERROR 1

/* Define to 1 to enable copying PHP CODE pages into HUGE PAGES. */
#define HAVE_HUGE_CODE_PAGES 1

/* Define to 1 if the odbc extension uses the IBM DB2. */
/* #undef HAVE_IBMDB2 */

/* Define to 1 if iconv implementation is IBM. */
/* #undef HAVE_IBM_ICONV */

/* Define to 1 if the PHP extension 'iconv' is available. */
#define HAVE_ICONV 1

/* Define to 1 if you have the <ieeefp.h> header file. */
/* #undef HAVE_IEEEFP_H */

/* Define to 1 if you have the 'if_indextoname' function. */
#define HAVE_IF_INDEXTONAME 1

/* Define to 1 if you have the 'if_nametoindex' function. */
#define HAVE_IF_NAMETOINDEX 1

/* Define to 1 if you have the <immintrin.h> header file. */
#define HAVE_IMMINTRIN_H 1

/* Define to 1 if you have the 'initgroups' function. */
#define HAVE_INITGROUPS 1

/* Define to 1 if the system has the type 'intmax_t'. */
#define HAVE_INTMAX_T 1

/* Define to 1 if you have the <inttypes.h> header file. */
#define HAVE_INTTYPES_H 1

/* Define to 1 if the odbc extension uses the iODBC. */
/* #undef HAVE_IODBC */

/* Define to 1 if you have the <iodbc.h> header file. */
/* #undef HAVE_IODBC_H */

/* Define to 1 if you have the <io.h> header file. */
/* #undef HAVE_IO_H */

/* Define to 1 if IPv6 is enabled and supported. */
#define HAVE_IPV6 1

/* Define to 1 if you have the <isqlext.h> header file. */
/* #undef HAVE_ISQLEXT_H */

/* Define to 1 if you have the <isql.h> header file. */
/* #undef HAVE_ISQL_H */

/* Define to 1 to enable JIT. */
#define HAVE_JIT 1

/* Define to 1 if you have the 'kill' function. */
#define HAVE_KILL 1

/* Define to 1 if system has a working 'kqueue' function. */
/* #undef HAVE_KQUEUE */

/* Define to 1 if you have the <langinfo.h> header file. */
#define HAVE_LANGINFO_H 1

/* Define to 1 if you have the 'lchown' function. */
#define HAVE_LCHOWN 1

/* Define to 1 if the PHP extension 'ldap' is available. */
/* #undef HAVE_LDAP */

/* Define to 1 if you have the 'ldap_control_find' function. */
/* #undef HAVE_LDAP_CONTROL_FIND */

/* Define to 1 if you have the 'ldap_extended_operation' function. */
/* #undef HAVE_LDAP_EXTENDED_OPERATION */

/* Define to 1 if you have the 'ldap_extended_operation_s' function. */
/* #undef HAVE_LDAP_EXTENDED_OPERATION_S */

/* Define to 1 if you have the 'ldap_parse_extended_result' function. */
/* #undef HAVE_LDAP_PARSE_EXTENDED_RESULT */

/* Define to 1 if you have the 'ldap_parse_reference' function. */
/* #undef HAVE_LDAP_PARSE_REFERENCE */

/* Define to 1 if you have the 'ldap_parse_result' function. */
/* #undef HAVE_LDAP_PARSE_RESULT */

/* Define to 1 if you have the 'ldap_passwd' function. */
/* #undef HAVE_LDAP_PASSWD */

/* Define to 1 if you have the 'ldap_refresh_s' function. */
/* #undef HAVE_LDAP_REFRESH_S */

/* Define to 1 if the ldap extension has SASL support enabled. */
/* #undef HAVE_LDAP_SASL */

/* Define to 1 if you have the 'ldap_start_tls_s' function. */
/* #undef HAVE_LDAP_START_TLS_S */

/* Define to 1 if you have the 'ldap_whoami_s' function. */
/* #undef HAVE_LDAP_WHOAMI_S */

/* Define to 1 if you have the 'atomic' library (-latomic). */
/* #undef HAVE_LIBATOMIC */

/* Define to 1 if you have the libavif library. */
/* #undef HAVE_LIBAVIF */

/* Define to 1 if you have the 'dl' library (-ldl). */
#define HAVE_LIBDL 1

/* Define to 1 if readline extension uses the 'libedit' library. */
/* #undef HAVE_LIBEDIT */

/* Define to 1 if the system has the Expat XML parser library. */
/* #undef HAVE_LIBEXPAT */

/* Define to 1 if you have the FreeType library. */
#define HAVE_LIBFREETYPE 1

/* Define to 1 if gd extension uses external system GD library. */
/* #undef HAVE_LIBGD */

/* Define to 1 if you have the 'libiconv' function. */
/* #undef HAVE_LIBICONV */

/* Define to 1 if you have the 'intl' library. */
#define HAVE_LIBINTL 1

/* Define to 1 if you have the libjpeg library. */
#define HAVE_LIBJPEG 1

/* Define to 1 if the system has the 'mm' library. */
/* #undef HAVE_LIBMM */

/* Define to 1 if you have the libpng library. */
#define HAVE_LIBPNG 1

/* Define to 1 if you have the <libproc.h> header file. */
/* #undef HAVE_LIBPROC_H */

/* Define to 1 if you have the <LibraryManager.h> header file. */
/* #undef HAVE_LIBRARYMANAGER_H */

/* Define to 1 if readline extension uses the 'readline' library. */
/* #undef HAVE_LIBREADLINE */

/* Define to 1 if you have the 'resolv' library (-lresolv). */
#define HAVE_LIBRESOLV 1

/* Define to 1 if you have the 'socket' library (-lsocket). */
/* #undef HAVE_LIBSOCKET */

/* Define to 1 if the PHP extension 'sodium' is available. */
/* #undef HAVE_LIBSODIUMLIB */

/* Define to 1 if you have the libwebp library. */
/* #undef HAVE_LIBWEBP */

/* Define to 1 if the PHP extension 'libxml' is available. */
#define HAVE_LIBXML 1

/* Define to 1 if libzip library has the 'zip_libzip_version' function
   (available since 1.3.1). */
/* #undef HAVE_LIBZIP_VERSION */

/* Define to 1 if you have the <linux/filter.h> header file. */
#define HAVE_LINUX_FILTER_H 1

/* Define to 1 if you have the <linux/sock_diag.h> header file. */
#define HAVE_LINUX_SOCK_DIAG_H 1

/* Define to 1 if you have the 'localtime_r' function. */
#define HAVE_LOCALTIME_R 1

/* Define to 1 if the system has the type 'long double'. */
/* #undef HAVE_LONG_DOUBLE */

/* Define to 1 if you have 'SO_LISTENQ*'. */
/* #undef HAVE_LQ_SO_LISTENQ */

/* Define to 1 if you have 'TCP_CONNECTION_INFO'. */
/* #undef HAVE_LQ_TCP_CONNECTION_INFO */

/* Define to 1 if you have 'TCP_INFO'. */
#define HAVE_LQ_TCP_INFO 1

/* Define to 1 if you have the 'mach_vm_read' function. */
/* #undef HAVE_MACH_VM_READ */

/* Define to 1 if you have the 'makedev' function. */
#define HAVE_MAKEDEV 1

/* Define to 1 if mbstring has multibyte regex support enabled. */
/* #undef HAVE_MBREGEX */

/* Define to 1 if the PHP extension 'mbstring' is available. */
#define HAVE_MBSTRING 1

/* Define to 1 if you have the 'memcntl' function. */
/* #undef HAVE_MEMCNTL */

/* Define to 1 if you have the 'memfd_create' function. */
#define HAVE_MEMFD_CREATE 1

/* Define to 1 if you have the 'memmem' function. */
#define HAVE_MEMMEM 1

/* Define to 1 if you have the 'mempcpy' function. */
#define HAVE_MEMPCPY 1

/* Define to 1 if you have the 'memrchr' function. */
#define HAVE_MEMRCHR 1

/* Define to 1 if libzip library has 'zip_*_method_supported' functions
   (available since 1.7.0). */
/* #undef HAVE_METHOD_SUPPORTED */

/* Define to 1 if you have the <minix/config.h> header file. */
/* #undef HAVE_MINIX_CONFIG_H */

/* Define to 1 if you have the 'mkfifo' function. */
#define HAVE_MKFIFO 1

/* Define to 1 if you have the 'mknod' function. */
#define HAVE_MKNOD 1

/* Define to 1 if you have the 'mkstemp' function. */
#define HAVE_MKSTEMP 1

/* Define to 1 if you have the 'mmap' function. */
#define HAVE_MMAP 1

/* Define to 1 if you have the 'mprotect' function. */
#define HAVE_MPROTECT 1

/* Define to 1 if you have the 'mremap' function. */
#define HAVE_MREMAP 1

/* Define to 1 if you have the 'nanosleep' function. */
#define HAVE_NANOSLEEP 1

/* Define to 1 if you have the <netinet/in.h> header file. */
#define HAVE_NETINET_IN_H 1

/* Define to 1 if you have the <net/if.h> header file. */
#define HAVE_NET_IF_H 1

/* Define to 1 if you have the 'ngettext' function. */
#define HAVE_NGETTEXT 1

/* Define to 1 if you have the 'nice' function. */
#define HAVE_NICE 1

/* Define to 1 if you have the 'nl_langinfo' function. */
#define HAVE_NL_LANGINFO 1

/* Define to 1 if you have the <nmmintrin.h> header file. */
#define HAVE_NMMINTRIN_H 1

/* Define to 1 if you have the <odbc.h> header file. */
/* #undef HAVE_ODBC_H */

/* Define to 1 if you have the 'openpty' function. */
#define HAVE_OPENPTY 1

/* Define to 1 to enable OpenSSL argon2 password hashing. */
/* #undef HAVE_OPENSSL_ARGON2 */

/* Define to 1 if you have the <openssl/crypto.h> header file. */
/* #undef HAVE_OPENSSL_CRYPTO_H */

/* Define to 1 if the PHP extension 'openssl' is available. */
#define HAVE_OPENSSL_EXT 1

/* Define to 1 if the ldap extension uses the Oracle Instant Client. */
/* #undef HAVE_ORALDAP */

/* Define to 1 if you have the 'pathconf' function. */
#define HAVE_PATHCONF 1

/* Define to 1 if PCRE JIT is enabled and supported. */
#define HAVE_PCRE_JIT_SUPPORT 1

/* Define to 1 if pcre has Valgrind support enabled. */
/* #undef HAVE_PCRE_VALGRIND_SUPPORT */

/* Define to 1 if the PHP extension 'pdo_pgsql' is available. */
/* #undef HAVE_PDO_PGSQL */

/* Define to 1 if the PHP extension 'pgsql' is available. */
/* #undef HAVE_PGSQL */

/* Define to 1 if libpq has the 'PQchangePassword' function (PostgreSQL 17 or
   later). */
/* #undef HAVE_PG_CHANGE_PASSWORD */

/* Define to 1 if libpq has the 'PQresultMemorySize' function (PostgreSQL 12
   or later). */
/* #undef HAVE_PG_RESULT_MEMORY_SIZE */

/* Define to 1 if libpq has the 'PQsetChunkedRowsMode' function (PostgreSQL 17
   or later). */
/* #undef HAVE_PG_SET_CHUNKED_ROWS_SIZE */

/* Define to 1 if libpq has the 'PQsocketPoll' function (PostgreSQL 17 or
   later). */
/* #undef HAVE_PG_SOCKET_POLL */

/* Define to 1 if the phpdbg SAPI has libedit/readline integration. */
/* #undef HAVE_PHPDBG_READLINE */

/* Define to 1 if the PHP extension 'session' is available. */
#define HAVE_PHP_SESSION 1

/* Define to 1 if you have the 'pidfd_open' function. */
/* #undef HAVE_PIDFD_OPEN */

/* Define to 1 if you have the 'poll' function. */
#define HAVE_POLL 1

/* Define to 1 if you have the <poll.h> header file. */
#define HAVE_POLL_H 1

/* Define to 1 if you have the 'port_create' function. */
/* #undef HAVE_PORT_CREATE */

/* Define to 1 if the PHP extension 'posix' is available. */
#define HAVE_POSIX 1

/* Define to 1 if you have the 'posix_spawn_file_actions_addchdir_np'
   function. */
#define HAVE_POSIX_SPAWN_FILE_ACTIONS_ADDCHDIR_NP 1

/* Define to 1 if libpq has the 'PQclosePrepared' function (PostgreSQL 17 or
   later). */
/* #undef HAVE_PQCLOSEPREPARED */

/* Define to 1 if PGVerbosity enum has PQERRORS_SQLSTATE. */
/* #undef HAVE_PQERRORS_SQLSTATE */

/* Define to 1 if you have the 'prctl' function. */
#define HAVE_PRCTL 1

/* Define to 1 if you have the 'pread' function. */
#define HAVE_PREAD 1

/* Define to 1 if you have the 'procctl' function. */
/* #undef HAVE_PROCCTL */

/* Define to 1 if libzip library has the
   'zip_register_progress_callback_with_state' function (available since
   1.3.0). */
/* #undef HAVE_PROGRESS_CALLBACK */

/* Define to 1 if you have the 'pset_bind' function. */
/* #undef HAVE_PSET_BIND */

/* Define if the PS_STRINGS exists. */
/* #undef HAVE_PS_STRINGS */

/* Define to 1 if you have the 'pthread_attr_getstack' function. */
/* #undef HAVE_PTHREAD_ATTR_GETSTACK */

/* Define to 1 if you have the 'pthread_attr_get_np' function. */
/* #undef HAVE_PTHREAD_ATTR_GET_NP */

/* Define to 1 if you have the 'pthread_getattr_np' function. */
/* #undef HAVE_PTHREAD_GETATTR_NP */

/* Define to 1 if you have the 'pthread_get_stackaddr_np' function. */
/* #undef HAVE_PTHREAD_GET_STACKADDR_NP */

/* Define to 1 if you have the 'pthread_jit_write_protect_np' function. */
/* #undef HAVE_PTHREAD_JIT_WRITE_PROTECT_NP */

/* Define to 1 if you have the 'pthread_set_qos_class_self_np' function. */
/* #undef HAVE_PTHREAD_SET_QOS_CLASS_SELF_NP */

/* Define to 1 if you have the 'pthread_stackseg_np' function. */
/* #undef HAVE_PTHREAD_STACKSEG_NP */

/* Define to 1 if you have the 'ptrace' function. */
#define HAVE_PTRACE 1

/* Define to 1 if the system has the type 'ptrdiff_t'. */
#define HAVE_PTRDIFF_T 1

/* Define to 1 if you have the <pty.h> header file. */
#define HAVE_PTY_H 1

/* Define to 1 if you have the 'putenv' function. */
#define HAVE_PUTENV 1

/* Define to 1 if you have the <pwd.h> header file. */
#define HAVE_PWD_H 1

/* Define to 1 if you have the 'pwrite' function. */
#define HAVE_PWRITE 1

/* Define to 1 if OpenSSL crypto library has the 'RAND_egd' function. */
/* #undef HAVE_RAND_EGD */

/* Define to 1 if you have the <resolv.h> header file. */
#define HAVE_RESOLV_H 1

/* Define to 1 if you have the 'res_ndestroy' function. */
/* #undef HAVE_RES_NDESTROY */

/* Define to 1 if you have the 'res_nsearch' function. */
#define HAVE_RES_NSEARCH 1

/* Define to 1 if you have the 'res_search' function. */
#define HAVE_RES_SEARCH 1

/* Define to 1 if you have the 'rfork' function. */
/* #undef HAVE_RFORK */

/* Define to 1 if edit/readline library has the 'rl_callback_read_char'
   function. */
/* #undef HAVE_RL_CALLBACK_READ_CHAR */

/* Define to 1 if edit/readline library has the 'rl_completion_matches'
   function. */
/* #undef HAVE_RL_COMPLETION_MATCHES */

/* Define to 1 if edit/readline library has the 'rl_on_new_line' function. */
/* #undef HAVE_RL_ON_NEW_LINE */

/* Define to 1 if the odbc extension uses the SAP DB. */
/* #undef HAVE_SAPDB */

/* Define to 1 if you have the 'scandir' function. */
#define HAVE_SCANDIR 1

/* Define to 1 if the 'sched_getcpu' function is properly supported. */
#define HAVE_SCHED_GETCPU 1

/* Define to 1 if you have the 'sched_setaffinity' function. */
#define HAVE_SCHED_SETAFFINITY 1

/* Define to 1 if system has a working 'select' function. */
#define HAVE_SELECT 1

/* Define to 1 if SELinux is available in PHP-FPM. */
/* #undef HAVE_SELINUX */

/* Define to 1 if you have the 'setegid' function. */
#define HAVE_SETEGID 1

/* Define to 1 if you have the 'setenv' function. */
#define HAVE_SETENV 1

/* Define to 1 if you have the 'seteuid' function. */
#define HAVE_SETEUID 1

/* Define to 1 if you have the 'setitimer' function. */
#define HAVE_SETITIMER 1

/* Define to 1 if you have the 'setpflags' function. */
/* #undef HAVE_SETPFLAGS */

/* Define to 1 if you have the 'setpriority' function. */
#define HAVE_SETPRIORITY 1

/* Define to 1 if you have the 'setproctitle' function. */
/* #undef HAVE_SETPROCTITLE */

/* Define to 1 if you have the 'setproctitle_fast' function. */
/* #undef HAVE_SETPROCTITLE_FAST */

/* Define to 1 if you have the 'setrlimit' function. */
#define HAVE_SETRLIMIT 1

/* Define to 1 if you have the 'setsid' function. */
#define HAVE_SETSID 1

/* Define to 1 if libzip library has the 'zip_file_set_mtime' function
   (available since 1.0.0). */
/* #undef HAVE_SET_MTIME */

/* Define to 1 if the PHP extension 'shmop' is available. */
#define HAVE_SHMOP 1

/* Define to 1 if you have the 'shm_create_largepage' function. */
/* #undef HAVE_SHM_CREATE_LARGEPAGE */

/* Define to 1 if you have the SysV IPC SHM support. */
#define HAVE_SHM_IPC 1

/* Define to 1 if you have the mmap(MAP_ANON) SHM support. */
#define HAVE_SHM_MMAP_ANON 1

/* Define to 1 if you have the POSIX mmap() SHM support. */
#define HAVE_SHM_MMAP_POSIX 1

/* Define to 1 if you have the 'shutdown' function. */
#define HAVE_SHUTDOWN 1

/* Define to 1 if SNMP library has the 'shutdown_snmp_logging' function. */
/* #undef HAVE_SHUTDOWN_SNMP_LOGGING */

/* Define to 1 if you have the 'sigaction' function. */
#define HAVE_SIGACTION 1

/* Define to 1 if you have the 'sigprocmask' function. */
#define HAVE_SIGPROCMASK 1

/* Define to 1 if you have the 'sigtimedwait' function. */
#define HAVE_SIGTIMEDWAIT 1

/* Define to 1 if you have the 'sigwaitinfo' function. */
#define HAVE_SIGWAITINFO 1

/* Define to 1 if the PHP extension 'simplexml' is available. */
#define HAVE_SIMPLEXML 1

/* Define to 1 if the PHP hash extension uses the slow SHA3 algorithm. */
/* #undef HAVE_SLOW_HASH3 */

/* Define to 1 if the PHP extension 'snmp' is available. */
/* #undef HAVE_SNMP */

/* Define to 1 if SNMP library has the 'usmHMAC192SHA256AuthProtocol' array.
 */
/* #undef HAVE_SNMP_SHA256 */

/* Define to 1 if SNMP library has the 'usmHMAC384SHA512AuthProtocol' array.
 */
/* #undef HAVE_SNMP_SHA512 */

/* Define to 1 if the PHP extension 'soap' is available. */
/* #undef HAVE_SOAP */

/* Define to 1 if you have the 'sockatmark' function. */
#define HAVE_SOCKATMARK 1

/* Define to 1 if you have the 'socketpair' function. */
#define HAVE_SOCKETPAIR 1

/* Define to 1 if the PHP extension 'sockets' is available. */
#define HAVE_SOCKETS 1

/* Define to 1 if the system has the type 'socklen_t'. */
#define HAVE_SOCKLEN_T 1

/* Define to 1 if the odbc extension uses the Solid DB. */
/* #undef HAVE_SOLID */

/* Define to 1 if Solid DB 3.0 is used. */
/* #undef HAVE_SOLID_30 */

/* Define to 1 if Solid DB 3.5 is used. */
/* #undef HAVE_SOLID_35 */

/* Define to 1 if you have the <sqlcli1.h> header file. */
/* #undef HAVE_SQLCLI1_H */

/* Define to 1 if ODBC library has 'SQLDataSources', as a function or macro.
 */
/* #undef HAVE_SQLDATASOURCES */

/* Define to 1 if you have the <sqlext.h> header file. */
/* #undef HAVE_SQLEXT_H */

/* Define to 1 if the PHP extension 'sqlite3' is available. */
/* #undef HAVE_SQLITE3 */

/* Define to 1 if SQLite library has the 'sqlite3_close_v2' function. */
/* #undef HAVE_SQLITE3_CLOSE_V2 */

/* Define to 1 if SQLite library was compiled with the
   SQLITE_ENABLE_COLUMN_METADATA and has the 'sqlite3_column_table_name'
   function. */
/* #undef HAVE_SQLITE3_COLUMN_TABLE_NAME */

/* Define to 1 if SQLite library has the 'sqlite3_errstr' function. */
/* #undef HAVE_SQLITE3_ERRSTR */

/* Define to 1 if SQLite library has the 'sqlite3_expanded_sql' function. */
/* #undef HAVE_SQLITE3_EXPANDED_SQL */

/* Define to 1 if you have the <sqltypes.h> header file. */
/* #undef HAVE_SQLTYPES_H */

/* Define to 1 if you have the <sqlucode.h> header file. */
/* #undef HAVE_SQLUCODE_H */

/* Define to 1 if you have the <sqlunix.h> header file. */
/* #undef HAVE_SQLUNIX_H */

/* Define to 1 if you have the <sql.h> header file. */
/* #undef HAVE_SQL_H */

/* Define to 1 if the system has the type 'ssize_t'. */
#define HAVE_SSIZE_T 1

/* Define to 1 if you have the 'statfs' function. */
#define HAVE_STATFS 1

/* Define to 1 if you have the 'statvfs' function. */
#define HAVE_STATVFS 1

/* Define to 1 if you have the <stdint.h> header file. */
#define HAVE_STDINT_H 1

/* Define to 1 if you have the <stdio.h> header file. */
#define HAVE_STDIO_H 1

/* Define to 1 if you have the <stdlib.h> header file. */
#define HAVE_STDLIB_H 1

/* Define to 1 if you have the 'std_syslog' function. */
/* #undef HAVE_STD_SYSLOG */

/* Define to 1 if you have the 'strcasecmp' function. */
#define HAVE_STRCASECMP 1

/* Define to 1 if you have the 'strcasestr' function. */
#define HAVE_STRCASESTR 1

/* Define if you have 'strerror_r'. */
#define HAVE_STRERROR_R 1

/* Define to 1 if you have the <strings.h> header file. */
#define HAVE_STRINGS_H 1

/* Define to 1 if you have the <string.h> header file. */
#define HAVE_STRING_H 1

/* Define to 1 if you have the 'strlcat' function. */
/* #undef HAVE_STRLCAT */

/* Define to 1 if you have the 'strlcpy' function. */
/* #undef HAVE_STRLCPY */

/* Define to 1 if you have the 'strndup' function. */
#define HAVE_STRNDUP 1

/* Define to 1 if you have the 'strnlen' function. */
#define HAVE_STRNLEN 1

/* Define to 1 if you have the 'strptime' function. */
#define HAVE_STRPTIME 1

/* Define to 1 if you have the 'strtok_r' function. */
#define HAVE_STRTOK_R 1

/* Define to 1 if you have the 'strtoll' function. */
#define HAVE_STRTOLL 1

/* Define to 1 if the system has the type 'struct cmsgcred'. */
/* #undef HAVE_STRUCT_CMSGCRED */

/* Define to 1 if the system has the type 'struct flock'. */
#define HAVE_STRUCT_FLOCK 1

/* Define to 1 if 'sa_len' is a member of 'struct sockaddr'. */
/* #undef HAVE_STRUCT_SOCKADDR_SA_LEN */

/* Define to 1 if the system has the type 'struct sockaddr_storage'. */
#define HAVE_STRUCT_SOCKADDR_STORAGE 1

/* Define to 1 if 'ss_family' is a member of 'struct sockaddr_storage'. */
#define HAVE_STRUCT_SOCKADDR_STORAGE_SS_FAMILY 1

/* Define to 1 if 'sun_len' is a member of 'struct sockaddr_un'. */
/* #undef HAVE_STRUCT_SOCKADDR_UN_SUN_LEN */

/* Define to 1 if 'st_blksize' is a member of 'struct stat'. */
#define HAVE_STRUCT_STAT_ST_BLKSIZE 1

/* Define to 1 if 'st_blocks' is a member of 'struct stat'. */
#define HAVE_STRUCT_STAT_ST_BLOCKS 1

/* Define to 1 if 'st_rdev' is a member of 'struct stat'. */
#define HAVE_STRUCT_STAT_ST_RDEV 1

/* Define to 1 if 'tm_gmtoff' is a member of 'struct tm'. */
#define HAVE_STRUCT_TM_TM_GMTOFF 1

/* Define to 1 if 'tm_zone' is a member of 'struct tm'. */
#define HAVE_STRUCT_TM_TM_ZONE 1

/* Define to 1 if the system has the type 'struct ucred'. */
#define HAVE_STRUCT_UCRED 1

/* Define to 1 if 'domainname' is a member of 'struct utsname'. */
#define HAVE_STRUCT_UTSNAME_DOMAINNAME 1

/* Define to 1 if your 'struct stat' has 'st_blocks'. Deprecated, use
   'HAVE_STRUCT_STAT_ST_BLOCKS' instead. */
#define HAVE_ST_BLOCKS 1

/* Define to 1 if you have the 'symlink' function. */
#define HAVE_SYMLINK 1

/* Define to 1 if you have the <sysexits.h> header file. */
#define HAVE_SYSEXITS_H 1

/* Define to 1 if you have the <syslog.h> header file. */
#define HAVE_SYSLOG_H 1

/* Define to 1 if FPM has systemd integration. */
/* #undef HAVE_SYSTEMD */

/* Define to 1 if the PHP extension 'sysvmsg' is available. */
#define HAVE_SYSVMSG 1

/* Define to 1 if the PHP extension 'sysvsem' is available. */
#define HAVE_SYSVSEM 1

/* Define to 1 if the PHP extension 'sysvshm' is available. */
#define HAVE_SYSVSHM 1

/* Define to 1 if you have the <sys/acl.h> header file. */
/* #undef HAVE_SYS_ACL_H */

/* Define to 1 if you have the <sys/file.h> header file. */
#define HAVE_SYS_FILE_H 1

/* Define to 1 if you have the <sys/ioctl.h> header file. */
#define HAVE_SYS_IOCTL_H 1

/* Define to 1 if you have the <sys/ipc.h> header file. */
#define HAVE_SYS_IPC_H 1

/* Define to 1 if you have the <sys/loadavg.h> header file. */
/* #undef HAVE_SYS_LOADAVG_H */

/* Define to 1 if you have the <sys/mman.h> header file. */
#define HAVE_SYS_MMAN_H 1

/* Define to 1 if you have the <sys/mount.h> header file. */
#define HAVE_SYS_MOUNT_H 1

/* Define to 1 if you have the <sys/param.h> header file. */
#define HAVE_SYS_PARAM_H 1

/* Define to 1 if you have the <sys/poll.h> header file. */
#define HAVE_SYS_POLL_H 1

/* Define to 1 if you have the <sys/pstat.h> header file. */
/* #undef HAVE_SYS_PSTAT_H */

/* Define to 1 if you have the <sys/resource.h> header file. */
#define HAVE_SYS_RESOURCE_H 1

/* Define to 1 if you have the <sys/select.h> header file. */
#define HAVE_SYS_SELECT_H 1

/* Define to 1 if you have the <sys/socket.h> header file. */
#define HAVE_SYS_SOCKET_H 1

/* Define to 1 if you have the <sys/sockio.h> header file. */
/* #undef HAVE_SYS_SOCKIO_H */

/* Define to 1 if you have the <sys/statfs.h> header file. */
#define HAVE_SYS_STATFS_H 1

/* Define to 1 if you have the <sys/statvfs.h> header file. */
#define HAVE_SYS_STATVFS_H 1

/* Define to 1 if you have the <sys/stat.h> header file. */
#define HAVE_SYS_STAT_H 1

/* Define to 1 if you have the <sys/sysexits.h> header file. */
/* #undef HAVE_SYS_SYSEXITS_H */

/* Define to 1 if you have the <sys/sysmacros.h> header file. */
#define HAVE_SYS_SYSMACROS_H 1

/* Define to 1 if you have the <sys/time.h> header file. */
#define HAVE_SYS_TIME_H 1

/* Define to 1 if you have the <sys/types.h> header file. */
#define HAVE_SYS_TYPES_H 1

/* Define to 1 if you have the <sys/uio.h> header file. */
#define HAVE_SYS_UIO_H 1

/* Define to 1 if you have the <sys/utsname.h> header file. */
#define HAVE_SYS_UTSNAME_H 1

/* Define to 1 if you have the <sys/vfs.h> header file. */
#define HAVE_SYS_VFS_H 1

/* Define to 1 if you have the <sys/wait.h> header file. */
#define HAVE_SYS_WAIT_H 1

/* Define to 1 if the PHP extension 'tidy' is available. */
/* #undef HAVE_TIDY */

/* Define to 1 if you have the <tidybuffio.h> header file. */
/* #undef HAVE_TIDYBUFFIO_H */

/* Define to 1 if Tidy library has the 'tidyOptGetDoc' function. */
/* #undef HAVE_TIDYOPTGETDOC */

/* Define to 1 if you have the <tidyp.h> header file. */
/* #undef HAVE_TIDYP_H */

/* Define to 1 if Tidy library has the 'tidyReleaseDate' function. */
/* #undef HAVE_TIDYRELEASEDATE */

/* Define to 1 if you have the <tidy.h> header file. */
/* #undef HAVE_TIDY_H */

/* Define to 1 if you have the <timelib_config.h> header file. */
#define HAVE_TIMELIB_CONFIG_H 1

/* Define to 1 if you have the 'times' function. */
#define HAVE_TIMES 1

/* Define to 1 if you have the <tmmintrin.h> header file. */
#define HAVE_TMMINTRIN_H 1

/* Define to 1 if your 'struct tm' has 'tm_zone'. Deprecated, use
   'HAVE_STRUCT_TM_TM_ZONE' instead. */
#define HAVE_TM_ZONE 1

/* Define to 1 if you have a working 'ttyname_r' function. */
#define HAVE_TTYNAME_R 1

/* Define to 1 if you don't have 'tm_zone' but do have the external array
   'tzname'. */
/* #undef HAVE_TZNAME */

/* Define to 1 if you have the 'tzset' function. */
#define HAVE_TZSET 1

/* Define to 1 if you have the <udbcext.h> header file. */
/* #undef HAVE_UDBCEXT_H */

/* Define to 1 if the system has the type 'union semun'. */
/* #undef HAVE_UNION_SEMUN */

/* Define to 1 if you have the <unistd.h> header file. */
#define HAVE_UNISTD_H 1

/* Define to 1 if the odbc extension uses the unixODBC. */
/* #undef HAVE_UNIXODBC */

/* Define to 1 if you have the <unix.h> header file. */
/* #undef HAVE_UNIX_H */

/* Define to 1 if you have the 'unsetenv' function. */
#define HAVE_UNSETENV 1

/* Define to 1 if you have the 'unshare' function. */
#define HAVE_UNSHARE 1

/* Define to 1 if the PHP extension 'odbc' is available. */
/* #undef HAVE_UODBC */

/* Define to 1 if faulting on write-protected memory support can be compiled
   for userfaultfd. */
/* #undef HAVE_USERFAULTFD_WRITEFAULT */

/* Define to 1 if you have the 'usleep' function. */
#define HAVE_USLEEP 1

/* Define to 1 if you have the 'utime' function. */
#define HAVE_UTIME 1

/* Define to 1 if you have the 'utimes' function. */
#define HAVE_UTIMES 1

/* Define to 1 if you have the <utime.h> header file. */
#define HAVE_UTIME_H 1

/* Define to 1 if Valgrind is enabled and supported. */
/* #undef HAVE_VALGRIND */

/* Define to 1 if you have the <valgrind/cachegrind.h> header file. */
/* #undef HAVE_VALGRIND_CACHEGRIND_H */

/* Define to 1 if you have the 'vasprintf' function. */
#define HAVE_VASPRINTF 1

/* Define to 1 if you have the 'wait3' function. */
#define HAVE_WAIT3 1

/* Define to 1 if you have the 'wait4' function. */
#define HAVE_WAIT4 1

/* Define to 1 if you have the 'waitid' function. */
#define HAVE_WAITID 1

/* Define to 1 if you have the <wchar.h> header file. */
#define HAVE_WCHAR_H 1

/* Define to 1 if you have the 'WIFCONTINUED' function. */
#define HAVE_WIFCONTINUED 1

/* Define to 1 if you have the <wmmintrin.h> header file. */
#define HAVE_WMMINTRIN_H 1

/* Define to 1 if the PHP extension 'xml' is available. */
#define HAVE_XML 1

/* Define to 1 if the PHP extension 'xmlreader' is available. */
#define HAVE_XMLREADER 1

/* Define to 1 if the PHP extension 'xmlwriter' is available. */
#define HAVE_XMLWRITER 1

/* Define to 1 if you have the xpm library. */
/* #undef HAVE_XPM */

/* Define to 1 if the PHP extension 'xsl' is available. */
#define HAVE_XSL 1

/* Define to 1 if the system has the EXSLT extension library for XSLT. */
#define HAVE_XSL_EXSLT 1

/* Define to 1 if the PHP extension 'zip' is available. */
/* #undef HAVE_ZIP */

/* Define to 1 if the PHP extension 'zlib' is available. */
#define HAVE_ZLIB 1

/* Define to 1 if _controlfp is present and usable. */
/* #undef HAVE__CONTROLFP */

/* Define to 1 if _controlfp_s is present and usable. */
/* #undef HAVE__CONTROLFP_S */

/* Define to 1 if _FPU_SETCW is present and usable. */
#define HAVE__FPU_SETCW 1

/* Define to 1 if you have the '__atomic_exchange_1' function. */
/* #undef HAVE___ATOMIC_EXCHANGE_1 */

/* Define to 1 if 'iconv()' is aliased to 'libiconv()'. */
/* #undef ICONV_ALIASED_LIBICONV */

/* Define to 1 if iconv has broken IGNORE. */
#define ICONV_BROKEN_IGNORE 1

/* Define to 1 if GD library has JIS-mapped Japanese font support. */
/* #undef JISX0208 */

/* The LMDB handler header file. */
/* #undef LMDB_INCLUDE_FILE */

/* Define to 1 to load the OpenSSL legacy algorithm provider in addition to
   the default provider. */
/* #undef LOAD_OPENSSL_LEGACY_PROVIDER */

/* Define to 1 if 'major', 'minor', and 'makedev' are declared in <mkdev.h>.
 */
/* #undef MAJOR_IN_MKDEV */

/* Define to 1 if 'major', 'minor', and 'makedev' are declared in
   <sysmacros.h>. */
#define MAJOR_IN_SYSMACROS 1

/* Define to 1 if 'asctime_r' declaration is missing. */
/* #undef MISSING_ASCTIME_R_DECL */

/* Define to 1 if 'ctime_r' declaration is missing. */
/* #undef MISSING_CTIME_R_DECL */

/* Define to 1 if 'gmtime_r' declaration is missing. */
/* #undef MISSING_GMTIME_R_DECL */

/* Define to 1 if 'localtime_r' declaration is missing. */
/* #undef MISSING_LOCALTIME_R_DECL */

/* Define to 1 if 'strtok_r' declaration is missing. */
/* #undef MISSING_STRTOK_R_DECL */

/* Define to 1 if mysqlnd has compressed protocol support. */
#define MYSQLND_COMPRESSION_ENABLED 1

/* Define to 1 if mysqlnd extended SSL is enabled through a system library. */
#define MYSQLND_HAVE_SSL 1

/* Define to 1 if mysqlnd core SSL is enabled. */
#define MYSQLND_SSL_SUPPORTED 1

/* The NDBM handler header file. */
/* #undef NDBM_INCLUDE_FILE */

/* The highest supported ODBC version. */
#define ODBCVER 0x0350

/* Define to the address where bug reports for this package should be sent. */
/* #undef PACKAGE_BUGREPORT */

/* Define to the full name of this package. */
/* #undef PACKAGE_NAME */

/* Define to the full name and version of this package. */
/* #undef PACKAGE_STRING */

/* Define to the one symbol short name of this package. */
/* #undef PACKAGE_TARNAME */

/* Define to the home page for this package. */
/* #undef PACKAGE_URL */

/* Define to the version of this package. */
/* #undef PACKAGE_VERSION */

/* Number of bits in non-UTF mode for PCRE library. */
#define PCRE2_CODE_UNIT_WIDTH 8

/* The MySQL Unix socket location as defined by 'mysql_config' for use with
   the pdo_mysql extension. */
/* #undef PDO_MYSQL_UNIX_ADDR */

/* Define to the ODBC driver or driver manager value. */
/* #undef PDO_ODBC_TYPE */

/* Define to 1 if SQLite library was compiled with the
   SQLITE_OMIT_LOAD_EXTENSION and does not have the extension support with the
   'sqlite3_load_extension' function. For usage in the pdo_sqlite. See
   https://www.sqlite.org/compile.html. */
/* #undef PDO_SQLITE_OMIT_LOAD_EXTENSION */

/* Define to 1 if the pdo_mysql extension uses mysqlnd. */
#define PDO_USE_MYSQLND 1

/* Define to 1 if phar extension has native OpenSSL support. */
#define PHAR_HAVE_OPENSSL 1

/* The build architecture. */
/* #undef PHP_BUILD_ARCH */

/* The compiler used for the PHP build. */
/* #undef PHP_BUILD_COMPILER */

/* The PHP build provider information. */
/* #undef PHP_BUILD_PROVIDER */

/* The system that PHP was built on. */
#define PHP_BUILD_SYSTEM "Linux danil.dmitrievn.fvds.ru 5.4.0-204-generic #224-Ubuntu SMP Thu Dec 5 13:38:28 UTC 2024 x86_64 x86_64 x86_64 GNU/Linux"

/* Define to 1 if your system has fork/vfork/CreateProcess. */
#define PHP_CAN_SUPPORT_PROC_OPEN 1

/* Define to 1 if the compiler supports AVX-512. */
#define PHP_HAVE_AVX512_SUPPORTS 1

/* Define to 1 if the compiler supports AVX-512 VBMI. */
#define PHP_HAVE_AVX512_VBMI_SUPPORTS 1

/* Define to 1 if the compiler supports '__builtin_clz'. */
#define PHP_HAVE_BUILTIN_CLZ 1

/* Define to 1 if the compiler supports '__builtin_clzl'. */
#define PHP_HAVE_BUILTIN_CLZL 1

/* Define to 1 if the compiler supports '__builtin_clzll'. */
#define PHP_HAVE_BUILTIN_CLZLL 1

/* Define to 1 if the compiler supports '__builtin_cpu_init'. */
#define PHP_HAVE_BUILTIN_CPU_INIT 1

/* Define to 1 if the compiler supports '__builtin_cpu_supports'. */
#define PHP_HAVE_BUILTIN_CPU_SUPPORTS 1

/* Define to 1 if the compiler supports '__builtin_ctzl'. */
#define PHP_HAVE_BUILTIN_CTZL 1

/* Define to 1 if the compiler supports '__builtin_ctzll'. */
#define PHP_HAVE_BUILTIN_CTZLL 1

/* Define to 1 if the compiler supports '__builtin_expect'. */
#define PHP_HAVE_BUILTIN_EXPECT 1

/* Define to 1 if the compiler supports '__builtin_frame_address'. */
#define PHP_HAVE_BUILTIN_FRAME_ADDRESS 1

/* Define to 1 if the compiler supports '__builtin_saddll_overflow'. */
#define PHP_HAVE_BUILTIN_SADDLL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_saddl_overflow'. */
#define PHP_HAVE_BUILTIN_SADDL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_smulll_overflow'. */
#define PHP_HAVE_BUILTIN_SMULLL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_smull_overflow'. */
#define PHP_HAVE_BUILTIN_SMULL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_ssubll_overflow'. */
#define PHP_HAVE_BUILTIN_SSUBLL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_ssubl_overflow'. */
#define PHP_HAVE_BUILTIN_SSUBL_OVERFLOW 1

/* Define to 1 if the compiler supports '__builtin_unreachable'. */
#define PHP_HAVE_BUILTIN_UNREACHABLE 1

/* Define to 1 if the compiler supports '__builtin_usub_overflow'. */
#define PHP_HAVE_BUILTIN_USUB_OVERFLOW 1

/* Define to 1 if you have HP-UX 10.x.-style reentrant time functions. */
/* #undef PHP_HPUX_TIME_R */

/* The iconv implementation. */
#define PHP_ICONV_IMPL "glibc"

/* Define to 1 you have IRIX-style reentrant time functions. */
/* #undef PHP_IRIX_TIME_R */

/* Define to 1 if mhash support is enabled. */
#define PHP_MHASH_BC 1

/* The MySQL Unix socket location. */
/* #undef PHP_MYSQL_UNIX_SOCK_ADDR */

/* Define to 1 if oniguruma has an invalid entry for KOI8 encoding. */
/* #undef PHP_ONIG_BAD_KOI8_ENTRY */

/* The 'uname' output. */
#define PHP_OS "Linux"

/* Define to 1 if 'pread' declaration with 'off64_t' is missing. */
/* #undef PHP_PREAD_64 */

/* Define to 1 if 'pwrite' declaration with 'off64_t' is missing. */
/* #undef PHP_PWRITE_64 */

/* Define to 1 if PHP uses its own SIGCHLD handler, and to 0 if not. */
#define PHP_SIGCHILD 0

/* The 'uname -a' output. */
#define PHP_UNAME "Linux danil.dmitrievn.fvds.ru 5.4.0-204-generic #224-Ubuntu SMP Thu Dec 5 13:38:28 UTC 2024 x86_64 x86_64 x86_64 GNU/Linux"

/* Define to 1 if PHP uses its own crypt_r, and to 0 if using the external
   crypt library. */
#define PHP_USE_PHP_CRYPT_R 1

/* Define to 1 if 'dlopen()' uses the 'RTLD_NOW' mode flag instead of
   'RTLD_LAZY'. */
/* #undef PHP_USE_RTLD_NOW */

/* Define to 1 if 'write(2)' works. */
#define PHP_WRITE_STDOUT 1

/* Define to the /proc/pid/mem interface filename value. */
#define PROC_MEM_FILE "mem"

/* The QDBM handler header file. */
/* #undef QDBM_INCLUDE_FILE */

/* The size of 'int', as computed by sizeof. */
#define SIZEOF_INT 4

/* The size of 'intmax_t', as computed by sizeof. */
#define SIZEOF_INTMAX_T 8

/* The size of 'long', as computed by sizeof. */
#define SIZEOF_LONG 8

/* The size of 'long long', as computed by sizeof. */
#define SIZEOF_LONG_LONG 8

/* The size of 'off_t', as computed by sizeof. */
#define SIZEOF_OFF_T 8

/* The size of 'ptrdiff_t', as computed by sizeof. */
#define SIZEOF_PTRDIFF_T 8

/* The size of 'size_t', as computed by sizeof. */
#define SIZEOF_SIZE_T 8

/* The size of 'ssize_t', as computed by sizeof. */
#define SIZEOF_SSIZE_T 8

/* Define to 1 if SQLite library was compiled with the
   SQLITE_OMIT_LOAD_EXTENSION and does not have the extension support with the
   'sqlite3_load_extension' function. For usage in the sqlite3 PHP extension.
   See https://www.sqlite.org/compile.html. */
/* #undef SQLITE_OMIT_LOAD_EXTENSION */

/* Define to 1 to be able to use the wchar defs in the obsolete <sqlunix.h>
   header file on some FreeBSD systems. */
/* #undef SS_FBX */

/* Define to 1 to be able to use the obsolete <sqlunix.h> header file on some
   Linux systems. */
/* #undef SS_LINUX */

/* If using the C implementation of alloca, define if you know the
   direction of stack growth for your system; otherwise it will be
   automatically deduced at runtime.
   STACK_DIRECTION > 0 => grows toward higher addresses
   STACK_DIRECTION < 0 => grows toward lower addresses
   STACK_DIRECTION = 0 => direction of growth unknown */
/* #undef STACK_DIRECTION */

/* Define to 1 if all of the C89 standard headers exist (not just the ones
   required in a freestanding environment). This macro is provided for
   backward compatibility; new code need not use it. */
#define STDC_HEADERS 1

/* Define to 1 if strerror_r returns char *. */
#define STRERROR_R_CHAR_P 1

/* The Tokyo Cabinet handler header file. */
/* #undef TCADB_INCLUDE_FILE */

/* Define to 1 if your <sys/time.h> declares 'struct tm'. */
/* #undef TM_IN_SYS_TIME */

/* Define to 1 if gd extension has JIS-mapped Japanese font support. */
/* #undef USE_GD_JISX0208 */

/* Define to 1 if cross-process locking is required by 'accept()'. */
/* #undef USE_LOCKING */

/* Define to 1 to use system default cipher list instead of the hardcoded
   value in OpenSSL. */
/* #undef USE_OPENSSL_SYSTEM_CIPHERS */

/* Enable extensions on AIX, Interix, z/OS.  */
#ifndef _ALL_SOURCE
#define _ALL_SOURCE 1
#endif
/* Enable general extensions on macOS.  */
#ifndef _DARWIN_C_SOURCE
#define _DARWIN_C_SOURCE 1
#endif
/* Enable general extensions on Solaris.  */
#ifndef __EXTENSIONS__
#define __EXTENSIONS__ 1
#endif
/* Enable GNU extensions on systems that have them.  */
#ifndef _GNU_SOURCE
#define _GNU_SOURCE 1
#endif
/* Enable X/Open compliant socket functions that do not require linking
   with -lxnet on HP-UX 11.11.  */
#ifndef _HPUX_ALT_XOPEN_SOCKET_API
#define _HPUX_ALT_XOPEN_SOCKET_API 1
#endif
/* Identify the host operating system as Minix.
   This macro does not affect the system headers' behavior.
   A future release of Autoconf may stop defining this macro.  */
#ifndef _MINIX
/* # undef _MINIX */
#endif
/* Enable general extensions on NetBSD.
   Enable NetBSD compatibility extensions on Minix.  */
#ifndef _NETBSD_SOURCE
#define _NETBSD_SOURCE 1
#endif
/* Enable OpenBSD compatibility extensions on NetBSD.
   Oddly enough, this does nothing on OpenBSD.  */
#ifndef _OPENBSD_SOURCE
#define _OPENBSD_SOURCE 1
#endif
/* Define to 1 if needed for POSIX-compatible behavior.  */
#ifndef _POSIX_SOURCE
/* # undef _POSIX_SOURCE */
#endif
/* Define to 2 if needed for POSIX-compatible behavior.  */
#ifndef _POSIX_1_SOURCE
/* # undef _POSIX_1_SOURCE */
#endif
/* Enable POSIX-compatible threading on Solaris.  */
#ifndef _POSIX_PTHREAD_SEMANTICS
#define _POSIX_PTHREAD_SEMANTICS 1
#endif
/* Enable extensions specified by ISO/IEC TS 18661-5:2014.  */
#ifndef __STDC_WANT_IEC_60559_ATTRIBS_EXT__
#define __STDC_WANT_IEC_60559_ATTRIBS_EXT__ 1
#endif
/* Enable extensions specified by ISO/IEC TS 18661-1:2014.  */
#ifndef __STDC_WANT_IEC_60559_BFP_EXT__
#define __STDC_WANT_IEC_60559_BFP_EXT__ 1
#endif
/* Enable extensions specified by ISO/IEC TS 18661-2:2015.  */
#ifndef __STDC_WANT_IEC_60559_DFP_EXT__
#define __STDC_WANT_IEC_60559_DFP_EXT__ 1
#endif
/* Enable extensions specified by C23 Annex F.  */
#ifndef __STDC_WANT_IEC_60559_EXT__
#define __STDC_WANT_IEC_60559_EXT__ 1
#endif
/* Enable extensions specified by ISO/IEC TS 18661-4:2015.  */
#ifndef __STDC_WANT_IEC_60559_FUNCS_EXT__
#define __STDC_WANT_IEC_60559_FUNCS_EXT__ 1
#endif
/* Enable extensions specified by C23 Annex H and ISO/IEC TS 18661-3:2015.  */
#ifndef __STDC_WANT_IEC_60559_TYPES_EXT__
#define __STDC_WANT_IEC_60559_TYPES_EXT__ 1
#endif
/* Enable extensions specified by ISO/IEC TR 24731-2:2010.  */
#ifndef __STDC_WANT_LIB_EXT2__
#define __STDC_WANT_LIB_EXT2__ 1
#endif
/* Enable extensions specified by ISO/IEC 24747:2009.  */
#ifndef __STDC_WANT_MATH_SPEC_FUNCS__
#define __STDC_WANT_MATH_SPEC_FUNCS__ 1
#endif
/* Enable extensions on HP NonStop.  */
#ifndef _TANDEM_SOURCE
#define _TANDEM_SOURCE 1
#endif
/* Enable X/Open extensions.  Define to 500 only if necessary
   to make mbstate_t available.  */
#ifndef _XOPEN_SOURCE
/* # undef _XOPEN_SOURCE */
#endif

/* Define if processor uses big-endian word. */
/* #undef WORDS_BIGENDIAN */

/* Define to 1 if checking the stack limit is supported. */
#define ZEND_CHECK_STACK_LIMIT 1

/* Define to 1 if debugging is enabled, and to 0 if not. */
#define ZEND_DEBUG 0

/* Define to 1 if Zend fiber uses ucontext instead of boost context. */
/* #undef ZEND_FIBER_UCONTEXT */

/* Define to 1 if Zend max execution timers are supported and enabled. */
/* #undef ZEND_MAX_EXECUTION_TIMERS */

/* Number of bytes for the ZEND_MM alignment. */
#define ZEND_MM_ALIGNMENT (size_t)8

/* Number of bytes for the logarithmic ZEND_MM alignment. */
#define ZEND_MM_ALIGNMENT_LOG2 (size_t)3

/* Define to 1 if ZEND_MM needs 8-byte realignment, and to 0 if not. */
#define ZEND_MM_NEED_EIGHT_BYTE_REALIGNMENT 0

/* Define to 1 if Zend signal handling is supported and enabled. */
#define ZEND_SIGNALS 1

/* Define to 1 if thread safety (ZTS) is enabled. */
/* #undef ZTS */

/* */
/* #undef _XOPEN_SOURCE */

/* Define to 1 when using musl libc. */
/* #undef __MUSL__ */

/* Define as 'int' if <sys/types.h> doesn't define. */
/* #undef gid_t */

/* Define as 'unsigned int' if <stddef.h> doesn't define. */
/* #undef size_t */

/* Define as 'int' if <sys/types.h> doesn't define. */
/* #undef uid_t */

#include <stdlib.h>

#ifdef HAVE_SYS_TYPES_H
#include <sys/types.h>
#endif

#ifdef HAVE_SYS_SELECT_H
#include <sys/select.h>
#endif

#include <string.h>

#endif /* PHP_CONFIG_H */
