<?php
session_start();
if(!isset($_SESSION['account'])){
    $_SESSION['account']=$_REQUEST["account"];
    $_SESSION['Authenticated']=True;
}
$acnt=$_SESSION["account"];
if(empty($_SESSION['account'])){
  include("logout.php");
}
?>

<?php

// conn DB
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

// check new location
if(isset($_REQUEST["edLat"]) && isset($_REQUEST["edLon"])){
    if($_REQUEST["edLon"]<=180 && $_REQUEST["edLon"]>=-180 && $_REQUEST["edLat"]<=90 && $_REQUEST["edLat"]>=-90){
        $sql = "UPDATE user SET location=ST_GeomFromText('POINT(".$_REQUEST['edLon'].' '.$_REQUEST['edLat'].")') WHERE account='$acnt'";
        $stmt=$conn->prepare($sql);
        $stmt->execute();
    }
}

// get user info
$sql = "SELECT * ,ST_AsText(location) AS txtLoc FROM user WHERE account= '$acnt'";
$stmt=$conn->prepare($sql);
$stmt->execute();
$data=$stmt->fetch();
$_SESSION['name']=$data["name"];
$_SESSION['phone']=$data["phone"];
$_SESSION['UID']=$data["UID"];
$_SESSION['wallet']=$data["wallet"];
if ($data["role"]){
    $_SESSION['role']=1;
}
else{
    $_SESSION['role']=0;
}

$pattern = "/[0-9]+[.]*[0-9]* [0-9]+[.]*[0-9]*/";
preg_match($pattern, $data["txtLoc"], $mth);
$location = explode(' ', $mth[0]);
$_SESSION['longitude']=$location[0];
$_SESSION['latitude']=$location[1];

$UID=$_SESSION["UID"];
$name=$_SESSION["name"];
$pho=$_SESSION["phone"];
$lon=$_SESSION["longitude"];
$lat=$_SESSION["latitude"];
$role=$_SESSION["role"];
$walt=$_SESSION["wallet"];

		
//get shop info		
if ($data["role"]){		
    $sql = "SELECT * ,ST_AsText(location) AS txtLoc FROM store WHERE UID='$UID'";		
    $stmt=$conn->prepare($sql);		
    $stmt->execute();		
    $data=$stmt->fetch();		
    $_SESSION['SID']=$data["SID"];	
    $_SESSION['shop_name']=$data["name"];		
    $_SESSION['category']=$data["category"];		
    $pattern = "/[0-9]+[.]*[0-9]* [0-9]+[.]*[0-9]*/";		
    preg_match($pattern, $data["txtLoc"], $mth);		
    $location = explode(' ', $mth[0]);		
    $_SESSION['shop_longitude']=$location[0];		
    $_SESSION['shop_latitude']=$location[1];		
    $SID=$_SESSION['SID'];		
}
?>

<?php 

// search stores
$srhShop=array();
$srhShopId=array();

if(isset($_REQUEST['srhShopId'])){
    foreach($_REQUEST['srhShopId'] as $s){
        array_push($srhShopId,$s);
    }
    // $srhShop[SID]['name' or 'categ']
    for($i=0;$i<count($srhShopId);$i++){
        $tmp['tmp']=array(
            'SID'=>$_REQUEST['srhShopId'][$i],
            'name'=>$_REQUEST['srhShopName'][$i],
            'categ'=>$_REQUEST['srhShopCat'][$i],
            'dis'=>$_REQUEST['srhShopDis'][$i],
            'realDis'=>round($_REQUEST['srhShopRealDis'][$i])/1000  // km
        );
        $srhShop[$i] = $tmp['tmp'];
    }
    $_SESSION["Shops"]=$srhShop;
}else{
    $srhShop=$_SESSION["Shops"];
}

?>

<?php

// get search page
$pg=1;
if(isset($_REQUEST["page"])){
    $pg=$_REQUEST["page"];
}

?>

