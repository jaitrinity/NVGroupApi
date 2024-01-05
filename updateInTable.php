<?php
include("dbConfiguration.php");
$updateType = $_REQUEST["updateType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
if($updateType == "device"){
	$deviceId = $jsonData->deviceId;
	$action = $jsonData->action;
	
	$updateDevice = "update `Devices` set `Active` = $action, `Update` = current_timestamp where `DeviceId` = $deviceId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateDevice)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "mapping"){
	$mappingId = $jsonData->mappingId;
	$action = $jsonData->action;
	
	$updateMapping = "update `Mapping` set `Active` = $action where `MappingId` = $mappingId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "assign"){
	$assignId = $jsonData->assignId;
	$action = $jsonData->action;
	
	$updateAssign = "update `Assign` set `Active` = $action where `AssignId` = $assignId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateAssign)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "employee"){
	$id = $jsonData->id;
	$action = $jsonData->action;
	
	$updateEmployee = "update `Employees` set `Active` = $action, `ActiveDeactiveDate` = current_timestamp where `Id` = $id ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateEmployee)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "editEmployee"){
	// $id = $jsonData->id;
	$employeeId = $jsonData->employeeId;
	$employeeName = $jsonData->employeeName;
	$roleId = $jsonData->roleId;
	$rmId = $jsonData->rmId;
	$mobile = $jsonData->mobile;
	$emailId = $jsonData->emailId;
	$state = $jsonData->state;

	$sql1 = "select * from `Employees` where `EmpId` = '$employeeId' and `Mobile` = '$mobile'  ";
	// echo $sql1;
	$query1 = mysqli_query($conn,$sql1);
	$isSame = false;
	if(mysqli_num_rows($query1) != 0){
		$isSame = true;
	}

	if(!$isSame){
		$sql2 = "select * from `Employees` where `Mobile` = '$mobile' ";
		$query2 = mysqli_query($conn,$sql2);
		$isExist2 = false;
		if(mysqli_num_rows($query2) != 0){
			$isExist2 = true;
		}
	}

	

	$output = new StdClass;
	if($isSame){
		$updateData = "";

		$updateEditEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `EmailId`='$emailId', `RoleId`=$roleId, `State`='$state', `RMId`='$rmId', `Update`=current_timestamp where `EmpId` = '$employeeId' ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully update";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	
	else if($isExist2){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$mobile." mobile number";
	}
	
	else{
		$updateEditEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `EmailId`='$emailId', `RoleId`=$roleId, `State`='$state', `RMId`='$rmId', `Update`=current_timestamp where `EmpId` = '$employeeId' ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully update";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	
	
	echo json_encode($output);
}
else if($updateType == "roleDelete"){
	$roleId = $jsonData->roleId;
	
	$deleteRole = "delete from `Role` where `RoleId` = $roleId ";
	$output = new StdClass;
	if(mysqli_query($conn,$deleteRole)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully Deleted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "roleUpdate"){
	$roleId = $jsonData->roleId;
	$menuId = $jsonData->menuId;
	// $verifierRole = $jsonData->verifierRole;
	// $approverRole = $jsonData->approverRole;
	
	// $updateRole = "update `Role` set `MenuId` = '$menuId',`Verifier_Role` = '$verifierRole', `Approver_Role` = '$approverRole' where `RoleId` = $roleId ";
	$updateRole = "update `Role` set `MenuId` = '$menuId' where `RoleId` = $roleId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateRole)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

else if($updateType == "updateMapping"){
	$mappingId = $jsonData->mappingId;
	$locationId = $jsonData->locationId;
	$verifierId = $jsonData->verifierId;
	$approverId = $jsonData->approverId;
	
	$updateMapping = "update `Mapping` set `LocationId` = '$locationId',`Verifier` = '$verifierId', `Approver` = '$approverId' where `MappingId` = $mappingId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "updateLocation"){
	$locationId = $jsonData->locationId;
	$locationName = $jsonData->locationName;
	$geoCoordinate = $jsonData->geoCoordinate;
	$siteId = $jsonData->siteId;
	$siteType = $jsonData->siteType;
	$address = $jsonData->address;
	
	$updateMapping = "update `Location` set `Name` = '$locationName', `Site_Id` = '$siteId', `Site_Type` = '$siteType', `GeoCoordinates` = '$geoCoordinate', 
	`Address` = '$address' where `LocationId` = $locationId ";

	// $updateMapping = "update `Location` set `Name` = '$locationName', `GeoCoordinates` = '$geoCoordinate', `Address` = '$address' 
	// where `LocationId` = $locationId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "routerSequence"){
	$loginEmpId = $jsonData->loginEmpId;
	$currentRouter = $jsonData->currentRouter;
	$explodeRouter = explode("/", $currentRouter);

	$updateRouter = "update `Header_Menu` set `Display_Order` = `Display_Order` + 1 where `Router_Link` = '".$explodeRouter[2]."' ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateRouter)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == 'changeChecklistChecpointSequence'){
	$menuId = $jsonData->menuId;
	$checkpointId = $jsonData->checkpointId;

	$updateChlChp = "update `Menu` set `CheckpointId` = '$checkpointId' where `MenuId` = ".$menuId." ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateChlChp)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "actionOnTransaction"){
	$actionType = $jsonData->actionType;
	$transactionId = $jsonData->transactionId;
	$status = $jsonData->status;
	$remark = $jsonData->remark;
	$reasonOfCancel = $jsonData->reasonOfCancel;
	$otherReason = $jsonData->otherReason;

	$trStatus = "update `TransactionHDR` set `TransactionStatus` = $status, `Remark` = '$remark' where `ActivityId` = $transactionId ";
	if($actionType == 'ptw')
		$trStatus = "update `TransactionHDR` set `Status` = 'PTW_100', `ReasonOfCancel` = '$reasonOfCancel', `OtherReason` = '$otherReason' where `ActivityId` = $transactionId ";
	$output = new StdClass;
	if(mysqli_query($conn,$trStatus)){
		$mpStatus = "update `Mapping` set `Active` = $status where `ActivityId` = $transactionId ";
		mysqli_query($conn,$mpStatus);
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

else if($updateType == "updateSupervisor"){
	$id = $jsonData->id;
	$employeeName = $jsonData->employeeName;
	$mobile = $jsonData->mobile;
	$whatsappNumber = $jsonData->whatsappNumber;
	$aadharNumber = $jsonData->aadharNumber;
	$tenentId = $jsonData->tenentId;
	
	$updateEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Whatsapp_Number`='$whatsappNumber', `AadharCard_Number`='$aadharNumber', `Update`=current_timestamp where `Id` = $id ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateEmployee)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

else if($updateType == "updateRaiser"){
	$id = $jsonData->id;
	$employeeName = $jsonData->employeeName;
	$mobile = $jsonData->mobile;
	$whatsappNumber = $jsonData->whatsappNumber;
	$aadharNumber = $jsonData->aadharNumber;
	$tenentId = $jsonData->tenentId;
	
	$updateEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Whatsapp_Number`='$whatsappNumber', `AadharCard_Number`='$aadharNumber', `Update`=current_timestamp where `Id` = $id ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateEmployee)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "updateVendor"){
	$id = $jsonData->id;
	$vendorName = $jsonData->vendorName;
	$vendorCode = $jsonData->vendorCode;
	$vendorType = $jsonData->vendorType;
	$state = $jsonData->state;
	$vendorMobile = $jsonData->vendorMobile;
	$tenentId = $jsonData->tenentId;
	
	$updateEmployee = "update `Employees` set `EmpId` = '$vendorCode', `Name`='$vendorName', `Mobile`='$vendorMobile', `State`='$state', `VendorType`='$vendorType', `Update`=current_timestamp where `Id` = $id ";

	// echo $updateEmployee;
	$output = new StdClass;
	if(mysqli_query($conn,$updateEmployee)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "changeSiteStatus"){
	$locationId = $jsonData->locationId;
	$siteStatus = $jsonData->siteStatus;
	$siteStatusReason = $jsonData->siteStatusReason;

	$sql = "UPDATE `Location` set `Is_Active` = $siteStatus, `Site_Status_Reason` = '$siteStatusReason', `Deactive_Date` = CURDATE() where LocationId = $locationId";
	$output = new StdClass;
	if(mysqli_query($conn,$sql)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

?>