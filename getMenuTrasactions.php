<?php
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$loginEmpState = $jsonData->loginEmpState;
$menuId = $jsonData->menuId;
$subCatMenuId = $jsonData->subCatMenuId;
$captionMenuId = $jsonData->captionMenuId;
$filterStartDate = $jsonData->filterStartDate;
$filterEndDate = $jsonData->filterEndDate;
$level = $jsonData->level;
$tenentId = $jsonData->tenentId;

$empList = [];
// Admin
if($loginEmpRole == '1'){
	$empSql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($empRow = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$empRow["EmpId"]);
		}
	}

}
// RM
else if($loginEmpRole == '3'){
	$empSql = "SELECT * FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($empRow = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$empRow["EmpId"]);
		}
	}
	// for self data
	array_push($empList,$loginEmpId);
}
// SH
else if($loginEmpRole == '4'){
	$explodeState = explode(",", $loginEmpState);
	$implodeState = implode("','", $explodeState);

	$empSql = "SELECT * FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($empRow = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$empRow["EmpId"]);
		}
	}
	// for self data
	array_push($empList,$loginEmpId);
}
else{
	array_push($empList,$loginEmpId);
}

$loginEmpId = implode("','", $empList);

if($level == 2){
	$menuId = $subCatMenuId;
}

$output = array();
$wrappedList = [];

$unionSql = "select DISTINCT t.`ActivityId` from (
select `ActivityId` from `Activity` where `EmpId` in ('$loginEmpId') and `MenuId` in ($menuId) and `Event` = 'Submit'
) t";


$sql = "SELECT distinct `h`.`ActivityId`, `h`.`ServerDateTime`, `h`.`Status`, `h`.`VerifierActivityId`, `h`.`ApproverActivityId`, `h`.`Site_Name`, `a`.`EmpId` as fillingByEmpId, `e`.`Name` as fillerByEmpName, a.MenuId as loopMenuId, `e`.`State` as fillingByState, `e`.`Area` as fillingByArea, '' as verifyByEmpId, '' as verifiedByEmpName, '' as verifiedDate, '' as approveByEmpId, '' as approvedByEmpName, '' as approvedDate, m.`PortalMenuName` as subName, a.`GeoLocation` 
FROM `TransactionHDR` h
join `Activity` a on `h`.`ActivityId` = `a`.`ActivityId`
join `Menu` m on a.`MenuId` = m.`MenuId` 
join `Location` l on a.`LocationId` = l.`LocationId`
left join `Mapping` mp on h.`ActivityId` = mp.`ActivityId` and mp.`LocationId` != 1
left join `Location` l2 on mp.`LocationId` = l2.`LocationId`
left join `Employees` e on `a`.`EmpId` = `e`.`EmpId` 
where `h`.`ActivityId` in ($unionSql) ";

if($filterStartDate != ''){
	$sql .= " and DATE_FORMAT(`h`.`ServerDateTime`,'%Y-%m-%d') >= '$filterStartDate' "; 
}
if($filterEndDate != ''){
	$sql .= " and DATE_FORMAT(`h`.`ServerDateTime`,'%Y-%m-%d') <= '$filterEndDate' "; 
}
if($filterStartDate == "" && $filterEndDate == ""){
	$sql .= " and h.`ServerDateTime` >= now()-interval 3 month ";
}

$sql .= " order by `h`.`ActivityId` desc";

// echo $sql;

$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$activityId = $row["ActivityId"];
	$serverDateTime = $row["ServerDateTime"];
	$verifierActivityId = $row["VerifierActivityId"];
	$approverActivityId = $row["ApproverActivityId"];
	$verifiedByEmpName = $row["verifiedByEmpName"];
	$verifiedDate = $row["verifiedDate"];
	$approvedByEmpName = $row["approvedByEmpName"];
	$approvedDate = $row["approvedDate"];

	$loopMenuId = $row["loopMenuId"];
	$subName = $row["subName"];
	$siteName = $row["Site_Name"];

	$fillingByEmpId = $row["fillingByEmpId"];
	$fillerByEmpName = $row["fillerByEmpName"];
	$fillingByState = $row["fillingByState"];
	$fillingByArea = $row["fillingByArea"];

	$verifyByEmpId = $row["verifyByEmpId"];
	$verifiedByEmpName = $row["verifiedByEmpName"];

	$approveByEmpId = $row["approveByEmpId"];
	$approvedByEmpName = $row["approvedByEmpName"];

	

	$isVerifierExist = false;
	$isApproverExist = false;
	// if($verifierActivityId == null){
		// $mappingSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$loopMenuId' ";
		// $mappingQuery = mysqli_query($conn,$mappingSql);
		// $mappingRow = mysqli_fetch_assoc($mappingQuery);

		// if($mappingRow["Verifier"] != null && $mappingRow["Verifier"] != ""){
		// 	$isVerifierExist = true;
		// }
		// if($mappingRow["Approver"] != null && $mappingRow["Approver"] != ""){
		// 	$isApproverExist = true;
		// }

	// }

	$isVerifier = false;
	// if($verifierActivityId == null){
	// 	$verifierSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$loopMenuId' and `Verifier` in ('$loginEmpId') ";
	// 	$verifierQuery=mysqli_query($conn,$verifierSql);
	// 	if(mysqli_num_rows($verifierQuery) !=0){
	// 		$isVerifier = true;
	// 	}
	// }
	
	$isApprover = false;
	// if($approverActivityId == null){
	// 	$approverSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$loopMenuId' and `Approver` in ('$loginEmpId') ";
	// 	$approverQuery=mysqli_query($conn,$approverSql);
	// 	if(mysqli_num_rows($approverQuery) !=0){
	// 		$isApprover = true;
	// 	}
	// }

	$pendingForApprove = "Yes";
	$pendingForVerify = "Yes";

	$myRoleForTask = "";
	if($isVerifier){
		$myRoleForTask = "Verifier";
	}
	else if($isApprover){
		$myRoleForTask = "Approver";
	}

	if(!$isVerifierExist)
		$pendingForVerify = "NA";

	if(!$isApproverExist)
		$pendingForApprove = "NA";

	
	$json = new StdClass;
	
	$json -> pendingForApprove = $pendingForApprove;
	$json -> menuId = $loopMenuId;
	$json -> subName = $subName;
	$json -> transactionId = $activityId;
	$json -> verifierTId = $verifierActivityId;
	$json -> approvedTId = $approverActivityId;
	$json -> dateTime = $serverDateTime;
	$json -> approveDetList = [];
	$json -> myRoleForTask = $myRoleForTask;
	$json -> transactionDetList = $transactionDetList;
	$json -> fillingByEmpId = $fillingByEmpId;
	$json -> fillingBy = $fillerByEmpName;
	$json -> fillingByState = $fillingByState;
	$json -> fillingByArea = $fillingByArea;
	
	$json -> verifiedBy = $verifiedByEmpName;
	$json -> approvedBy = $approvedByEmpName;
	$json -> verifiedDate = $verifiedDate;
	$json -> approvedDate = $approvedDate;

	$json -> siteName = $siteName;
	$json -> pendingForVerify = $pendingForVerify;
	$json -> latLong = $row["GeoLocation"];
	
	array_push($wrappedList,$json);

}

$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000', 'count' => $level);
echo json_encode($output);
?>