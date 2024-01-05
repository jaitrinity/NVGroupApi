<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$tenentId = $jsonData->tenentId;
$menuId = $jsonData->menuId;

$distSubCat = [];
$sql = "SELECT `CheckpointId` FROM `Menu` WHERE `MenuId` = $menuId ";
$query = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($query);
$checkpointIdStr = $row["CheckpointId"];
$menuIdStr = explode(":", $checkpointIdStr)[1];
$menuIdList = explode(",", $menuIdStr);
$menuImplodeList = implode(",", $menuIdList);

$sql = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuImplodeList) and `Tenent_Id` = $tenentId ";
$query=mysqli_query($conn,$sql);

$output = array();
$wrappedList = [];
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	$portalMenuName = $row["PortalMenuName"];
	$json = new StdClass;
	$json -> paramCode = $menuId;
	$json -> paramDesc = $portalMenuName;
	array_push($wrappedList,$json);
	
}
$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000');
echo json_encode($output);
?>