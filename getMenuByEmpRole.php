<?php
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$empRoleId = $jsonData->empRoleId;
$tenentId = $jsonData->tenentId;

$menuList = array();
$sql3 = "SELECT `MenuId`, `PortalMenuName` as Cat, `CheckpointId`, `Icons` FROM `Menu` where `Tenent_Id` = 100 ORDER BY `MenuId` ASC ";
$query3=mysqli_query($conn,$sql3);
while($row3 = mysqli_fetch_assoc($query3)){
	$menuId = $row3["MenuId"];
	$catName = $row3["Cat"];
	$checkpointId = $row3["CheckpointId"];
	$icons = $row3["Icons"];
	
	$json = new StdClass;
	$json -> menuId = $menuId;
	$json -> menuName = $catName;
	$json -> routerLink = "mg/".$menuId;
	$json -> checkpointId = $checkpointId;
	$json -> icon = explode(",", $icons)[0];
	array_push($menuList,$json);

}
		
$output = array('wrappedList' => $menuList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000');

echo json_encode($output);
?>