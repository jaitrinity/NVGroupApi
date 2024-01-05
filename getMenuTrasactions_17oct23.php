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
SELECT `ActivityId` FROM `Mapping` where (`EmpId` in ('$loginEmpId') OR `Verifier` in ('$loginEmpId') OR `Approver` in ('$loginEmpId')) and `MenuId` in ($menuId) and `ActivityId` != 0
UNION
select `ActivityId` from `Activity` where `EmpId` in ('$loginEmpId') and `MenuId` in ($menuId) and `Event` = 'Submit'
UNION 
SELECT h.ActivityId FROM EmployeeLocationMapping el join Location l on el.LocationId = l.LocationId join TransactionHDR h on l.Site_Id = h.Site_Id join Activity a on h.ActivityId = a.ActivityId and a.MenuId in ($menuId) and a.Event = 'Submit' WHERE el.`Emp_Id` in ('$loginEmpId') ) t";


$sql = "SELECT distinct `h`.`ActivityId`, `h`.`ServerDateTime`, `h`.`Status`, `h`.`VerifierActivityId`, `h`.`ApproverActivityId`, (case when a.MenuId in (SELECT `MenuId` FROM `Menu` where `MenuType` = 1 and `Tenent_Id` = 1) then concat(l2.`Site_Type`,' - ',l2.`Name`) else `h`.`Site_Name` end) as `Site_Name`, `a`.`EmpId` as fillingByEmpId, `e`.`Name` as fillerByEmpName, a.MenuId as loopMenuId, `e`.`State` as fillingByState, `e`.`Area` as fillingByArea, `a1`.`EmpId` as verifyByEmpId, `e1`.`Name` as verifiedByEmpName, `a1`.`ServerDateTime` as verifiedDate, `a2`.`EmpId` as approveByEmpId, `e2`.`Name` as approvedByEmpName, `a2`.`ServerDateTime` as approvedDate, m.`PortalMenuName` as subName
FROM `TransactionHDR` h
join `Activity` a on `h`.`ActivityId` = `a`.`ActivityId`
join `Menu` m on a.`MenuId` = m.`MenuId` 
join `Location` l on a.`LocationId` = l.`LocationId`
left join `Mapping` mp on h.`ActivityId` = mp.`ActivityId` and mp.`LocationId` != 1
left join `Location` l2 on mp.`LocationId` = l2.`LocationId`
left join `Activity` a1 on `h`.`VerifierActivityId` = `a1`.`ActivityId` 
left join `Activity` a2 on `h`.`ApproverActivityId` = `a2`.`ActivityId`
left join `Employees` e on `a`.`EmpId` = `e`.`EmpId` 
left join `Employees` e1 on `a1`.`EmpId` = `e1`.`EmpId` 
left join `Employees` e2 on `a2`.`EmpId` = `e2`.`EmpId` 
where `h`.`ActivityId` in ($unionSql) ";

if($filterStartDate != ''){
	$sql .= " and DATE_FORMAT(`h`.`ServerDateTime`,'%Y-%m-%d') >= '$filterStartDate' "; 
}
if($filterEndDate != ''){
	$sql .= " and DATE_FORMAT(`h`.`ServerDateTime`,'%Y-%m-%d') <= '$filterEndDate' "; 
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
	$json -> fillingBy = $fillerByEmpName;
	$json -> fillingByState = $fillingByState;
	$json -> fillingByArea = $fillingByArea;
	
	$json -> verifiedBy = $verifiedByEmpName;
	$json -> approvedBy = $approvedByEmpName;
	$json -> verifiedDate = $verifiedDate;
	$json -> approvedDate = $approvedDate;

	$json -> siteName = $siteName;
	$json -> pendingForVerify = $pendingForVerify;
	
	
	array_push($wrappedList,$json);

}

$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000', 'count' => $level);
echo json_encode($output);
?>