<?php
// replace special symbol
function replaceStr($str){
    $str=str_replace("'","&apos;",$str);
    $str=str_replace('"','&quot;',$str);
    return $str;
}
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <title>Hello, world!</title>
</head>

<body>
 
  <nav class="navbar navbar-inverse">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand " href="#">WebSiteName</a>
      </div>
      <a href="logout.php">
      <button type="button " style="position:absolute;top:12px; right:10px;" class=" btn btn-info " >Logout</button>
      </a>
    </div>
    
  </nav>
  <div class="container">

    <ul class="nav nav-tabs">
      <li class="active"><a href="#home">Home</a></li>
      <li><a href="#menu1" onclick="dis_AddProduct()">shop</a></li>
      <li><a href="#myOrder" onclick="getMyOdr()">My Order</a></li>
      <li><a href="#shopOrder" onclick="getShopOdr()">Shop Order</a></li>
      <li><a href="#transRecord" onclick="getTrnsRc()">Transaction Record</a></li>
    </ul>

    <div class="tab-content">
      <div id="home" class="tab-pane fade in active">
        <h3>Profile</h3>
        <div class="row">
          <div class="col-xs-12">
            Account: <?php echo $acnt; ?>, Name: <?php echo $name; ?>, Role: <?php echo ($role)?'Manager':'User';?>, PhoneNumber: <?php echo $pho; ?>,  location: <?php echo $lon; ?>, <?php echo $lat; ?>
            
            <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal"
            data-target="#location">edit location</button>
            <!--  -->
            <!-- <form action="sign-up.html" method="post"> -->
            <div class="modal fade" id="location"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
              <div class="modal-dialog  modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">edit location</h4>
                  </div>
                  <div class="modal-body">
                    <label class="control-label " for="latitude">latitude</label>
                    <input type="text" class="form-control" id="latitude" placeholder="enter latitude">
                      <br>
                      <label class="control-label " for="longitude">longitude</label>
                    <input type="text" class="form-control" id="longitude" placeholder="enter longitude">
                  </div>
                  <div class="modal-footer">
                    <!-- <a href="index.html?edit=true"> -->
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="EdLoca()">Edit</button>
                    <!-- <input type="submit" class="btn btn-default" data-dismiss="modal" value="Edit"></button> -->
                    <!-- </a> -->
                  </div>
                </div>
              </div>
            </div>
            <!-- </form> -->
            <script>
                function EdLoca(){
                    edLat=document.getElementById('latitude').value;
                    edLon=document.getElementById('longitude').value;
                    if(edLat!="" && edLon!=""){
                      const pattern = /^[0-9]+[.]*[0-9]*$/;
                      if(pattern.exec(edLat)&&pattern.exec(edLon)){
                        mthLat=parseFloat(pattern.exec(edLat));
                        mthLon=parseFloat(pattern.exec(edLon));
                        if(mthLon<=180 && mthLon>=-180 && mthLat<=90 && mthLat>=-90){
                            window.location.href="nav.php?"+"&edLat="+mthLat+"&edLon="+mthLon;
                        }
                      }
                    }
                }
            </script>

            <!--  -->
            walletbalance: <?php echo $walt; ?>
            <!-- Modal -->
            <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal"
              data-target="#myModal">Add value</button>
            <div class="modal fade" id="myModal"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
              <div class="modal-dialog  modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add value</h4>
                  </div>
                  <div class="modal-body">
                    <input type="text" class="form-control" id="value" placeholder="enter add value">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="EdWalt()">Add</button>
                  </div>
                </div>
              </div>
            </div>
            <script>
                function EdWalt(){
                    edVal=document.getElementById('value').value;
                    if(edVal!=""){
                      const pattern = /^[1-9][0-9]*$/;
                      if((mthVal=pattern.exec(edVal))){
                        mthVal=parseInt(mthVal);
                        if(mthVal>0){
                            edWalt_post(mthVal);
                        }
                      }else{
                        window.alert('Value should be a positive integer.');
                      }
                    }
                }
                function edWalt_post(value) {
                  var uid=<?php echo $UID?>;
                  var name=<?php echo '"'.$name.'"'?>;
                  // console.log(uid);
                  jQuery.ajax({
                    url: "walt_edit.py",
                    data:{
                      uid: uid,
                      name: name,
                      value: value,
                      time: getTime()
                    },
                    type: "POST",
                    success: function(data) {
                      window.location.href="nav.php";
                      // console.log(data)
                    },
                    error: function() {
                      console.log('ERROR')
                    }
                  });
                }

                function getTime(){
                  t = new Date();
                  Y=t.getFullYear();
                  M=t.getMonth()+1;M=(M<10)?'0'+M:M;
                  D=t.getDate();D=(D<10)?'0'+D:D;
                  h=t.getHours();h=(h<10)?'0'+h:h;
                  m=t.getMinutes();m=(m<10)?'0'+m:m;
                  s=t.getSeconds();s=(s<10)?'0'+s:s;

                  tStr = Y+'/'+M+'/'+D+' '+h+':'+m+':'+s;
                  console.log(tStr);
                  return tStr;
                }

                function getCurrTime(){
                  btns=document.getElementsByName('myOdrTime')
                  tStr=getTime();
                  for(var i=0;i<btns.length;i++){
                    btns[i].value=tStr;
                  }
                  // console.log(btns)
                }
            </script>
          </div>

        </div>

        <!-- 
                
             -->
        <h3>Search</h3>
        <div class=" row  col-xs-8">
          <form class="form-horizontal" action="search.py" method="post">
            <div class="form-group">
              <label class="control-label col-sm-1" for="Shop">Shop</label>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="shopName" placeholder="Enter Shop name">
              </div>
              <label class="control-label col-sm-1" for="distance">distance</label>
              <div class="col-sm-5">


                <select class="form-control" id="sel1" name="dist">
                  <option>near</option>
                  <option>medium </option>
                  <option>far</option>
                  <option>all</option>

                </select>
              </div>

            </div>

            <div class="form-group">

              <label class="control-label col-sm-1" for="Price">Price</label>
              <div class="col-sm-2">

                <input type="text" class="form-control" name="PriLow">

              </div>
              <label class="control-label col-sm-1" for="~">~</label>
              <div class="col-sm-2">

                <input type="text" class="form-control" name="PriHigh"> 

              </div>
              <label class="control-label col-sm-1" for="Meal">Meal</label>
              <div class="col-sm-5">
                <input type="text" list="Meals" class="form-control" id="Meal" placeholder="Enter Meal" name="Meal">
                <datalist id="Meals">
                  <option value="Hamburger">
                  <option value="coffee">
                </datalist>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-sm-1" for="category"> category</label>
            
              
                <div class="col-sm-5">
                  <input type="text" list="categorys" class="form-control" id="category" placeholder="Enter shop category" name="categ">
                  <datalist id="categorys">
                    <option value="fast food">
               
                  </datalist>
                </div>
                <?php
                    echo "<input type='hidden' name='longitude' value='".$lon."'>";
                    echo "<input type='hidden' name='latitude' value='".$lat."'>";
                ?>
            
              <label class="control-label col-sm-1" for="order">Order by</label>
              <div class="col-sm-5">
                <select class="form-control" id="sel2" name="sort">
                  <option>distance</option>
                  <option>name</option>
                  <option>category </option>
                </select>
                <select class="form-control" style="margin-top: 5px;" id="sel3" name="order">
                  <option>ascending</option>
                  <option>descending</option>
                </select>
              </div><br>

                <button type="submit" style="margin: 18px;"class="btn btn-primary">Search</button>
              
            </div>
          </form>
        </div>
        <div class="row">
          <div class="  col-xs-8">
            <table class="table" style="margin-top: 10px; margin-bottom: 0px;">
              <thead>
                <tr>
                  <th scope="col">#</th>
                
                  <th scope="col">shop name</th>
                  <th scope="col">shop category</th>
                  <th scope="col">Distance</th>
               
                </tr>
              </thead>
              <tbody>
                <?php
                if(isset($srhShop)){
                    for($i=($pg-1)*5;$i<count($srhShop) && $i<$pg*5;$i++){
                        echo "<tr>";
                        echo "<th scope='row'>".($i+1)."</th>";
                        echo "<td>".replaceStr($srhShop[$i]["name"])."</td>";
                        echo "<td>".replaceStr($srhShop[$i]["categ"])."</td>";
                        echo "<td>".$srhShop[$i]["dis"]."</td>";
                        echo "<td>  <button type='button' class='btn btn-info' data-toggle='modal' data-target='#s".$srhShop[$i]['SID']."'>Open menu</button></td>";
                        echo "</tr>";
                    }
                }
                ?>
              </tbody>
            </table>

            <ul class="pagination pagination-lg" style="margin-bottom: 100px">
            
            <?php
            if(isset($srhShop)){
                if($pg>1){
                    echo "<li><a href='nav.php?&page=".($pg-1)."'>&laquo;</a></li>";
                }else{
                    echo "<li><a href='#'>&laquo;</a></li>";
                }
                
                for($i=0;$i<ceil(count($srhShop)/5);$i++){
                    echo "<li><a href='nav.php?&page=".($i+1)."'>".($i+1)."</a></li>";
                }

                if($pg<ceil(count($srhShop)/5)){
                    echo "<li><a href='nav.php?&page=".($pg+1)."'>&raquo;</a></li>";
                }else{
                    echo "<li><a href='#'>&raquo;</a></li>";
                }
            }
            ?>
            </ul><br>

    <?php
    function getProducts($sid,$conn){
        $menu=array();
        try{
            $sql = "SELECT * FROM product WHERE SID='$sid'";
            $stmt=$conn->prepare($sql);
            $stmt->execute();
            $chk = $stmt->setFetchMode(PDO::FETCH_ASSOC);
            while(!empty($row=$stmt->fetch())){
                $tmp=array(
                    'PID'=>$row['PID'],
                    'pic'=>$row['picture'],
                    'name'=>$row['name'],
                    'price'=>$row['price'],
                    'quant'=>$row['quantity']
                );
                array_push($menu,$tmp);
            }
            
        }catch(PDOException $e){
            echo 'Error';
        }
        return $menu;
    }
    ?>

