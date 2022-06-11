<?php
// replace special symbol
function replaceStr($str){
    $str=str_replace("'","&apos;",$str);
    $str=str_replace('"','&quot;',$str);
    return $str;
}
?>

<?php

session_start();
$sid=$_SESSION['SID'];
$uName=$_SESSION['name'];
$sts=$_REQUEST['status'];

$host = 'localhost';
$port=3306;
$dbusername ='root';
$dbpassword = '';
$dbname = 'test'; 
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $dbusername, '');
} catch (PDOException $e) {
    echo "Conn_Failed";
}

$sql="
SELECT *,o.category as ocat
FROM orders as o
INNER JOIN store as s
ON o.SID=s.SID
WHERE o.SID=$sid
ORDER BY o.OID
";
$stmt=$conn->prepare($sql);
$stmt->execute();

// Return current date from the remote server
date_default_timezone_set("Asia/Taipei");
$date = date("Y/m/d h:i:s");

echo <<<EOT
<style type="text/css">
form{margin:0px; display:inline}
</style>

<form action="myOrder_FinishAll.py" method="post" id="sendAll_ForC_myOdr">
  <input type="hidden" name="myOdrTime" value="$date">
  <input type="hidden" name="uName" value="$uName">
  <input type="submit" value="Finish Selected Order" class="btn btn-primary" style='margin-top: 20px;'>
  <input type="submit" formaction="myOrder_CancelAll.py" value="Cancel Selected Order" class="btn btn-danger" style='margin-top: 20px;'>
</form>


<!--
<button class="btn btn-primary" style='margin-top: 20px;' onclick='finishAll_myOdr()'>Finish Selected Order</button>
<form action="myOrder_FinishAll.py" method="post" id="sendAll_FmyOdr"></form>
<input type="hidden" name="myOdrTime" value="0" form="sendAll_FmyOdr">
<input type="hidden" name="uName" value="$uName" form="sendAll_FmyOdr">

<button class="btn btn-danger" style='margin-top: 20px;' onclick='cancelAll_myOdr()'>Cancel Selected Order</button>
<form action="myOrder_CancelAll.py" method="post" id="sendAll_myOdr"></form>
<input type="hidden" name="myOdrTime" value="0" form="sendAll_myOdr">
<input type="hidden" name="uName" value="$uName" form="sendAll_myOdr">
-->
EOT;

echo<<<EOT
<table class="table" style="margin-top: 10px; margin-bottom: 0px;">
    <thead>
    <tr>
        <th scope="col"></th>
        <th scope="col">Order ID</th>
        <th scope="col">Status</th>
        <th scope="col">Start</th>
        <th scope="col">End</th>
        <th scope="col">Shop Name</th>
        <th scope="col">Total Price</th>
        <th scope="col">Order Details</th>
        <th scope="col">Action</th>
    </tr>
    </thead>
EOT;
echo '<tbody>';
$odrs=array();
while ($odr=$stmt->fetch()) {
    $oid=$odr['OID'];
    $oSts=($odr['status']==0)? 'Not Finished': (($odr['status']==1)? 'Finished': 'Cancel');
    if($sts=='All'||$oSts==$sts){
        $oCtgy=($odr['ocat'])?1:0;
        $oStart=$odr['start'];
        $oEnd=$odr['end'];
        $oSName=$odr['name'];
        $oAmnt=$odr['amount'];
        $oDis=$odr['distance'];

        $tmp=array(
            'OID'=>$oid,
            'ctgry'=>$oCtgy,
            'dis'=>$oDis,
            'amnt'=>$oAmnt
        );
        // array_push($odrs,$tmp);
        $odrs[$oid]=$tmp;

        echo '<tr>';
        if($oSts=='Not Finished') echo '<td><input type="checkbox" value="'.$oid.'" form="sendAll_ForC_myOdr" name="chkBox"></td>';
        else echo '<td></td>';
        echo<<<EOT
            <th scope='row'>$oid</th>
            <td>$oSts</td>
            <td>$oStart</td>
            <td>$oEnd</td>
            <td>$oSName</td>
            <td>$oAmnt</td>
            <td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#s_o$oid'>Order Details</button></td>
        EOT;
        echo '<td>';
        if($oSts=='Not Finished'){
            echo<<<EOT
                <form action='finishOrder.py' method='post' id='sendOneF_shOdr_o$oid'></form>
                <input type="hidden" name="OID" value="$oid" form='sendOneF_shOdr_o$oid'>
                <input type="hidden" name="uName" value='$uName' form='sendOneF_shOdr_o$oid'>
                <input type="hidden" name="myOdrTime" value='0' form='sendOneF_shOdr_o$oid'>
                <input type="submit" class="btn btn-success" value="Done" onclick='getCurrTime()' form='sendOneF_shOdr_o$oid'/>
            
                <form action='cancelOrder.py' method='post' id='sendOneC_shOdr_o$oid'></form>
                <input type="hidden" name="OID" value="$oid" form='sendOneC_shOdr_o$oid'>
                <input type="hidden" name="uName" value='$uName' form='sendOneC_shOdr_o$oid'>
                <input type="hidden" name="myOdrTime" value='0' form='sendOneC_shOdr_o$oid'>
                <input type="submit" class="btn btn-danger" value="Cancel" onclick='getCurrTime()' form='sendOneC_shOdr_o$oid'/>   
            
            EOT;
        }
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';

foreach($odrs as $o){
    $oid=$o['OID'];
    $oCat=$o['ctgry'];

    $sql="
    SELECT *
    FROM orders as o
    INNER JOIN content as c
    ON o.OID=c.OID
    INNER JOIN product as p
    ON c.PID=p.PID
    WHERE o.OID=$oid;
    ";
    $stmt=$conn->prepare($sql);
    $stmt->execute();

    echo<<<EOT
    <div class='modal fade' id='s_o$oid'  data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Order</h4>
            </div>
            <div class="modal-body">
                <!--  -->
                <div class="row">
                    <div class="  col-xs-12">
                        <table class="table" style=" margin-top: 15px;">
                            <thead>
                                <tr>
                                <th scope="col">Picture</th>
                                <th scope="col">meal name</th>
                                <th scope="col">price</th>
                                <th scope="col">Quantity</th>
                                </tr>
                            </thead>
    EOT;
    while ($prod=$stmt->fetch()){
        $pPic=$prod['picture'];
        $pName=replaceStr($prod['name']);
        $pPrice=$prod['price'];
        $pAmnt=$prod['amount'];
        $img_type=$prod['imgType'];
    echo<<<EOT
                            <tbody>
                                <td><img src="data:$img_type;base64,$pPic"/></td>
                                <td>$pName</td>
                                <td>$pPrice</td>
                                <td>$pAmnt</td>
                            </tbody>
                        
    EOT;
    }
    echo<<<EOT
                        </table>
                    </div>
                </div>
                <!--  -->
            </div>
    EOT;
            echo '<div class="modal-footer">';
            $deliFee=($oCat=='1')? ( (round($o['dis']*10)>=10)? round($o['dis']*10) : 10 ): 0;
            echo '<p><big>Subtotal $'.($o['amnt']-$deliFee).'</big></p>';
            echo '<p><small>Delivery fee $'.($deliFee).'</small></p>';
            echo '<p><big>Total Price $'.($o['amnt']).'</big></p>';
            
            echo '</div>';

    echo<<<EOT
        </div>
    </div>
    </div>
    EOT;
}
?>