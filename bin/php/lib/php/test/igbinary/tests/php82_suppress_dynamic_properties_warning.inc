<?php
if (PHP_VERSION_ID < 80200) {
    return;
}
$totalErrors = 0;
function igbinary_suppress_dynamic_properties_warning($errno, $errstr): bool {
    if ($errno === E_DEPRECATED && str_contains($errstr, 'Creation of dynamic property')) {
        $GLOBALS['totalErrors']++;
        return true;
    }
    return false;
}
set_error_handler('igbinary_suppress_dynamic_properties_warning');

function igbinary_expect_warn_dynamic_properties(): void {
    if ($GLOBALS['totalErrors'] < 1) {
        echo "Expected this test to have at least one warning about creating dynamic properties in php 8.2\n";
    }
}
register_shutdown_function('igbinary_expect_warn_dynamic_properties');
