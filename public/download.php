<?php
    $path     = $_GET['path'] ;
    $filename = $_GET['file'] ;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path . '/' . $filename));
    readfile($path . '/' . $filename);
    exit;