<?php 

if(isset($srhShop)){
    $rowCnt=0;
    foreach($srhShop as $shop){
        $rowCnt++;
        $sid=$shop['SID'];
        $sDis=$shop['realDis'];
        $sName=replaceStr($shop['name']);
        echo<<<EOT
        <div class='modal fade' id='s$sid'  data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
        <div class="modal-dialog">

        <!-- Modal content-->
        <form action='makeOrder.py' method='post'>
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">menu</h4>
            </div>
            <div class="modal-body">
            <!--  -->

            <div class="row">
            <div class="  col-xs-12">
                <table class="table" style=" margin-top: 15px;">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Picture</th>
                    
                    <th scope="col">meal name</th>
                
                    <th scope="col">price</th>
                    <th scope="col">Quantity</th>
                    
                    <th scope="col">Order check</th>
                    </tr>
                </thead>
        EOT;
        
        $menu=getProducts($shop['SID'],$conn);
        echo "<tbody>";
    
        $rowCnt=0;
        foreach($menu as $m){
            $rowCnt++;
            $mPID=$m['PID'];
            $mPic=$m['pic'];
            $mName=replaceStr($m['name']);
            $mPric=$m['price'];
            $mQuan=$m['quant'];
            $uniqFName='S'.$sid.'_M'.$mPID;
            echo<<<EOT
                <tr>
                    <th scope='row'>$rowCnt</th>
                    <td><img src="data:$img_type;base64,$mPic"/></td>
                    <td>$mName</td>
                    <td>$mPric</td>
                    <td>$mQuan</td>
                    <td><input type="button" name="minus$sid" value="-" onclick="minusItem$uniqFName()"/> <input style="width: 25px" type="text" name="mQuan" id="Quan_$uniqFName" value="0" readonly/> <input type="button" name="plus$sid" value="+" onclick="plusItem$uniqFName()"/></td>
                </tr>
            EOT;
            echo "<input type='hidden' name='mPID' value='$mPID'/>";
            echo<<<EOT
            <script>
            function plusItem$uniqFName(){
              val=parseInt(document.getElementById('Quan_$uniqFName').value);
              calcu$sid($mPric,1);
              val+=1;
              document.getElementById('Quan_$uniqFName').value=val;
            }
            function minusItem$uniqFName(){
              val=parseInt(document.getElementById('Quan_$uniqFName').value);
              calcu$sid($mPric,((val<=0)?0:-1));
              val=(val<=0)?0:val-1;
              document.getElementById('Quan_$uniqFName').value=val;
            }
            </script>
            EOT;
        }
        
        echo"</tbody>";

        echo<<<EOT
                </table>
                </form>
            </div>

            </div>

            <label class="control-label col-sm-1" for="status" style="margin-top:25px">Type</label>
            <div class="col-sm-4" style="margin-top:20px">
              <select class="form-control" id="mType$sid">
                <option>Delivery</option>
                <option>Take Out</option>
              </select>
            </div>

            <!--  -->
            </div>
            <div class="modal-footer" id="mfooter$sid">
              <input type="button" class="btn btn-default" onclick="calAmnt$sid()" value='Calculate The Price' />
            </div>
            <div class="modal-footer" id="mCal$sid" style="visibility:hidden">
              <input id="mfooterCal$sid" type="hidden" value='0'/>
              <input id="mfooterTTAmnt$sid" name="amnt" type="hidden" value='0'/>
              <input id="mfooterDis$sid" name='dis' type="hidden" value='0'/>
              <input id="mTime$sid" name='time' type="hidden" value='0'/>
              <input id="mCat$sid" name='ctgry' type="hidden" value='0'/>
              <input name='SID' type="hidden" value='$sid'/>
              <input name='UID' type="hidden" value='$UID'/>
              <input name='walt' type="hidden" value='$walt'/>
              <input name='sName' type="hidden" value='$sName'/>
              <input name='name' type="hidden" value='$name'/>


              <big><p id="mSub$sid"></p></big>
              <small><p id="mDeli$sid" type="text"></p></small>
              <big><p id="mTotal$sid"></p></big>
              <input type="submit" class="btn btn-default" onclick="calAmnt$sid()" value='Order' />
            </div>
        </div>
        </form>

        </div>
        </div>
        EOT;


        echo<<<EOT
        <script>
        function calcu$sid(price,quant){
          var amnt=parseInt(document.getElementById('mfooterCal$sid').value);
          amnt+=price*quant;
          document.getElementById('mfooterCal$sid').value=amnt;
        }

        function calAmnt$sid(){
          var pAmnt=parseInt(document.getElementById('mfooterCal$sid').value);
          if(pAmnt==0){
            alert('Add foods into cart first.')
            return;
          }
          var type=document.getElementById('mType$sid').value;
          type=(type=="Delivery")?1:0;
          var dis=(type)?parseFloat($sDis):0;
          var deliFee=(type)?Math.round(dis*10):0;
          deliFee=(deliFee>=0 && deliFee<10 && type)?10:deliFee;
          var ttAmnt=pAmnt+deliFee;

          // show values on window
          document.getElementById('mSub$sid').innerHTML="Subtotal $"+pAmnt;
          document.getElementById('mDeli$sid').innerHTML="Delivery Fee $"+deliFee;
          document.getElementById('mTotal$sid').innerHTML="Total Price $"+ttAmnt;
          
          document.getElementById('mCal$sid').style.visibility = 'visible';
          document.getElementById('mfooter$sid').style.visibility = 'hidden';
          document.getElementById('mfooter$sid').style.visibility = 'hidden';
          document.getElementById('mType$sid').setAttribute('disabled',true);

          var plus=document.getElementsByName('plus$sid');
          var minus=document.getElementsByName('minus$sid')
          for(var i=0;i<plus.length;i++){
            plus[i].type='hidden';
            minus[i].type='hidden';
          }

          // hidden post
          document.getElementById('mfooterDis$sid').value=dis;
          document.getElementById('mfooterTTAmnt$sid').value=ttAmnt;
          document.getElementById('mTime$sid').value=getTime();
          document.getElementById('mCat$sid').value=type;

        }

        </script>
        EOT;
    }
}
?>
</div>

        </div>
      </div>
      <div id="menu1" class="tab-pane fade">

        <h3> Start a business </h3>
        <form class="form-horizontal" action="shop_reg.py" method="post">
          <div class="form-group ">
            
          <input type="hidden" name="UID" value="<?php echo $UID;?>"> 
            
            <div class="row">
              <div class="col-xs-2">
                <label for="shopname">Shop name: </label>
                <?php 
                if ($_SESSION['role']) {
                    $shopName=replaceStr($_SESSION['shop_name']);
                    echo <<<EOT
                        <input class="form-control" value="$shopName" disabled>
                        <script>
                          #document.getElementById("shopName").value="$shopName";
                        </script>
                    EOT;
                }else{ 
                    echo<<<LABEL
                    <input class="form-control" id="shopname" name="shopname" placeholder="macdonald" type="text" oninput="checky()">
                    <span id="check"></span>
                    LABEL;
                }
                ?>
              </div>
              <div class="col-xs-2">
                <label for="shopcategory">Shop category: </label>
                <?php 
                if ($_SESSION['role']) {
                    $shopCat=replaceStr($_SESSION['category']);
                    echo <<<EOT
                        <input class="form-control" value="$shopCat" disabled>
                    EOT;
                } else echo '<input class="form-control" id="shopcategory" name="shopcategory" placeholder="fast food" type="text" >';
                ?>
                </div>
              <div class="col-xs-2">
                <label for="latitude">Latitude: </label>
                <?php if ($_SESSION['role']) {
                  echo "<input class='form-control' value='".$_SESSION['shop_latitude']."' disabled>";
                } else echo '<input class="form-control" id="latitude" name="latitude" placeholder="24.78472733371133" type="text" >'
                ?>
                </div>
              <div class="col-xs-2">
                <label for="longitude">Longitude: </label>
                <?php if ($_SESSION['role']) {
                  echo "<input class='form-control' value='".$_SESSION['shop_longitude']."' disabled>";
                } else echo '<input class="form-control" id="longitude" name="longitude" placeholder="121.00028167648875" type="text" >'
                ?>
                </div>
            </div>
          </div>

          <div class=" row" style=" margin-top: 25px;">
            <div class=" col-xs-3">
              <button type="submit" class="btn btn-primary"  <?php echo ($_SESSION["role"]) ? 'disabled': '' ?>>register</button>
            </div>
          </div>
        </form>
        <script type="text/javascript">
            function checky() {
                jQuery.ajax({
                    url: "checkShopName.py",
                    data: 'shopname=' + $("#shopname").val(),
                    type: "POST",
                    success: function(data) {
                        document.getElementById("check").innerHTML = data;
                    },
                    error: function() {
                        console.log('ERROR')
                    }
                });
            }
        </script>

        <hr>
        <h3>ADD</h3>
        <form class="form-horizontal" enctype="multipart/form-data" action="add_item.php" method="post">
          <div class="form-group ">
          <?php if ($_SESSION['role']){
            echo<<<LABEL
            <input type="hidden" name="SID" value= {$_SESSION['SID']}>
            LABEL;
          } ?>
            <div class="row">
              <div class="col-xs-6">
                <label for="mealname">meal name</label>
                <input class="form-control" id="mealname" name="mealname" type="text">
              </div>
            </div>
            <div class="row" style=" margin-top: 15px;">
              <div class="col-xs-3">
                <label for="price">price</label>
                <input class="form-control" id="price" name="price" type="text">
              </div>
              <div class="col-xs-3">
                <label for="quantity">quantity</label>
                <input class="form-control" id="quantity" name="quantity" type="text">
              </div>
            </div>

            <div class="row" style=" margin-top: 25px;">

              <div class=" col-xs-3">
                <label for="picture">上傳圖片</label>
                <input multiple class="file-loading" id="picture" name="file" type="file">
                <?php

                ?>
              </div>
              <div class=" col-xs-3">
                <button id="addProduct" type="submit" class="btn btn-primary" style=" margin-top: 15px;" value="upload">Add</button>
                <script>
                  function dis_AddProduct(){
                    var role=parseInt(<?php echo $role; ?>);
                    if(!role){
                      document.getElementById('addProduct').setAttribute('disabled',true);
                    }
                  }
                </script>
              </div>
           </div>
          </div>
        </form>

        <div class="row">
          <div class="  col-xs-8">
            <table class="table" style=" margin-top: 15px;">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Picture</th>
                  <th scope="col">meal name</th>
              
                  <th scope="col">price</th>
                  <th scope="col">Quantity</th>
                  <th scope="col">Edit</th>
                  <th scope="col">Delete</th>
                </tr>
              </thead>
              <tbody>


              <?php
                if ($role){
                  $stmt=$conn->prepare("SELECT * FROM product WHERE SID= '$SID' ");
                  $stmt->execute();
                  $cnt = 1;
                  while ( $product=$stmt->fetch()) {
                    $PID = $product['PID'];
                    $picture = $product['picture'];                  
                    $name = $product['name'];
                    $price = $product['price'];
                    $quantity = $product['quantity'];
                    $img_type= $product['imgType'];
                    echo<<<LABEL
                    <tr>
                      <th scope="row">$cnt</th>
                      <td><img src="data:$img_type;base64,$picture" /></td>
                      <td>$name</td>
                      <td>$price </td>
                      <td>$quantity </td>
                      <td><button type="button" class="btn btn-info" data-toggle="modal" data-target="#$PID"> Edit</button></td>                     
                      
                      <div class="modal fade" id="$PID" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          
                         <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="staticBackdropLabel">$name Edit</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form class="form-horizontal" role="form" method="POST" action="prod_edit.py">
                              <input type="hidden" name="PID" value="$PID"> 
                              <div class="modal-body">                              
                                 <div class="row" >
                                  <div class="col-xs-6">
                                    <label for="price">price</label>
                                    <input class="form-control" id="price" name="price" type="text" placeholder="enter price">
                                  </div>
                                  <div class="col-xs-6">
                                    <label for="quantity">quantity</label>
                                    <input class="form-control" id="quantity" name="quantity" type="text" placeholder="enter quantity">
                                  </div>
                                </div>
                      
                              </div>
                              <div class="modal-footer">
                                <button class="btn btn-default btn-primary" type="submit" name="submit">Edit</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                      <form class="form-horizontal" role="form" method="POST" action="prod_del.py">
                      <input type="hidden" name="PID" value="$PID">
                      <td><button type="submit" class="btn btn-danger">Delete</button></td>
                      </form>
                    </tr>
                    LABEL;
                    $cnt++;
                  }
                }
              ?>
                
             </tbody>
            </table>
          </div>

        </div>


      </div>

      <!-- My Order -->
      <div id="myOrder" class="tab-pane fade">
        <div class="row">
          <label class="control-label col-sm-1" for="status" style="margin-top:25px">Status</label>
          <div class="col-sm-2" style="margin-top:20px">
            <!-- <form method="POST" action=""> -->
              <!-- <select class="form-control" id="odrStatus" onchange="this.form.submit()"> -->
              <select class="form-control" id="odrSts" onchange="getMyOdr()">
                <option>All</option>
                <option>Finished </option>
                <option>Not Finished</option>
                <option>Cancel</option>
              </select>
            <!-- </form> -->
          </div>
        </div>
        <div class="row">
          <div id="myOdrContent"></div>
        </div>
        <script>
          function getMyOdr(){
            var uid=<?php echo $UID; ?>;
            var sts=document.getElementById("odrSts").value;
            document.getElementById("myOdrContent").innerHTML="";
            jQuery.ajax({
              url: "getMyOrder.php", 
              data:{
                // uid: uid,
                status: sts
              },
              type: "POST",
              success: function(data) {
                // window.location.href="nav.py";
                document.getElementById("myOdrContent").innerHTML=data;
                // $('#myOdrContent').html(data);
                // console.log(data);
              },
              error: function() {
                console.log('ERROR')
              }
            });
          }

          function cancelAll_myOdr(){
              console.log('ca');
              getCurrTime();
              document.getElementById('sendAll_myOdr').submit();
          }
        </script>
      </div>

      <!-- Shop Order -->
      <div id="shopOrder" class="tab-pane fade">
      <div class="row">
          <label class="control-label col-sm-1" for="status" style="margin-top:25px" id="shopOdrStsLabel">Action</label>
          <div class="col-sm-2" style="margin-top:20px" id="shopOdrStsDiv">
              <select class="form-control" id="shopOdrSts" onchange="getShopOdr()">
                <option>All</option>
                <option>Finished </option>
                <option>Not Finished</option>
                <option>Cancel</option>
              </select>
          </div>
        </div>
        <div class="row">
          <div id="shopOdrContent"></div>
        </div>
        <script>
          function getShopOdr(){
            var role=parseInt(<?php echo $role; ?>);
            if(!role){
              // document.getElementById('shopOdrSts').setAttribute('disabled',true);
              document.getElementById('shopOdrStsDiv').innerHTML="<h1>You haven't started a business.</h1>";
              document.getElementById('shopOdrStsLabel').innerHTML="";
              return;
            }

            var sts=document.getElementById("shopOdrSts").value;
            document.getElementById("shopOdrContent").innerHTML="";
            jQuery.ajax({
              url: "getShopOrder.php", 
              data:{
                status: sts
              },
              type: "POST",
              success: function(data) {
                document.getElementById("shopOdrContent").innerHTML=data;
              },
              error: function() {
                console.log('ERROR')
              }
            });
          }
        </script>
      </div>

      <!-- Trans Record -->
      <div id="transRecord" class="tab-pane fade">
        <div class="row">
          <label class="control-label col-sm-1" for="status" style="margin-top:25px">Action</label>
          <div class="col-sm-2" style="margin-top:20px">
              <select class="form-control" id="trSts" onchange="getTrnsRc()">
                <option>All</option>
                <option>Payment </option>
                <option>Receive</option>
                <option>Recharge</option>
                <option>Refund</option>
              </select>
          </div>
        </div>
        <div class="row">
          <div id="TrnsRcrdContent"></div>
        </div>
        <script>
          function getTrnsRc(){
            var uid=<?php echo $UID; ?>;
            var actn=document.getElementById("trSts").value;
            document.getElementById("TrnsRcrdContent").innerHTML="";
            jQuery.ajax({
              url: "getTransRecord.php", 
              data:{
                action: actn
              },
              type: "POST",
              success: function(data) {
                document.getElementById("TrnsRcrdContent").innerHTML=data;
              },
              error: function() {
                console.log('ERROR')
              }
            });
          }
        </script>
      </div>


    </div>
  </div>

  <!-- Option 1: Bootstrap Bundle with Popper -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script> -->
  <script>
    $(document).ready(function () {
      $(".nav-tabs a").click(function () {
        $(this).tab('show');
      });
    });
  </script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>