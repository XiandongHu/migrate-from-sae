<?php

function traceHttp() {
    $content = date('Y-m-d H:i:s')."\nREMOTE_ADDR:".$_SERVER["REMOTE_ADDR"]."\nQUERY_STRING:".$_SERVER["QUERY_STRING"]."\n";
    logger($content);
}

function logger($message) {
    if (isset($_SERVER['HTTP_APPNAME'])) {  //SAE
        sae_set_display_errors(false);
        sae_debug(trim($message));
        sae_set_display_errors(true);
    } else {
        $max_size = 10000;
        $log_filename = "log.xml";
        if (file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)) {
            unlink($log_filename);
        }
        file_put_contents($log_filename, $message, FILE_APPEND);
    }
}

?>
