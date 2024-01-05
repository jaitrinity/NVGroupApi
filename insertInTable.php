<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
require 'base64ToAny.php';
$insertType = $_REQUEST["insertType"];
$methodType = $_SERVER['REQUEST_METHOD'];
//echo $insertType;
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
if($insertType == "employeeLocationMapping" && $methodType === 'POST'){
	$tenentId = $jsonData->tenentId;
	$state = $jsonData->state;
	$locationId = $jsonData->siteId;
	$role = $jsonData->role;
	$employee = $jsonData->employee;
	$isOR = $jsonData->isOR;
	$actionType = $jsonData->actionType;
	
	if($actionType == 'insert'){

		$insertEmpLocMapping = "INSERT INTO `EmployeeLocationMapping`(`LocationId`, `Emp_Id`, `Tenent_Id`) ";
		$tableData = "";
		if($isOR == false){
			$explodeState = explode(",", $state);
			$implodeState = implode("','", $explodeState);

			$stateLocSql = "SELECT l.LocationId FROM Location l where l.State in ('".$implodeState."') and l.Tenent_Id = $tenentId ";
			$stateLocQuery = mysqli_query($conn,$stateLocSql);
			while($stateLocRow = mysqli_fetch_assoc($stateLocQuery)){
				$locId = $stateLocRow["LocationId"];
				$locMapSql = "SELECT Id FROM EmployeeLocationMapping where LocationId = $locId and Emp_Id = '$employee' and Tenent_Id = $tenentId ";
				$locMapQuery = mysqli_query($conn,$locMapSql);
				$locMapRowcount = mysqli_num_rows($locMapQuery);
				if($locMapRowcount == 0){
					$tableData .= "($locId, '$employee', $tenentId),";
				}
			}

			$tableData = rtrim($tableData,',');

			$insertEmpLocMapping .= 'VALUES '.$tableData;
		}
		else{
			$explodeLocId = explode(",", $locationId);

			for($i=0; $i<count($explodeLocId); $i++){
				$locId = $explodeLocId[$i];
				$locMapSql = "SELECT Id FROM EmployeeLocationMapping where LocationId = $locId and Emp_Id = '$employee' and Tenent_Id = $tenentId ";
				$locMapQuery = mysqli_query($conn,$locMapSql);
				$locMapRowcount = mysqli_num_rows($locMapQuery);
				if($locMapRowcount == 0){
					$tableData .= "($locId, '$employee', $tenentId),";
				}
			}

			$tableData = rtrim($tableData,',');

			$insertEmpLocMapping .= 'VALUES '.$tableData;
		}

		// echo $insertEmpLocMapping;

		$output = new StdClass;
		if($tableData == ""){
			$output -> responseCode = "403";
			$output -> responseDesc = "Employee location mapping already exist as per select criteria";
		}
		else{
			if(mysqli_query($conn,$insertEmpLocMapping)){
				$output -> responseCode = "100000";
				$output -> responseDesc = "Successfully inserted";
			}
			else{
				$output -> responseCode = "-102003";
				$output -> responseDesc = "Something went wrong";
			}
		}			
		echo json_encode($output);
	}	
	else{
		$locIdList = [];
		if($isOR == false){
			$stateLocSql = "SELECT l.LocationId FROM Location l where l.State in ('".$implodeState."') and l.Tenent_Id = $tenentId ";
			$stateLocQuery = mysqli_query($conn,$stateLocSql);

			while($stateLocRow = mysqli_fetch_assoc($stateLocQuery)){
				array_push($locIdList, $stateLocRow["LocationId"]);
			}
		}
		else{
			$explodeLocId = explode(",", $locationId);

			for($i=0; $i<count($explodeLocId); $i++){
				$locId = $explodeLocId[$i];
				array_push($locIdList, $locId);
			}
		}
		$updateEmpLocMapping = "UPDATE `EmployeeLocationMapping` SET `Emp_Id`='$employee', `Update_Date` = current_timestamp  WHERE `LocationId` in (".implode(",", $locIdList).") and `Tenent_Id`= $tenentId";

		// echo $updateEmpLocMapping;
		$output = new StdClass;
		if(mysqli_query($conn,$updateEmpLocMapping)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully update";
		}
		else{
			$output -> responseCode = "-102003";
			$output -> responseDesc = "Something went wrong";
		}
		echo json_encode($output);
	}
}
else if($insertType == "importLocation" && $methodType === 'POST'){
	// echo $jsonData;
	$failExcelArr = [];
	foreach($jsonData as $importData) { 
		$srNo = $importData->srNo;
		$state = $importData->state;
		$city = $importData->city;
		$area = $importData->area;
		$locationName = $importData->locationName;
		$siteId = $importData->siteId;
		$siteType = $importData->siteType;
		$geoCoordinate = $importData->geoCoordinate;
		$tenentId = $importData->tenentId;

		// echo $state." ".$city.' '.$area.' '.$geoCoordinate;

		$stateCityAreaSelect = "SELECT * FROM `StateCityAreaMaster` WHERE `State` = '$state' ";
		$stateCityAreaResult = mysqli_query($conn,$stateCityAreaSelect);
		$rowcount=mysqli_num_rows($stateCityAreaResult);
		if($rowcount == 0){
			// $stateCityAreaInsert = "INSERT INTO `StateCityAreaMaster`(`State`, `City`, `Area`) VALUES ('$state', '$city', '$area')";
			$stateCityAreaInsert = "INSERT INTO `StateCityAreaMaster`(`State`) VALUES ('$state')";
			mysqli_query($conn,$stateCityAreaInsert);
		}

		$insertLocation = "INSERT INTO `Location`(`Name`, `State`, `Site_Id`, `Site_Type`, `GeoCoordinates`, `Tenent_Id`) 
		VALUES ('$locationName', '$state', '$siteId', '$siteType', '$geoCoordinate', $tenentId)";

		if(mysqli_query($conn,$insertLocation)){
			// Succfully insert
		}
		else{
			array_push($failExcelArr, $srNo);
		}
		
	}

	$output = new StdClass;
	$output -> responseCode = "100000";
	if(count($failExcelArr) == 0)
		$output -> responseDesc = "Successfully inserted";
	else
		$output -> responseDesc = "Except ".implode(',',$failExcelArr)." SrNo of excel, Data Successfully inserted";
	echo json_encode($output);

}
else if($insertType == "uploadMapping_old" && $methodType === 'POST'){
	$failExcelArr = [];
	foreach($jsonData as $importData) { 
		$locationId = $importData->locationId;
		$state = $importData->state;
		$siteType = $importData->siteType;
		$fromDate = $importData->fromDate;
		$tenentId = $importData->tenentId;
		$menuId = 0;
		if($siteType == "Office") $menuId = 6;
		else if($state == "HR") $menuId = 3;

		$empLocSql = "SELECT `Emp_Id` FROM `EmployeeLocationMapping` where `LocationId` = $locationId and `Tenent_Id` = $tenentId ";
		$empLocResult = mysqli_query($conn,$empLocSql);
		$rowCount=mysqli_num_rows($empLocResult);
		if($rowCount == 0){
			array_push($failExcelArr, $locationId);
		}
		else{
			$row = mysqli_fetch_assoc($empLocResult);
			$empId = $row["Emp_Id"];

			$vdList = explode(",", $fromDate);
			$tableSql = "INSERT INTO `Mapping`(`EmpId`, `Verifier`, `Approver`, `MenuId`, `LocationId`, `Start`, `End`,`Tenent_Id`)";
			$dataSqlArr = array();
			for($i=0;$i<count($vdList);$i++){
				$fDate = $vdList[$i];
				$fDate = str_replace('/', '-', $fDate);
				$fDate = date("Y-m-d", strtotime($fDate));
				$tDate = $fDate;
				$aa = "('$empId', '$empId', '$empId', $menuId, '$locationId', '$fDate', '$tDate', $tenentId)";
				array_push($dataSqlArr, $aa);

			}
			$dataSql = implode(",", $dataSqlArr);

			$insertMapping = "$tableSql VALUES $dataSql";

			if(mysqli_query($conn,$insertMapping)){}
			else{
				array_push($failExcelArr, $locationId);
			}
		}
	}
	$output = new StdClass;
	if(count($failExcelArr) == 0){
		$output -> responseDesc = "Successfully uploaded";
		$output -> responseCode = "100000";
	}
	else{
		$output -> responseDesc = "Except ".implode(',',$failExcelArr)." locationId of excel, Data Successfully inserted";
		$output -> responseCode = "102001";
	}
	echo json_encode($output);
}
else if($insertType == "uploadMapping" && $methodType === 'POST'){
	$sql = "SELECT `MenuId`, `AppMenuName` FROM `Menu` where `Tenent_Id` = 1 and `MenuType` = 1";
	$query = mysqli_query($conn,$sql);
	$menuList = array();
	while($row = mysqli_fetch_assoc($query)){
		$loopMenuId = $row["MenuId"];
		$appMenuName = $row["AppMenuName"];
		$menuJson = array('menuId' => $loopMenuId, 'appMenuName' => $appMenuName);
		array_push($menuList, $menuJson);
	}

	$failExcelArr = [];
	foreach($jsonData as $importData) { 
		$locationId = $importData->locationId;
		$state = $importData->state;
		$siteType = $importData->siteType;
		$fromDate = $importData->fromDate;
		$tenentId = $importData->tenentId;
		$menuId = 0;
		for($i=0;$i<count($menuList);$i++){
			$lmi = $menuList[$i]["menuId"];
			$amn = $menuList[$i]["appMenuName"];
			
			if($amn == $siteType) {
				$menuId = $lmi;
				break;
			} 
			else if($amn == $state && ($siteType == "Shop" || $siteType == "Hotel")) {
				$menuId = $lmi;
				break;
			}
		}

		// echo $amn.' -- '.$siteType.' -- '.$state.' -- '.$menuId.PHP_EOL;

		$empLocSql = "SELECT `Emp_Id` FROM `EmployeeLocationMapping` where `LocationId` = $locationId and `Tenent_Id` = $tenentId ";
		$empLocResult = mysqli_query($conn,$empLocSql);
		$rowCount=mysqli_num_rows($empLocResult);
		if($rowCount == 0){
			array_push($failExcelArr, $locationId);
		}
		else{
			$row = mysqli_fetch_assoc($empLocResult);
			$empId = $row["Emp_Id"];

			$vdList = explode(",", $fromDate);
			$tableSql = "INSERT INTO `Mapping`(`EmpId`, `Verifier`, `Approver`, `MenuId`, `LocationId`, `Start`, `End`,`Tenent_Id`)";
			$dataSqlArr = array();
			for($i=0;$i<count($vdList);$i++){
				$fDate = $vdList[$i];
				$fDate = str_replace('/', '-', $fDate);
				$fDate = date("Y-m-d", strtotime($fDate));
				$tDate = $fDate;
				$aa = "('$empId', '$empId', '$empId', $menuId, '$locationId', '$fDate', '$tDate', $tenentId)";
				array_push($dataSqlArr, $aa);

			}
			$dataSql = implode(",", $dataSqlArr);

			$insertMapping = "$tableSql VALUES $dataSql";

			if(mysqli_query($conn,$insertMapping)){}
			else{
				array_push($failExcelArr, $locationId);
			}
		}
	}
	$output = new StdClass;
	if(count($failExcelArr) == 0){
		$output -> responseDesc = "Successfully uploaded";
		$output -> responseCode = "100000";
	}
	else{
		$output -> responseDesc = "Except ".implode(',',$failExcelArr)." locationId of excel, Data Successfully inserted";
		$output -> responseCode = "102001";
	}
	echo json_encode($output);
}
else if($insertType == "location" && $methodType === 'POST'){
	$state = $jsonData->state;
	$city = $jsonData->city;
	$area = $jsonData->area;
	$locationName = $jsonData->locationName;
	$siteId = $jsonData->siteId;
	$siteType = $jsonData->siteType;
	$geoCoordinate = $jsonData->geoCoordinate;
	$address = $jsonData->address;
	$tenentId = $jsonData->tenentId;

	$stateCityAreaSelect = "SELECT * FROM `StateCityAreaMaster` WHERE `State` = '$state' ";
	$stateCityAreaResult = mysqli_query($conn,$stateCityAreaSelect);
	$rowcount=mysqli_num_rows($stateCityAreaResult);
	if($rowcount == 0){
		$stateCityAreaInsert = "INSERT INTO `StateCityAreaMaster`(`State`) VALUES ('$state')";
		mysqli_query($conn,$stateCityAreaInsert);
	}

	$insertLocation = "INSERT INTO `Location`(`Name`, `State`, `Site_Id`, `Site_Type`, `GeoCoordinates`, `Address`, `Tenent_Id`) 
	VALUES ('$locationName', '$state', '$siteId', '$siteType', '$geoCoordinate', '$address', $tenentId)";

	$output = new StdClass;
	if(mysqli_query($conn,$insertLocation)){
		//$last_id = $conn->insert_id;
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
		//echo "New record created successfully. Last inserted ID is: " . $last_id;
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
		//echo "New record created successfully. Last inserted ID is: " . $last_id;
	}
	echo json_encode($output);
}
else if($insertType == "employee" && $methodType === 'POST'){
	$employeeId = $jsonData->employeeCode;
	$employeeName = $jsonData->employeeName;
	$roleId = $jsonData->roleId;
	$rmId = $jsonData->rmId;
	$mobile = $jsonData->mobile;
	$emailId = $jsonData->emailId;
	$state = $jsonData->state;
	$hq = $jsonData->hq;
	$tenentId = $jsonData->tenentId;

	$sql1 = "select * from `Employees` where `Mobile` = '$mobile' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	$query1 = mysqli_query($conn,$sql1);

	$isExist1 = false;
	if(mysqli_num_rows($query1) != 0){
		$isExist1 = true;
	}

	$sql3 = "select * from `Employees` where `EmpId` = '$employeeId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	$query3 = mysqli_query($conn,$sql3);
	$isExist3 = false;
	if(mysqli_num_rows($query3) != 0){
		$isExist3 = true;
	}

	$output = new StdClass;
	if($isExist1){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$mobile." mobile number";
	}
	else if($isExist3){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$employeeId." employee id";
	}
	else{
		$password  = $mobile;

		$insertEmployee = "INSERT INTO `Employees`(`EmpId`, `Name`, `Password`, `Mobile`, `EmailId`, `RoleId`, `State`, `City`, `RMId`, 
		`Tenent_Id`, `Registered`, `Update`, `Active`) VALUES ('$employeeId', '$employeeName', '$password', '$mobile', '$emailId', $roleId, '$state', '$hq', '$rmId', $tenentId, current_timestamp, current_timestamp, 1)";

		// echo $insertEmployee;

		if(mysqli_query($conn,$insertEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";	
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	echo json_encode($output);

}
else if($insertType == "assign" && $methodType === 'POST'){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$startDate = $jsonData->startDate;
	$endDate = $jsonData->endDate;
	
	$insertAssign = "INSERT INTO `Assign`(`EmpId`, `MenuId`,`LocationId`,`StartDate`,`EndDate`,`Active`) VALUES ('$empId',$menuId,'$locationId','$startDate','$endDate',1)";


	$output = new StdClass;
	if(mysqli_query($conn,$insertAssign)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);

}
else if($insertType == "mapping" && $methodType === 'POST'){
	$empRole = $jsonData->empRole;
	$menuId = $jsonData->menuId;
	$state = $jsonData->state;
	$city = $jsonData->city;
	$area = $jsonData->area;
	$verifierRole = $jsonData->verifierRole;
	$approverRole = $jsonData->approverRole;
	$startDate = $jsonData->startDate;
	$endDate = $jsonData->endDate;
	$tenentId = $jsonData->tenentId;

	$explodeState = explode(",", $state);
	$implodeState = implode("','", $explodeState);

	$explodeEmpRole = explode(",", $empRole);
	$implodeEmpRole = implode("','", $explodeEmpRole);

	$sql = "SELECT `Emp_Id`,`LocationId` FROM `EmployeeLocationMapping` WHERE `State` in ('".$implodeState."') and `Role` in ('".$implodeEmpRole."') 
	and `Tenent_Id` = $tenentId ";
	if($city != ''){
	 	$sql .= "and `City` = '$city' ";
	}
	
	if($area != ''){
		$sql .= "and `Area` = '$area' ";
	}
	
	$query=mysqli_query($conn,$sql);
	$rowcount=mysqli_num_rows($query);

	$sql1 = "SELECT `Emp_Id` FROM `EmployeeLocationMapping` WHERE `State` in ('".$implodeState."') and `Role` = '$verifierRole' and `Tenent_Id` = $tenentId ";
	if($city != ''){
	 	$sql1 .= "and `City` = '$city' ";
	}
	
	if($area != ''){
		$sql1 .= "and `Area` = '$area' ";
	}
	
	$query1=mysqli_query($conn,$sql1);
	$row1 = mysqli_fetch_assoc($query1);
	$verifierId = $row1["Emp_Id"];

	$sql2 = "SELECT `Emp_Id` FROM `EmployeeLocationMapping` WHERE `State` in ('".$implodeState."') and `Role` = '$approverRole' and `Tenent_Id` = $tenentId ";
	if($city != ''){
	 	$sql2 .= "and `City` = '$city' ";
	}
	
	if($area != ''){
		$sql2 .= "and `Area` = '$area' ";
	}
	
	$query2=mysqli_query($conn,$sql2);
	$row2 = mysqli_fetch_assoc($query2);
	$approverId = $row2["Emp_Id"];

	$insertTable = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Verifier`,`Approver`,`Start`,`End`,`Tenent_Id`) ";
	$insertValue = "";
	$ii = 0;
	while($row = mysqli_fetch_assoc($query)){
		$fillerId = $row["Emp_Id"];
		$locationId = $row["LocationId"];

		$insertValue .= "('$fillerId',$menuId,'$locationId', '$verifierId','$approverId','$startDate','$endDate',$tenentId)";
		if($ii<($rowcount-1)){
			$insertValue .= ",";
		}

		$ii++;
	}
	
	$output = new StdClass;

	if($insertValue != ""){
		$insertMapping = $insertTable.' VALUES '.$insertValue;
		// echo $insertMapping;
		
		if(mysqli_query($conn,$insertMapping)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	else{
		$output -> responseCode = "404";
		$output -> responseDesc = "No record found as per select data.";
	}
	echo json_encode($output);

}
else if($insertType == "mapping_old" && $methodType === 'POST'){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$verifier = $jsonData->verifier;
	$approver = $jsonData->approver;
	$startDate = $jsonData->startDate;
	$endDate = $jsonData->endDate;
	$tenentId = $jsonData->tenentId;

	$explodeLocationId = explode(",", $locationId);
	$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`,`LocationId`,`Verifier`,`Approver`,`Start`,`End`,`Tenent_Id`) VALUES ";
	$insertValue = "";
	for($i=0;$i<count($explodeLocationId);$i++){
		$insertValue .= "('$empId',$menuId,'".$explodeLocationId[$i]."','$verifier','$approver','$startDate','$endDate',$tenentId)";
		if($i != count($explodeLocationId)-1){
			$insertValue .= ",";
		}
	}
	
	$output = new StdClass;
	if(mysqli_query($conn,$insertMapping.$insertValue)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);

}
else if($insertType == "checkpoint" && $methodType === 'POST'){
	$tenentId = $jsonData->tenentId;
	$description = $jsonData->description;
	$optionValue = $jsonData->optionValue;
	$isMandatory = $jsonData->isMandatory;
	$isEditable = $jsonData->isEditable;
	$inputTypeId = $jsonData->inputTypeId;
	$languageId = $jsonData->languageId;
	$correct = $jsonData->correct;
	$size = $jsonData->size;
	$score = $jsonData->score;
	$dependent = $jsonData->dependent;
	$isSql = $jsonData->isSql;
	$active = $jsonData->active;
	$logic = $jsonData->logic;
	$type = $jsonData->type;
	$videoBase64 = $jsonData->videoBase64;
	$imageBase64 = $jsonData->imageBase64;
	if($active == ""){
		$active = 1;
	}

	$errorInSql = "";
	$queryColumn = "";
	$columnValueArr = array();
 
	if($type == 0 && $inputTypeId == 21){
		$descSql = $optionValue;
		if(startsWith($descSql,"SELECT")){
			if(!$conn->query($descSql)){
				$errorInSql = ":".$conn->error;
			}
			else{
				
				$queryResult = mysqli_query($conn,$descSql);
				$fieldinfo = $queryResult -> fetch_fields();
				if(count($fieldinfo) == 1){
					foreach ($fieldinfo as $val) {
					    $queryColumn .= $val -> name;

					}

					array_push($columnValueArr,$queryColumn);
					while($row = mysqli_fetch_assoc($queryResult)){
						array_push($columnValueArr,$row[$queryColumn]);
					}					
				}
				else{
					$errorInSql = ": Only single column query valid.";
				}
			}
		}
		else{
			$errorInSql = ": only `select` query is valid. ";
		}
	}

	$t=time();
	$base64 = new Base64ToAny();

	if($inputTypeId == 18){
		$optionValue = $base64->base64_to_jpeg($videoBase64,$t.'_Video');
	}
	else if($inputTypeId == 19){
		$optionValue = $base64->base64_to_jpeg($imageBase64,$t.'_Image');
	}
	
	$insertCheckpoint = "INSERT INTO `Checkpoints`(`Description`, `Value`, `TypeId`, `Mandatory`, `Editable`, `Language`, `Correct`, `Size`, `Score`, 
	`Dependent`, `Logic`, `IsSql`, `Active`,`Tenent_Id`) 
	VALUES ('$description', '$optionValue', $inputTypeId, $isMandatory, $isEditable, $languageId, '$correct', '$size', '$score', '$dependent', '$logic', 
	$isSql, $active, $tenentId)";

	$output = new StdClass;
	if($errorInSql !=""){
		$output -> responseCode = "-102003";
		$output -> responseDesc = "Wrong Sql ".$errorInSql;
	}
	else if($queryColumn != ""){
		$output -> responseCode = "200";
		$output -> responseDesc = "Sql Column :".$queryColumn;
		$output -> columnValueArr = $columnValueArr;
	}
	else if($errorInSql == "" && mysqli_query($conn,$insertCheckpoint)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong ";
	}
	echo json_encode($output);

}
else if($insertType == "inputType" && $methodType === 'POST'){
	$typeName = $jsonData->typeName;
	$insertInputType = "INSERT INTO `Type`(`Type`) VALUES ('$typeName')";
	$output = new StdClass;
	if(mysqli_query($conn,$insertInputType)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($insertType == "checklist" && $methodType === 'POST'){
	$category = $jsonData->category;
	$subcategory = $jsonData->subcategory;
	$caption = $jsonData->caption;
	$checkpointId = $jsonData->checkpointId;
	$verifierId = $jsonData->verifierId;
	$approverId = $jsonData->approverId;
	$geoFence = $jsonData->geoFence;
	$icons = $jsonData->icons;
	$categoryIcon = $jsonData->categoryIcon;
	$subcategoryIcon = $jsonData->subcategoryIcon;
	$captionIcon = $jsonData->captionIcon;
	$active = $jsonData->active;
	$editMenuId = $jsonData->editMenuId;
	$tenentId = $jsonData->tenentId;
	$verifierRole = $jsonData->verifierRole;
	$approvalRole = $jsonData->approvalRole;
	$type = $jsonData->type;

	$t=time();
	$base64 = new Base64ToAny();

	if($type == "new"){

		if($categoryIcon != ""){
			$icons = $base64->base64_to_jpeg($categoryIcon,$t.'_CategoryIcon');
		}
		if($subcategory != "" && $subcategoryIcon != ""){
			$ic = $base64->base64_to_jpeg($subcategoryIcon,$t.'_SubcategoryIcon');
			$icons .= ','.$ic;
		}
		if($caption != "" && $captionIcon != ""){
			$ic = $base64->base64_to_jpeg($captionIcon,$t.'_CaptionIcon');
			$icons .= ','.$ic;
		}

		$insertChecklist = "INSERT INTO `Menu`(`Cat`,`Sub`,`Caption`,`CheckpointId`,`Verifier`,`Approver`,`GeoFence`,`Icons`,`Active`,`Verifier_Role`,
		`Approver_Role`,`Tenent_Id`) VALUES 
		('$category', '$subcategory','$caption','$checkpointId','$verifierId','$approverId','$geoFence','$icons',$active,'$verifierRole',
		'$approvalRole',$tenentId)";

		$output = new StdClass;
		$checklistResult = mysqli_query($conn,$insertChecklist);
		// echo $checklistResult;
		if($checklistResult != ""){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
		echo json_encode($output);
	}
	else{
		
		if($categoryIcon != ""){
			$icons = $base64->base64_to_jpeg($categoryIcon,$t.'_CategoryIcon');
		}
		if($subcategory != "" && $subcategoryIcon != ""){
			$ic = $base64->base64_to_jpeg($subcategoryIcon,$t.'_SubcategoryIcon');
			$icons .= ','.$ic;
		}
		if($caption != "" && $captionIcon != ""){
			$ic = $base64->base64_to_jpeg($captionIcon,$t.'_CaptionIcon');
			$icons .= ','.$ic;
		}

		$updateChecklist = "UPDATE `Menu` set `Cat` = '$category', `Sub` = '$subcategory', `Caption` = '$caption', `CheckpointId` = '$checkpointId',
		`Verifier` = '$verifierId', `Approver` = '$approverId', `GeoFence` = '$geoFence', `Icons` = '$icons', `Active` = $active, 
		`Verifier_Role` = '$verifierRole', `Approver_Role` = '$approvalRole'
		where `MenuId` = '$editMenuId' ";

		$output = new StdClass;
		$checklistResult = mysqli_query($conn,$updateChecklist);
		// echo $checklistResult;
		if($checklistResult != ""){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
		echo json_encode($output);
	}

		
}
else if($insertType == "role" && $methodType === 'POST'){
	$roleName = $jsonData->roleName;
	$menuId = $jsonData->menuId;
	$tenentId = $jsonData->tenentId;

	$sql = "SELECT * FROM `Role` WHERE `Role` = '$roleName' and Tenent_Id = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$rowcount=mysqli_num_rows($query);

	$output = new StdClass;
	if($rowcount == 0){
		$insertRole = "INSERT INTO `Role`(`Role`,`MenuId`,`Tenent_Id`) VALUES ('$roleName', '$menuId', $tenentId)";
		if(mysqli_query($conn,$insertRole)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	else{
		$output -> responseCode = "403";
		$output -> responseDesc = "$roleName role already exist.";
	}
	echo json_encode($output);
}


?>

<?php
// function sendCompaintMailPhp($emailFrom, $empEmailId, $ccEmailId, $bccEmailId, $subject, $msg){
function sendMail($msg){
	$emailFrom = "Galaxy spin";
	// $empEmailId = "pushkar.tyagi@trinityapplab.co.in";
	$empEmailId = "jai.prakash@trinityapplab.co.in";
	$ccEmailId = "";
	$bccEmailId = "";
	// $bccEmailId = "jai.prakash@trinityapplab.co.in";
	$subject = "PTW Raise";
	$url = "http://www.in3.co.in:8080/Aviom/aviom/sendCompaintMailPhp";

	$dataArray = ['emailFrom' => $emailFrom, 'emailId' => $empEmailId, 'ccEmailId' => $ccEmailId, 'bccEmailId' => $bccEmailId, 'subject' => $subject, 
	'msg' => $msg];
	$data = http_build_query($dataArray);
	$getUrl = $url."?".$data;

	$ch = curl_init();   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $getUrl);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
       
    $response = curl_exec($ch);
        
    if(curl_error($ch)){
        // echo 'Request Error:' . curl_error($ch);
    }else{
        // echo $response;
    }
       
    curl_close($ch);
}
function startsWith ($string, $startString) 
{
	$string = strtolower($string);
	$startString = strtolower($startString);

	$len = strlen($startString); 
	return (substr($string, 0, $len) === $startString); 
}
?>