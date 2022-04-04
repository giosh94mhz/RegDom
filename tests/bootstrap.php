<?php
require_once 'vendor/autoload.php';

foreach (scandir(__DIR__.'../data/') as $filename) {
    if (preg_match('/^cached_/', $filename)) {
        unlink($filename);
    }
}
