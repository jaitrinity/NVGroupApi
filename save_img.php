<?php 

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

?>
<?php
$dir = date("M-Y-d");
if (!file_exists('/var/www/trinityapplab.co.in/NVGroup/files/'.$dir)) {
    mkdir('/var/www/trinityapplab.co.in/NVGroup/files/'.$dir, 0777, true);
}
$t=date("YmdHis");
$target_dir = "files/".$dir."/";

$activityId=$_REQUEST["trans_id"];
$company=$_REQUEST["company"];
$chk_id=$_REQUEST["chk_id"];
$depend_upon=$_REQUEST["depend_upon"];
$timestamp=$_REQUEST["timestamp"];
$caption=$_REQUEST["caption"];
$latitude=$_REQUEST["latitude"];
$longitude=$_REQUEST["longitude"];
$img_timestamp=$_REQUEST["img_timestamp"];
$latlong = $latitude.','.$longitude;
$t = $img_timestamp;
$cpId = "";
$dependId = "";
$cpIdlist = explode("_",$chk_id);
$dIdlist = explode("_",$depend_upon);
if(count($cpIdlist) > 2){
	$cpId = $cpIdlist[1];
}
else{
	$cpId = $cpIdlist[0];
}
$dependId = $dIdlist[0];

$prevValue = "";
$target_file = $target_dir."".$t.$_FILES["attachment"]["name"];
	
//echo $target_file."<br>";
//echo "$t".$_FILES["attachment"]["name"];
$isWrite = move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file); 
//echo $isWrite;
if ($isWrite) 
{
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$link = $_SERVER['HTTP_HOST']; 
	$fileURL = "http://".$link."/".$parts[1]."/".$target_file;
	
	$selectQuery = "Select Value, Lat_Long, Date_time from TransactionDTL where ActivityId = '$activityId' and ChkId = '$cpId'  and DependChkId = '$dependId' 
	and Value like 'http%'";
	$selectData = mysqli_query($conn,$selectQuery);
	$rowcount = mysqli_num_rows($selectData);
	if($rowcount > 0){
		$sr = mysqli_fetch_assoc($selectData);
		$prevValue = $sr['Value'];
		$imgList = explode(",", $prevValue);
		if(in_array($fileURL, $imgList)){
			// Not do any action if image url already exist in db.
		}
		else{
			$prevLat_Long = $sr['Lat_Long'];
			$prevDatetime = $sr["Date_time"];
			$query = "Update TransactionDTL set Value = '$prevValue,$fileURL', Lat_Long = '$prevLat_Long:$latlong', `Date_time` = '$prevDatetime,$timestamp' 
			where ActivityId = '$activityId' and ChkId = '$cpId'  and DependChkId = '$dependId'";	
		}
			
	}
	else{
		$query = "Update TransactionDTL set Value = '$fileURL', Lat_Long = '$latlong', `Date_time` = '$timestamp' 
		where ActivityId = '$activityId' and ChkId = '$cpId'  and DependChkId = '$dependId'";	
	}
	
	mysqli_query($conn,$query);

	$arr[]=array('error' => '200','message'=>'Save Successfully!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>$fileURL);
	header('Content-Type: application/json');
	echo json_encode($arr[0]);
} 
else 
{
	$arr[]=array('error' => '201','message'=>'Error!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>'');
	header('Content-Type: application/json');
	echo json_encode($arr[0]);
    //echo "Sorry, there was an error uploading your file.";
    //exit();
}
?>