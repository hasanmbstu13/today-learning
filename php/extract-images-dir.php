<?php
    $slider_images = array();
    $dir = $_SERVER['DOCUMENT_ROOT'].'/slider_images/';
    $slider_folder = '/slider_images/';
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if(!in_array($file,array(".",".."))){
                    $slider_images[] = $slider_folder.$file;
                }

            }
            closedir($dh);
        }
    }
    $slider_images = implode(",", $slider_images);
 ?>
