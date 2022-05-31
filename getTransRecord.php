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
$uid=$_SESSION['UID'];
$actn=$_REQUEST['action'];

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
SELECT *
FROM transaction
WHERE UID=$uid
";
$stmt=$conn->prepare($sql);
$stmt->execute();

echo<<<EOT
<table class="table" style="margin-top: 10px; margin-bottom: 0px;">
    <thead>
    <tr>
        <th scope="col">Record ID</th>
        <th scope="col">Action</th>
        <th scope="col">Time</th>
        <th scope="col">Trader</th>
        <th scope="col">Amount Change</th>
    </tr>
    </thead>
EOT;
echo '<tbody>';
while ($rcrd=$stmt->fetch()) {
    $tid=$rcrd['TID'];
    // $tActn=($rcrd['action']==-1)? 'Payment': (($rcrd['action']==1)? 'Receive': 'Recharge');
    $tActn='Recharge';
    switch($rcrd['action']){
        case -1:
            $tActn='Payment';
            break;
        case 1:
            $tActn='Receive';
            break;
        case 2:
            $tActn='Refund';
        default:
            break;
    }
    if($actn=='All'||$tActn==$actn){
        $tTime=$rcrd['time'];
        $tTradr=replaceStr($rcrd['trader']);
        $tChng=($rcrd['amount']>0)? '+'.$rcrd['amount'] : $rcrd['amount'];

        echo<<<EOT
        <tr>
            <th scope='row'>$tid</th>
            <td>$tActn</td>
            <td>$tTime</td>
            <td>$tTradr</td>
            <td>$tChng</td>
        </tr>
        EOT;
    }
}
echo '</tbody>';
echo '</table>';
?>
