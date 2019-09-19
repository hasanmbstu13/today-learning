<?php
include "inc_check_session.php";
include($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// Initialise your autoloader (this example is using Composer)
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use WebPConvert\WebPConvert;

function redirect($file_name = null, $image_id = null, $msg = null, $css_class = null){
    if($image_id){
        header("location: {$file_name}.php?ImageID={$image_id}&msg={$msg}&css_class={$css_class}");
        exit;
    }else{
        header("location: {$file_name}.php?msg=$msg&css_class={$css_class}");
        exit;
    }
}
$msg = null;
function upload_banner_image($image_id = null){
    global $msg;
    $target_dir = $_SERVER['DOCUMENT_ROOT']."/slider_images/";
    $target_file = $target_dir . basename($_FILES["UFile"]["name"]);
//    var_dump($target_file); exit;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $image_info = pathinfo($target_file);
//    var_dump(pathinfo($target_file));exit;
//    $imageFileType = strtolower(pathinfo($target_file));
//    var_dump($imageFileType); exit;
// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["UFile"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $msg .= " File is not an image.";
            $uploadOk = 0;
        }
    }
// Check if file already exists
    if (file_exists($target_file)) {
        $msg .= " File already exists.";
        $uploadOk = 0;
    }
// Check file size
    if ($_FILES["UFile"]["size"] > 500000) {
        $msg .= " Your file is too large.";
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" && $imageFileType != "webp") {
        $msg .= " Only JPG, JPEG, PNG, GIF, WEBP files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $msg .= " Sorry, your file was not uploaded.";
       redirect('new_banner_image', $image_id, $msg, 'warn');
// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["UFile"]["tmp_name"], $target_file)) {
            if($imageFileType != 'webp'){
                // Save the webp image
                WebPConvert::convert($target_file, $image_info['dirname'].'/'.$image_info['filename'].'.webp');
            }
            $msg .= " The file ". basename( $_FILES["UFile"]["name"]). " has been uploaded.";
            return $image_info;
        } else {
            $msg .= "Sorry, there was an error uploading your file.";
            redirect('new_banner_image', $image_id, $msg, 'warn');
        }
    }

}
$image_id = isset($_POST['ImageID']) ? $_POST['ImageID'] : '';
$SortKey = isset($_POST['SortKey']) ? $_POST['SortKey'] : '';
$title= isset($_POST['title']) ? str_replace('"',"&#34;",str_replace("'","&#39;",str_replace("'","&#39;",$_REQUEST['title']))) : '';

if($image_id){
    $image_info = upload_banner_image($image_id);
    $image_name = isset($image_info['filename']) ? $image_info['filename'] : '';
    $image_type = isset($image_info['extension']) ? strtolower($image_info['extension']) : '';
    $sql = "SELECT * FROM HomepageSliderImages WHERE ImageID={$image_id}";
    $rs1 = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_array($rs1);
    if($row['image_type'] != 'webp'){
        unlink($_SERVER['DOCUMENT_ROOT']."/slider_images/".$row['Image'].".".$row['image_type']);
        unlink($_SERVER['DOCUMENT_ROOT']."/slider_images/".$row['Image'].".webp");
    }else{
        unlink($_SERVER['DOCUMENT_ROOT']."/slider_images/".$row['Image'].".webp");
    }
    $sql = "UPDATE HomepageSliderImages SET Image='$image_name',title='$title',image_type='$image_type',SortKey={$SortKey} WHERE ImageID={$image_id}";
    $rs=mysql_query($sql) or die (mysql_error());
    $msg .= " Updated Successfully.";
    redirect('new_banner_image', $image_id, $msg, 'upl_success');
}else{
    $image_info = upload_banner_image();
    $image_name = isset($image_info['filename']) ? $image_info['filename'] : '';
    $image_type = isset($image_info['extension']) ? strtolower($image_info['extension']) : '';
    $sql = "INSERT INTO HomepageSliderImages(title,Image,image_type,SortKey) VALUES ('$title','$image_name','$image_type',$SortKey)";
    $rs=mysql_query($sql) or die (mysql_error());
    $msg .= " Added Successfully.";
    redirect('banner_slider', null, $msg, 'upl_success');
}