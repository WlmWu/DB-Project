<?php
// replace special symbol
function replaceStr($str){
    $str=str_replace("'","&apos;",$str);
    $str=str_replace('"','&quot;',$str);
    return $str;
}
?>

<?php
if($_FILES["file"]["size"]!=0){
    $file_name=$_FILES["file"]["tmp_name"];
    // resize image
    $maxDim = 100;
    $file_name = $_FILES['file']['tmp_name'];
    list($width, $height, $type, $attr) = getimagesize( $file_name );
    if ( $width > $maxDim || $height > $maxDim ) {
        $target_filename = $file_name;
        $ratio = $width/$height;
        if( $ratio > 1) {
            $new_width = $maxDim;
            $new_height = $maxDim/$ratio;
        } else {
            $new_width = $maxDim*$ratio;
            $new_height = $maxDim;
        }
        $src = imagecreatefromstring( file_get_contents( $file_name ) );
        $dst = imagecreatetruecolor( $new_width, $new_height );
        imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        imagedestroy( $src );
        imagepng( $dst, $target_filename ); // adjust format as needed
        imagedestroy( $dst );
    }

    $file = fopen($file_name, "rb");
    //讀入圖片檔資料
    $fileContents = fread($file, filesize($file_name)); 
    //關閉圖片檔
    fclose($file);
    //讀取出來的圖片資料必須使用base64_encode()函數加以編碼：圖片檔案資料編碼
    $fileContents = base64_encode($fileContents);

                                    
    // echo '<img src="data:'.$img_type.';base64,' . $fileContents . '" />';

    $sid = $_REQUEST['SID'];
    $name=replaceStr($_REQUEST['mealname']);
    $price=$_REQUEST['price'];
    $quant=$_REQUEST['quantity'];
    $img_type=$_FILES["file"]["type"];
    // echo 'name: '.$name;
    
    $formName=sprintf("<input type='hidden' name='mealname' value='%s'>",$name);
    $formImg=sprintf("<input type='hidden' name='file' value='%s'>",$fileContents);
    $formType=sprintf("<input type='hidden' name='imgType' value='%s'>",$img_type);
    
    echo<<<EOT
    <form action="add_item.py" method="post">
        <input type='hidden' name='SID' value='$sid'>
        <input type='hidden' name='mealname' value='$name'>
        <input type='hidden' name='price' value='$price'>
        <input type='hidden' name='quantity' value='$quant'>
        <input type='hidden' name='file' value='$fileContents'>
        <input type='hidden' name='imgType' value='$img_type'>
    </form>
    <script>
        document.getElementsByTagName('form')[0].submit();
    </script>
    EOT;
}else{
    echo<<<EOT
        <script>
            window.location.href="nav.php"
            window.alert("Image is empty")
        </script>
    EOT;
}
?>