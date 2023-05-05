<?php
/**
 * Summary of console_log
 * @param mixed $data
 * @param mixed $context
 * @return void
 */
function console_log($data, $context = 'Debug in Console') {

    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}