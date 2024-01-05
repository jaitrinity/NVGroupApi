<?php
include("dbConfiguration.php");
$selectType = $_REQUEST["selectType"];
//echo $selectType;
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$tenentId = $jsonData->tenentId;
if($selectType == "EmpLocMapping"){
	
	$sql = "SELECT empLoc.Id, loc.State, loc.Name as locName, loc.Site_Type, empLoc.Emp_Id, emp.Name as empName, emp.RoleId, ro.Role as empRole FROM EmployeeLocationMapping empLoc join Location loc on empLoc.LocationId = loc.LocationId left join Employees emp on empLoc.Emp_Id = emp.EmpId left join Role ro on emp.RoleId = ro.RoleId where empLoc.Tenent_Id = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$empLocMappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		
		$json = array(
			'id' => $row["Id"],
			'state' => $row["State"],
			'locName' => $row["locName"],
			'siteType' => $row["Site_Type"],
			'empId' => $row["Emp_Id"],
			'empName' => $row["empName"],
			'roleId' => $row["RoleId"],
			'roleName' => $row["empRole"]
		);
		array_push($empLocMappingArr,$json);
	}
	$output = array();
	$output = array('empLocMappingList' => $empLocMappingArr);
	echo json_encode($output);
}
else if($selectType == "EmployeeByCircleAndRole"){
	$state = $jsonData->state;
	$role = $jsonData->role;

	// $sql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = '$tenentId' and `State` = '$state' and `RoleId` = '$role' ";
	$sql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = '$tenentId' and `RoleId` = '$role' ";
	$query=mysqli_query($conn,$sql);
	$empArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$json = array(
			'paramCode' => $row["EmpId"],
			'paramDesc' => $row["Name"]
		);
		array_push($empArr,$json);
	}
	$output = array();
	$output = array('employeeList' => $empArr);
	echo json_encode($output);
}
else if($selectType == "assign"){
	$sql = "SELECT `Assign`.`AssignId`, `Employees`.`Name`  as empName, `Assign`.`MenuId`,`Menu`.`Cat`,`Assign`.`LocationId`,`Assign`.`StartDate`,`Assign`.`EndDate`, `Assign`.`Active` FROM `Assign` left join `Employees` on `Assign`.`EmpId` = `Employees`.`EmpId` left join `Menu` on `Assign`.`MenuId` = `Menu`.`MenuId` ";
	$query=mysqli_query($conn,$sql);

	$assignArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$assignId = $row["AssignId"];
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$startDate = $row["StartDate"];
		$endDate = $row["EndDate"];
		$active = $row["Active"];
		
		$json = array(
			'assignId' => $assignId,
			'empId' => $empId,
			'empName' => $empName,
			'menuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'active' => $active,
		);
		array_push($assignArr,$json);
	}
	$output = array();
	$output = array('assignList' => $assignArr);
	echo json_encode($output);

}
else if($selectType == "activity"){
	$a = "";
	if($loginEmpRole != "Admin" && $loginEmpRole != "SpaceWorld" && $loginEmpRole != "Management"){
		$a = " `Activity`.`EmpId` = '$loginEmpId' and ";
	}
	$sql = "SELECT `Activity`.`EmpId`, `Employees`.`Name`  as empName, `Activity`.`MenuId`,`Menu`.`Cat`,`Activity`.`LocationId`,`Location`.`Name` as locName, 
	`Activity`.`Event`,`Activity`.`MobileDateTime` FROM `Activity` left join `Employees` on `Activity`.`EmpId` = `Employees`.`EmpId` 
	left join `Menu` on `Activity`.`MenuId` = `Menu`.`MenuId` 
	left join `Location` on `Activity`.`LocationId` = `Location`.`LocationId` 
	where ".$a." `Activity`.`Tenent_Id` = $tenentId and `Activity`.`Event` = 'Submit' ";
	$query=mysqli_query($conn,$sql);

	$activityArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$locName = $row["locName"];
		$event = $row["Event"];
		$dateTime = $row["MobileDateTime"];
		
		$json = array(
			'empId' => $empId,
			'empName' => $empName,
			'menuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'locName' => $locName,
			'event' => $event,
			'dateTime' => $dateTime,
		);
		array_push($activityArr,$json);
	}
	$output = array();
	$output = array('activityList' => $activityArr);
	echo json_encode($output);

}
else if($selectType == "employee"){
	
	$sql = "SELECT `e1`.*, `Role`.`Role` as roleName, e2.`Name` as rmName FROM `Employees` e1 
	left join `Role` on `e1`.`RoleId` = `Role`.`RoleId` 
	left join `Employees` e2 on e1.`RMId` = e2.`EmpId` where `e1`.`Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);

	$rmIdList = array();
	$empArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$id = $row["Id"];
		$empId = $row["EmpId"];
		$empName = $row["Name"];
		$password = $row["Password"];
		$mobile = $row["Mobile"];
		$emailId = $row["EmailId"];
		$roleId = $row["RoleId"];
		$area = $row["Area"];
		$city = $row["City"];
		$state = $row["State"];
		$rmId = $row["RMId"];
		$fieldUser = $row["FieldUser"];
		$active = $row["Active"];
		$roleName = $row["roleName"];
		$rmName = $row["rmName"];

		$json = array(
			'id' => $id,
			'empId' => $empId,
			'empName' => $empName,
			'password' => $password,
			'mobile' => $mobile,
			'emailId' => $emailId,
			'roleId' => $roleId,
			'area' => $area,
			'city' => $city,
			'state' => $state,
			'rmId' => $rmId,
			'fieldUser' => $fieldUser,
			'fieldUserValue' => $fieldUser == 1 ? "Yes" : "No",
			'active' => $active,
			'roleName' => $roleName,
			'rmName' => $rmName,
		);
		array_push($empArr,$json);
	}
	$output = array();
	$output = array('employeeList' => $empArr);
	echo json_encode($output);
}
else if($selectType == "supervisor"){
	$supervisorList = array();
	$sql = "SELECT * FROM `Employees` where `RMId` = '$loginEmpId' and `RoleId` = 54 and `Active` = 1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$supJson = array('id' => $row["Id"], 'name' => $row["Name"], 'mobile' => $row["Mobile"], 'whatsapp' => $row["Whatsapp_Number"], 'aadharCard' => $row["AadharCard_Number"]);
		array_push($supervisorList,$supJson);
	}

	$output = array('supervisorList' => $supervisorList);
	echo json_encode($output);
}
else if($selectType == "raiser"){
	$raiserList = array();
	$sql = "SELECT * FROM `Employees` where `RMId` = '$loginEmpId' and `RoleId` = 57 and `Active` = 1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$supJson = array('id' => $row["Id"], 'name' => $row["Name"], 'mobile' => $row["Mobile"], 'whatsapp' => $row["Whatsapp_Number"], 'aadharCard' => $row["AadharCard_Number"]);
		array_push($raiserList,$supJson);
	}

	$output = array('raiserList' => $raiserList);
	echo json_encode($output);
}
else if($selectType == "device"){
	$c = "";
	if($loginEmpRole != "Admin" && $loginEmpRole != "SpaceWorld" && $loginEmpRole != "Management"){
		$c = " `EmpId` = '$loginEmpId' and ";
	}
	$sql = "SELECT * FROM `Devices` where ".$c." `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);

	$deviceArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$deviceId = $row["DeviceId"];
		$empId = $row["EmpId"];
		$mobile = $row["Mobile"];
		$token = $row["Token"];
		$name = $row["Name"];
		$make = $row["Make"];
		$model = $row["Model"];
		$os = $row["OS"];
		$appVer = $row["AppVer"];
		$active = $row["Active"];
		$registeredOn = $row["Registered"];
		
		$json = array(
			'deviceId' => $deviceId,
			'empId' => $empId,
			'mobile' => $mobile,
			'token' => $token,
			'name' => $name,
			'make' => $make,
			'model' => $model,
			'os' => $os,
			'appVer' => $appVer,
			'active' => $active,
			'registeredOn' => explode(" ", $registeredOn)[0],
		);
		array_push($deviceArr,$json);
	}
	$output = array();
	$output = array('deviceList' => $deviceArr);
	echo json_encode($output);
}
else if($selectType == "location"){
	$loginEmpState = $jsonData->loginEmpState;
	$filterSql = "";
	if($loginEmpRole == "Admin"){
		
	}
	else if($loginEmpRole == "RM"){
		$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
		$filterSql .= " and (el.Emp_Id in ($empSql) or el.Emp_Id = '$loginEmpId') ";
		
	}
	else if($loginEmpRole == "SH"){
		$explodeState = explode(",", $loginEmpState);
		$implodeState = implode("','", $explodeState);

		$empSql = "SELECT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = '$tenentId' and `Active` = 1";
		$filterSql .= " and (el.Emp_Id in ($empSql) or el.Emp_Id = '$loginEmpId') ";
	}
	$sql = "SELECT l.* FROM Location l join EmployeeLocationMapping el on l.LocationId = el.LocationId ".$filterSql." where 1=1 and l.Tenent_Id = $tenentId and l.Is_Active = 1";
	// echo $sql;
	$query=mysqli_query($conn,$sql);

	$locationArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$locId = $row["LocationId"];
		$state = $row["State"];
		$locName = $row["Name"];
		$siteType = $row["Site_Type"];
		$isActive = $row["Is_Active"];
		$active = $isActive == 1 ? "Active" : "Deactive";
		
		$json = array(
			'locId' => $locId,
			'state' => $state,
			'locName' => $locName,
			'siteType' => $siteType,
			'isActive' => $isActive,
			'active' => $active
		);
		array_push($locationArr,$json);
	}
	$output = array();
	$output = array('locationList' => $locationArr);
	echo json_encode($output);
}
else if($selectType == "mapping"){
	$loginEmpState = $jsonData->loginEmpState;
	$filterSql = "";
	if($loginEmpRole == "Admin"){
		
	}
	else if($loginEmpRole == "RM"){
		$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
		$filterSql .= " and m.EmpId in ($empSql) ";
		
	}
	$sql = "SELECT * FROM MappingView m where 1=1".$filterSql;
	$sql .= " Order by m.`MappingId` desc ";
	// echo $sql;
	$query=mysqli_query($conn,$sql);

	$mappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$mappingId = $row["MappingId"];	
		$empName = $row["Name"];
		$locationName = $row["SiteName"];
		$startDate = $row["Start"];
		$endDate = $row["End"];
		$active = $row["Active"];
		$tktStatus = $row["TktStatus"];
		$activityId = $row["ActivityId"];
		if($activityId != 0) $tktStatus .= " - ".$activityId;
		$json = array(
			'mappingId' => $mappingId,
			'empName' => $empName,
			'locationName' => $locationName,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'active' => $active,
			'tktStatus' => $tktStatus

		);
		array_push($mappingArr,$json);
	}
	$output = array();
	$output = array('mappingList' => $mappingArr);
	echo json_encode($output);
}
else if($selectType == "mapping_old"){
	
	$sql = "SELECT `m`.`MappingId`, `m`.`EmpId`, `e1`.`Name` as empName, `m`.`MenuId`, `Menu`.`Cat`, `Menu`.`Sub`, `Menu`.`Caption`, `m`.`LocationId`, `m`.`Verifier`, `m`.`Approver`, `m`.`Active`, `m`.`Start`, `m`.`End`, `l`.`Name` as locationName, `e2`.`Name` as verifierName, `e3`.`Name` as approverName, (case when m.`ActivityId` !=0 then 'Completed' when (m.`Start`<=curdate() and m.`End`>=curdate() and m.`ActivityId` = 0) then 'Pending' when m.`End`<curdate() and m.`ActivityId` = 0 then 'Delay' when (m.`Start`>curdate() and m.`End`>curdate() and m.`ActivityId` = 0) then 'Wait' end) as TktStatus 
	FROM `Mapping` m left join `Employees` e1 on `m`.`EmpId` = `e1`.`EmpId` 
	left join `Menu` on `m`.`MenuId` = `Menu`.`MenuId` 
	left join `Location` l on `m`.`LocationId` = `l`.`LocationId` 
	left join `Employees` e2 on `m`.`Verifier` = `e2`.`EmpId` 
	left join `Employees` e3 on `m`.`Approver` = `e3`.`EmpId` where `m`.`LocationId` != 1 and `m`.`Tenent_Id` = $tenentId order by `m`.`MappingId` desc";
	$query=mysqli_query($conn,$sql);

	$mappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$mappingId = $row["MappingId"];
		$empId = $row["EmpId"];
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$cat = $row["Cat"];
		$sub = $row["Sub"];
		$caption = $row["Caption"];
		$locId = $row["LocationId"];
		$verifier = $row["Verifier"];
		$approver = $row["Approver"];
		$active = $row["Active"];
		$startDate = $row["Start"];
		$endDate = $row["End"];
		$locationName = $row["locationName"];
		$verifierName = $row["verifierName"];
		$approverName = $row["approverName"];
		$tktStatus = $row["TktStatus"];
		$menuName = "";
		if($cat !=""){
			$menuName = $cat;
		}
		if($sub !=""){
			$menuName = $sub;
		}
		if($caption !=""){
			$menuName = $caption;
		}
		
		$json = array(
			'mappingId' => $mappingId,
			'empId' => $empId,
			'empName' => $empName,
			'MenuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'verifier' => $verifier,
			'approver' => $approver,
			'active' => $active,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'locationName' => $locationName,
			'verifierName' => $verifierName,
			'approverName' => $approverName,
			'tktStatus' => $tktStatus

		);
		array_push($mappingArr,$json);
	}
	$output = array();
	$output = array('mappingList' => $mappingArr);
	echo json_encode($output);
}
else if($selectType == "checkpoint"){
	$sql = "SELECT *, `Type`.`Type` as typeName FROM `Checkpoints` left join `Type` on `Checkpoints`.`TypeId` = `Type`.`TypeId` 
	where `Checkpoints`.`Tenent_Id` = $tenentId order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	//echo $sql;
	$checkpointArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointId = $row["CheckpointId"];
		$description = $row["Description"];
		$value = $row["Value"];
		$typeId = $row["TypeId"];
		$mandatory = $row["Mandatory"];
		$editable = $row["Editable"];
		$correct = $row["Correct"];
		$size = $row["Size"];
		$score = $row["Score"];
		$language = $row["Language"] == 1 ? "English" : "Hindi";
		$active = $row["Active"];
		$dependent = $row["Dependent"] == 1 ? "Yes" : "No";
		$logic = $row["Logic"];
		$typeName = $row["typeName"];

		if($typeId == "4"){
			$typeName = explode(",", $typeName)[$size];
		}

		//echo $description;
		
		$json = array(
			'checkpointId' => $checkpointId,
			'description' => $description,
			'value' => $value,
			//'description' => '',
			//'value' => '',
			'typeId' => $typeId,
			'mandatory' => $mandatory,
			'editable' => $editable,
			'correct' => $correct,
			'size' => $size,
			'score' => $score,
			'language' => $language,
			'active' => $active,
			'dependent' => $dependent,
			'logic' => $logic,
			'typeName' => $typeName

		);
		array_push($checkpointArr,$json);
	}
	$output = array();
	$output = array('checkpointList' => $checkpointArr);
	echo json_encode($output);
}

else if($selectType == "checkpointListArr"){
	$sql = "SELECT * FROM `Checkpoints` where `Tenent_Id` = $tenentId order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	//echo $sql;
	$checkpointListArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointIdd = $row["CheckpointId"];
		$descriptionn = $row["Description"];
		
		$json = array(
			'paramCode' => $checkpointIdd,
			'paramDesc' => $checkpointIdd.' '.$descriptionn
		);
		array_push($checkpointListArr,$json);
	}
	$output = array();
	$output = array('checkpointListArr' => $checkpointListArr);
	echo json_encode($output);
}

else if($selectType == "inputType"){
	$inputTypeSql = "SELECT `TypeId`,`Type` FROM `Type` ";
	$inputTypeQuery=mysqli_query($conn,$inputTypeSql);
	$inputTypeArr = array();
	while($inputTypeRow = mysqli_fetch_assoc($inputTypeQuery)){
		$typeId = $inputTypeRow["TypeId"];
		$typeName = $inputTypeRow["Type"];
		if($typeId == 4){
			$explodeTypeName = explode(",", $typeName);
			for($i=0;$i<count($explodeTypeName);$i++){
				$json = array(
					'typeId' => $typeId,
					'typeName' => $explodeTypeName[$i],
				);
				array_push($inputTypeArr,$json);
			}
		}
		else{
			$json = array(
				'typeId' => $typeId,
				'typeName' => $typeName,
			);
			array_push($inputTypeArr,$json);
		}
		
	}
	$output = array();
	$output = array('inputTypeList' => $inputTypeArr);
	echo json_encode($output);
}
else if($selectType == "checklist"){
	$checklistSql = "SELECT * FROM `Menu` where `Tenent_Id` = $tenentId order by `MenuId` desc ";
	$checklistQuery=mysqli_query($conn,$checklistSql);
	$checklistArr = array();
	while($checklistRow = mysqli_fetch_assoc($checklistQuery)){
		$menuId = $checklistRow["MenuId"];
		$category = $checklistRow["Cat"];
		$subcategory = $checklistRow["Sub"];
		$caption = $checklistRow["Caption"];
		$checkpoint = $checklistRow["CheckpointId"];
		$verifier = $checklistRow["Verifier"];
		$approver = $checklistRow["Approver"];
		$geoFence = $checklistRow["GeoFence"];
		$icons = $checklistRow["Icons"];
		$explodeIcons = explode(",", $icons);
		$catIcons = $explodeIcons[0];
		$subcatIcons = $explodeIcons[1];
		$capIcons = $explodeIcons[2];
		$active = $checklistRow["Active"];

		$json = array(
			'menuId' => $menuId,
			'category' => $category,
			'subcategory' => $subcategory,
			'caption' => $caption,
			'checkpoint' => $checkpoint,
			'verifier' => $verifier,
			'approver' => $approver,
			'geoFence' => $geoFence,
			'icons' => $icons,
			'catIcons' => $catIcons,
			'subcatIcons' => $subcatIcons,
			'capIcons' => $capIcons,
			'active' => $active
		);
		array_push($checklistArr,$json);
	}
	$output = array();
	$output = array('checklist' => $checklistArr);
	echo json_encode($output);
}

else if($selectType == "headerMenu"){

	$ss = "";
	if($loginEmpRole != 'Admin'){
		$sql = "SELECT * FROM `Employees` WHERE `EmpId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
		$query=mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query)){
			$headerMenuIdArr = explode(",", $row["Header_Menu_Id"]);
			$ss = " `Header_Menu_Id` in (".implode(",", $headerMenuIdArr).")  and ";
		}
	}

	$headerMenuArr = array();
	$headerMenuSql = "SELECT * FROM `Header_Menu` where ".$ss." `Is_Active` = 1 order by `Display_Order` desc ";
	$headerMenuQuery=mysqli_query($conn,$headerMenuSql);
	while($headerMenuRow = mysqli_fetch_assoc($headerMenuQuery)){
		$id = $headerMenuRow["Header_Menu_Id"];
		$menuName = $headerMenuRow["Name"];
		$routerLink = $headerMenuRow["Router_Link"];

		$json = array(
			'menuId' => $id,
			'menuName' => $menuName,
			'routerLink' => $routerLink
		);
		array_push($headerMenuArr,$json);
	}
	$output = array();
	$output = array('headerMenuList' => $headerMenuArr);
	echo json_encode($output);	
}
else if($selectType == "role"){
	$roleSql = "SELECT * FROM `Role` where `Role` != 'Admin' and `Tenent_Id` = $tenentId ";
	$roleQuery=mysqli_query($conn,$roleSql);
	$roleArr = array();
	while($roleRow = mysqli_fetch_assoc($roleQuery)){
		$roleId = $roleRow["RoleId"];
		$roleName = $roleRow["Role"];
		$verifierRole = $roleRow["Verifier_Role"];
		$approverRole = $roleRow["Approver_Role"];
		$menuId = $roleRow["MenuId"];

		$json = array(
			'roleId' => $roleId,
			'roleName' => $roleName,
			'verifierRole' => $verifierRole,
			'approverRole' => $approverRole,
			'menuId' => $menuId
		);
		array_push($roleArr,$json);
	}
	$output = array();
	$output = array('roleList' => $roleArr);
	echo json_encode($output);
}

else if($selectType == "caption"){
	$loginEmpRole = $jsonData->loginEmpRole;
	$categoryName = $jsonData->categoryName;
	$subCategoryName = $jsonData->subCategoryName;

	$capSql = "SELECT * FROM `Menu` where `Cat` = '$categoryName' and `Sub` = '$subCategoryName' ";
	$capQuery=mysqli_query($conn,$capSql);
	$capArr = array();
	while($capRow = mysqli_fetch_assoc($capQuery)){
		$menuId = $capRow["MenuId"];
		$caption = $capRow["Caption"];

		$json = array(
			'paramCode' => $menuId,
			'paramDesc' => $caption
		);
		array_push($capArr,$json);
	}
	$output = array();
	$output = array('captionList' => $capArr);
	echo json_encode($output);
}

else if($selectType == "portal_color"){

	$colorSql = "SELECT `Login_Page`, `Button`, `Color1`, `Color2` FROM `portal_color` ";
	$colorQuery=mysqli_query($conn,$colorSql);
	$colorArr = array();
	while($colorRow = mysqli_fetch_assoc($colorQuery)){
		$loginPage = $colorRow["Login_Page"];
		$button = $colorRow["Button"];
		$color1 = $colorRow["Color1"];
		$color2 = $colorRow["Color2"];

		$json = array(
			'loginPage' => $loginPage,
			'button' => $button,
			'color1' => $color1,
			'color2' => $color2,
		);
		array_push($colorArr,$json);
	}
	$output = array();
	$output = array('colorList' => $colorArr);
	echo json_encode($output);
}

else if($selectType == "previewCheckpointDetails"){
	$previewCheckpointId = $jsonData->previewCheckpointId;
	$checkpointPage = explode(":", $previewCheckpointId);

	$previewCheckpointDetails = array();

	for($i=0;$i<count($checkpointPage);$i++){
		// $checkpointIds = convertListInOperatorValue(explode(",", $checkpointPage[$i]));
		// $checkpointIds = implode(',',explode(",", $checkpointPage[$i]));

		$explodeCheckpoint = explode(",", $checkpointPage[$i]);
		$checkpointArr = array();
		for($ii=0;$ii<count($explodeCheckpoint);$ii++){
			$sql = "SELECT * FROM `Checkpoints` where `CheckpointId` in (".$explodeCheckpoint[$ii].")  ";
			$query=mysqli_query($conn,$sql);
			// echo $sql;
			
			while($row = mysqli_fetch_assoc($query)){
				$checkpointId = $row["CheckpointId"];
				$description = $row["Description"];
				$value = $row["Value"];
				$typeId = $row["TypeId"];
				$mandatory = $row["Mandatory"];
				$editable = $row["Editable"];
				$correct = $row["Correct"];
				$size = $row["Size"];
				$score = $row["Score"];
				$language = $row["Language"];
				$active = $row["Active"];
				$dependent = $row["Dependent"];
				$logic = $row["Logic"];

				//echo $description;
				$logicCpArr = array();
				if($dependent == 1){
					$logicCheckpoint = explode(":", $logic);
					for($j=0;$j<count($logicCheckpoint);$j++){
						// $logicCheckpoint2 = explode(",", $logicCheckpoint[$j]);
						// $logicCheckpointIds = convertListInOperatorValue($logicCheckpoint2);
						//$logicCheckpointIds = implode(',',explode(",", $logicCheckpoint[$j]));

						$explodeLogicCheckpoint = explode(",", $logicCheckpoint[$j]);
						for($jj=0;$jj<count($logicCheckpoint);$jj++){
							$logicCpSql = "SELECT * FROM `Checkpoints` where `CheckpointId` in (".$logicCheckpoint[$jj].")  ";
							$logicCpQuery=mysqli_query($conn,$logicCpSql);
							while($logicCpRow = mysqli_fetch_assoc($logicCpQuery)){
								$logicJson = array(
									'checkpointId' => $logicCpRow["CheckpointId"],
									'description' => $logicCpRow["Description"],
									'value' => $logicCpRow["Value"],
									'typeId' => $logicCpRow["TypeId"]

								);
								array_push($logicCpArr,$logicJson);
							}
						}	
					}
						
				}
				
				$json = array(
					'checkpointId' => $checkpointId,
					'description' => $description,
					'value' => $value,
					'typeId' => $typeId,
					'mandatory' => $mandatory,
					'editable' => $editable,
					'correct' => $correct,
					'size' => $size,
					'score' => $score,
					'language' => $language,
					'active' => $active,
					'dependent' => $dependent,
					'logic' => $logic,
					'logicCpArr' => $logicCpArr

				);
				array_push($checkpointArr,$json);
			}
		}

			
		$previewJson = array('page' => $i+1, 'checkpointArr' => $checkpointArr);
		array_push($previewCheckpointDetails,$previewJson);
	}
	

	$output = array();
	$output = array('previewCheckpointDetails' => $previewCheckpointDetails);
	echo json_encode($output);
}

else if($selectType == "state"){
	$sql = "SELECT distinct `State` FROM `StateCityAreaMaster` where `State` is not null order by `State` ";
	$query=mysqli_query($conn,$sql);
	$stateList = array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($stateList,$row["State"]);
	}

	$output = array();
	$output = array('state' => array_unique($stateList));
	echo json_encode($output);

}
else if($selectType == "stateHqList"){
	$state = $jsonData->state;
	$sql = "SELECT `City` FROM `StateCityAreaMaster` where `State` = '$state' ";
	$query=mysqli_query($conn,$sql);
	$stateList = array();
	$row = mysqli_fetch_assoc($query);
	$hq = $row["City"];
	$hqExplode = explode(",", $hq);
	$stateHqList = array();
	for($i=0; $i<count($hqExplode); $i++){
		$json = array('paramCode' => $hqExplode[$i], 'paramDesc' => $hqExplode[$i].' ');
		array_push($stateHqList,$json);
	}
	
	$output = array();
	$output = array('stateHqList' => $stateHqList);
	echo json_encode($output);
}

else if($selectType == "city"){
	$state = $jsonData->state;
	$explodeState = explode(",", $state);
	$implodeState = implode("','", $explodeState);
	$sql = "SELECT distinct `City` FROM `StateCityAreaMaster` where  `State` in ('".$implodeState."') order by `City`  ";
	$query=mysqli_query($conn,$sql);
	$cityList = array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($cityList,$row["City"]);
	}

	$output = array();
	$output = array('city' => array_unique($cityList));
	echo json_encode($output);

}

else if($selectType == "area"){
	$state = $jsonData->state;
	$explodeState = explode(",", $state);
	$implodeState = implode("','", $explodeState);
	$city = $jsonData->city;
	$explodeCity = explode(",", $city);
	$implodeCity = implode("','", $explodeCity);
	$sql = "SELECT distinct `Area` FROM `StateCityAreaMaster` where `State` in ('".$implodeState."')  and `City` in ('".$implodeCity."') order by `Area`  ";
	$query=mysqli_query($conn,$sql);
	$areaList = array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($areaList,$row["Area"]);
	}

	$output = array();
	$output = array('area' => array_unique($areaList));
	echo json_encode($output);

}
else if($selectType == "dynamicColumn"){
	$menuId = $jsonData->menuId;
	$sql = "SELECT * FROM `Dynamic_Column_Header` where `Menu_Id` = '$menuId' and `Is_Active` = 1 ORDER by `Display_Order` ";
	$query=mysqli_query($conn,$sql);
	$dynamicColumnList = array();
	while($row = mysqli_fetch_assoc($query)){
		$data = array('columnKey' => $row["Column_Key"], 'columnTitle' => $row["Column_Title"], 'columnWidth' => $row["Column_Width"]);
		array_push($dynamicColumnList, $data);
	}
	$output = array('dynamicColumn' => $dynamicColumnList);
	echo json_encode($output);
}
else if($selectType == "incidentCategory"){
	$sql = "SELECT `Value` FROM `Checkpoints` WHERE `CheckpointId` = 4789 and `Tenent_Id` = 2";
	$query=mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($query);

	$output = array('incidentCategory' => $row["Value"]);
	echo json_encode($output);
}
else if($selectType == "siteType"){
	$sql = "SELECT DISTINCT `Site_CAT` FROM `Location` where `Site_CAT` is not null and `Site_CAT` !='' and  `Tenent_Id` = 2";
	$query=mysqli_query($conn,$sql);
	$siteTypeList = array();
	while($row = mysqli_fetch_assoc($query)){
		$st = $row["Site_CAT"];
		$sTJson = array('paramCode' => $st, 'paramDesc' => $st.' ' );
		array_push($siteTypeList, $sTJson);
	}

	$sql2 = "SELECT DISTINCT Airport_Metro FROM `Location` where Airport_Metro is not null and Airport_Metro != '' and Airport_Metro != '0' 
	and `Tenent_Id` = 2 ";
	$query2=mysqli_query($conn,$sql2);
	$metroSiteTypeList = array();
	while($row2 = mysqli_fetch_assoc($query2)){
		$mst = $row2["Airport_Metro"];
		$msTJson = array('paramCode' => $mst, 'paramDesc' => $mst.' ' );
		array_push($metroSiteTypeList, $msTJson);
	}
	$json1 = array('paramCode' => 'High_R_Site', 'paramDesc' => 'High Revenue Site' );
	array_push($metroSiteTypeList, $json1);
	$json2 = array('paramCode' => 'ISQ', 'paramDesc' => 'ISQ ' );
	array_push($metroSiteTypeList, $json2);
	$json3 = array('paramCode' => 'Retail_IBS', 'paramDesc' => 'Retail IBS' );
	array_push($metroSiteTypeList, $json3);

	$output = array('siteType' => $siteTypeList, 'metroSiteType' => $metroSiteTypeList);
	echo json_encode($output);
}
else if($selectType == "siteSurveyCheckpoint"){
	$sql = "SELECT CheckpointId FROM Menu where MenuId = 279 and Tenent_Id = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($query);
	$checkpoint = $row["CheckpointId"];
	$exCp = explode(",", $checkpoint);
	$imCp = implode(",", $exCp);
	$cpSql = "select * from Checkpoints where CheckpointId in (".$imCp.") and Tenent_Id = $tenentId";
	$cpQuery=mysqli_query($conn,$cpSql);
	$siteSurveyCpList = array();
	while($cpRow = mysqli_fetch_assoc($cpQuery)){
		$json = new StdClass;
		$json -> chkpId = $cpRow["CheckpointId"];
		$json -> description = $cpRow["Description"];
		$json -> value = $cpRow["Value"];
		$json -> typeId = $cpRow["TypeId"];
		$json -> mandatory = $cpRow["Mandatory"];
		$json -> editable = $cpRow["Editable"];
		$json -> correct = $cpRow["Correct"];
		$json -> size = $cpRow["Size"];
		$json -> Score = $cpRow["Score"];
		$json -> language = $cpRow["Language"];
		$json -> Active = $cpRow["Active"];
		$json -> Is_Dept = $cpRow["Dependent"];
		$json -> Logic = $cpRow["Logic"];
		$json -> answer = "";
		if($cpRow['IsSql'] == 1){
		   // $empId = "34";
		    $valueSql = $cpRow["Value"];
		    $stmt = mysqli_prepare($conn,$valueSql);
		    mysqli_stmt_bind_param($stmt, 'si', $loginEmpId,$tenentId);
		    mysqli_stmt_execute($stmt);
		    mysqli_stmt_store_result($stmt);
		    mysqli_stmt_bind_result($stmt,$project);
		    if(mysqli_stmt_num_rows($stmt) > 0){
		       $valueArray = array();
		       while($v = mysqli_stmt_fetch($stmt)){
		            array_push($valueArray,$project);
		       }
		       $json -> value =implode(',',$valueArray); 
			
		    }
		    else{
		        $json -> value = "";    
		    }
		    mysqli_stmt_close($stmt);
		}
		else{
		    $json -> value = $cpRow["Value"];    
		}
		array_push($siteSurveyCpList,$json);
	}
	$output = array('siteSurveyCheckpoint' => $siteSurveyCpList);
	echo json_encode($output);
}
else if($selectType == "ptwCheckpoint"){
	$sql = "SELECT CheckpointId FROM Menu where MenuId = 286 and Tenent_Id = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($query);
	$checkpoint = $row["CheckpointId"];
	$exCp = explode(",", $checkpoint);
	$imCp = implode(",", $exCp);
	$cpSql = "select * from Checkpoints where CheckpointId in (".$imCp.") and Tenent_Id = $tenentId";
	$cpQuery=mysqli_query($conn,$cpSql);
	$ptwCpList = array();
	while($cpRow = mysqli_fetch_assoc($cpQuery)){
		$typeId = $cpRow["TypeId"];
		$value = $cpRow["Value"];
		$explodeValue = explode(",", $value);
		$valueList = array();
		for($i=0;$i<count($explodeValue); $i++){
			$v = $explodeValue[$i];
			$json = array(
				'paramCode' => $v,
				'paramDesc' => $v.' '
			);
			array_push($valueList,$json);
		}
		
		$json = new StdClass;
		$json -> chkpId = $cpRow["CheckpointId"];
		$json -> description = $cpRow["Description"];
		$json -> value = $cpRow["Value"];
		$json -> typeId = $cpRow["TypeId"];
		$json -> mandatory = $cpRow["Mandatory"];
		$json -> editable = $cpRow["Editable"];
		$json -> correct = $cpRow["Correct"];
		$json -> size = $cpRow["Size"];
		$json -> Score = $cpRow["Score"];
		$json -> language = $cpRow["Language"];
		$json -> Active = $cpRow["Active"];
		$json -> Is_Dept = $cpRow["Dependent"];
		$json -> Logic = $cpRow["Logic"];
		$json -> answer = "";
		if($cpRow['IsSql'] == 1){
		   // $empId = "34";
		    $valueSql = $cpRow["Value"];
		    $stmt = mysqli_prepare($conn,$valueSql);
		    mysqli_stmt_bind_param($stmt, 'si', $loginEmpId,$tenentId);
		    mysqli_stmt_execute($stmt);
		    mysqli_stmt_store_result($stmt);
		    mysqli_stmt_bind_result($stmt,$project);
		    if(mysqli_stmt_num_rows($stmt) > 0){
		       $valueArray = array();
		       while($v = mysqli_stmt_fetch($stmt)){
		            array_push($valueArray,$project);
		       }
		       $json -> value =implode(',',$valueArray); 
			
		    }
		    else{
		        $json -> value = "";    
		    }
		    mysqli_stmt_close($stmt);
		}
		else{
		    $json -> value = $cpRow["Value"];    
		}
		$json -> valueList = $valueList;
		array_push($ptwCpList,$json);
	}
	$output = array('ptwCheckpoint' => $ptwCpList);
	echo json_encode($output);
}
else if($selectType == "appMenu"){
	$sql = "SELECT * FROM `AppMenu`";
	$query=mysqli_query($conn,$sql);
	$appMenuList = array();
	while($row = mysqli_fetch_assoc($query)){
		$data = array('menuId' => $row["Menu_Id"], 'menuName' => $row["Menu_Name"], 'url' => $row["URL"]);
		array_push($appMenuList, $data);
	}
	$output = array('appMenu' => $appMenuList);
	echo json_encode($output);
}
else if($selectType == "allType"){
	$sql = "SELECT distinct `State` FROM `StateCityAreaMaster` where `State` is not null and `State` != '' order by `State` ";
	$query=mysqli_query($conn,$sql);
	$stateList = array();
	while($row = mysqli_fetch_assoc($query)){
		$state = $row["State"];
		$stateJson = array('paramCode' => $state, 'paramDesc' => $state.' ');
		array_push($stateList,$stateJson);
	}

	$sql = "SELECT DISTINCT `Site_Type` FROM `Location` where `Site_Type` is not null and `Site_Type` != '' ";
	$query=mysqli_query($conn,$sql);
	$siteTypeList = array();
	while($row = mysqli_fetch_assoc($query)){
		$siteType = $row["Site_Type"];
		$siteJson = array('paramCode' => $siteType, 'paramDesc' => $siteType.' ');
		array_push($siteTypeList,$siteJson);
	}

	$supervisorList = array();
	$sql = "SELECT * FROM `Employees` where `RMId` = '$loginEmpId' and `RoleId` = 54 and `Active` = 1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$supJson = array('name' => $row["Name"], 'mobile' => $row["Mobile"], 'whatsapp' => $row["Whatsapp_Number"], 'aadharCard' => $row["AadharCard_Number"]);
		array_push($supervisorList,$supJson);
	}


	$output = array('circleList' => $stateList, 'siteTypeList'=>$siteTypeList, 'supervisorList' => $supervisorList);
	echo json_encode($output);

}

else if($selectType == "siteNameByCircle"){
	$circleName = $jsonData->circleName;
	$sql = "SELECT concat(`Name`,' --- ',`Site_Id`) as SiteIdName FROM `Location` where `State` = '$circleName' ";
	$query=mysqli_query($conn,$sql);
	$siteIdNameList = array();
	while($row = mysqli_fetch_assoc($query)){
		$siteIdName = $row["SiteIdName"];
		$siteIdNameJson = array('paramCode' => $siteIdName, 'paramDesc' => $siteIdName.' ');
		array_push($siteIdNameList,$siteIdNameJson);
	}
	$output = array('siteIdNameList' => $siteIdNameList);
	echo json_encode($output);

}
else if($selectType == "vendorCreation"){
	$sql = "SELECT distinct `State` FROM `StateCityAreaMaster` where `State` is not null and `State` != '' order by `State` ";
	$query=mysqli_query($conn,$sql);
	$stateList = array();
	while($row = mysqli_fetch_assoc($query)){
		$outJson = array('paramCode' => $row["State"], 'paramDesc' => $row["State"].' ');
		array_push($stateList,$outJson);
	}

	$sql = "SELECT `Value` from `Checkpoints` where `CheckpointId` = 5511 ";
	$query=mysqli_query($conn,$sql);
	$vendorTypeList = array();
	while($row = mysqli_fetch_assoc($query)){
		$valueList = explode(",", $row["Value"]);
		for($ii = 0;$ii<count($valueList);$ii++){
			$loopValue = $valueList[$ii];
			$outJson = array('paramCode' => $loopValue, 'paramDesc' => $loopValue.' ');
			array_push($vendorTypeList,$outJson);
		}
			
	}

	$output = array();
	$output = array('stateList' => $stateList, 'vendorTypeList' => $vendorTypeList);
	echo json_encode($output);
}
else if($selectType == "vendor"){
	$sql = "SELECT *  FROM `Employees` WHERE `RoleId` = 53";
	$query=mysqli_query($conn,$sql);
	$vendorList = array();
	while($row = mysqli_fetch_assoc($query)){
		$outJson = array('id' => $row["Id"], 'vendorName' => $row["Name"], 'vendorCode' => $row["EmpId"], 'vendorType' => $row["VendorType"], 'vendorState' => $row["State"], 'vendorMobile' => $row["Mobile"]);
		array_push($vendorList,$outJson);
	}
	$output = array('vendorList' => $vendorList);
	echo json_encode($output);
}
else if($selectType == "attendance"){
	$loginEmpState = $jsonData->loginEmpState;
	$underEmpList = array();
	if($loginEmpRole == "Admin"){
		$sql = "SELECT `EmpId` FROM `Employees` WHERE `Tenent_Id` = '$tenentId' and `Active` = 1";
		$query=mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query)){
			array_push($underEmpList, $row["EmpId"]);
		}
	}
	else if($loginEmpRole == "RM"){
		$sql = "SELECT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = '$tenentId' and `Active` = 1";
		$query=mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query)){
			array_push($underEmpList, $row["EmpId"]);
		}
		// for self data
		array_push($underEmpList, $loginEmpId);
	}
	else if($loginEmpRole == "SH"){
		$explodeState = explode(",", $loginEmpState);
		$implodeState = implode("','", $explodeState);

		$sql = "SELECT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = '$tenentId' and `Active` = 1";
		$query=mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query)){
			array_push($underEmpList, $row["EmpId"]);
		}
		// for self data
		array_push($underEmpList, $loginEmpId);
	}
	else{
		// for self data
		array_push($underEmpList, $loginEmpId);
	}

	$empIds = implode("','", $underEmpList);

	$attendanceList = array();
	$filterSql = "";
	$filterStartDate = $jsonData->filterStartDate;
	$filterEndDate = $jsonData->filterEndDate;

	if($filterStartDate != ""){
		$filterSql .= " and `AttendanceDate` >= '$filterStartDate' ";
	}
	if($filterEndDate != ""){
		$filterSql .= " and `AttendanceDate` <= '$filterEndDate' ";
	}

	$attSql = "SELECT * FROM `Attendance` where `EmpId` in ('".$empIds."') ".$filterSql." ORDER by `AttendanceDate` desc";
	$attQuery=mysqli_query($conn,$attSql);
	while($attRow = mysqli_fetch_assoc($attQuery)){
		$inDateTime = $attRow["InDateTime"] == null ? '' : $attRow["InDateTime"];
		$outDateTime = $attRow["OutDateTime"] == null ? '' : $attRow["OutDateTime"];
		$workingHours = $attRow["WorkingHours"] == null ? '' : $attRow["WorkingHours"];
		$inLatlong = $attRow["InLatlong"] == null ? '' : $attRow["InLatlong"];
		$outLatlong = $attRow["OutLatlong"] == null ? '' : $attRow["OutLatlong"];

		$attJson = array('empId' => $attRow["EmpId"], 'name' => $attRow["Name"], 'attendanceDate' => $attRow["AttendanceDate"], 
			'inDateTime' => $inDateTime, 'outDateTime' => $outDateTime, 'workingHours' => $workingHours, 'inLatlong' => $inLatlong, 
			'outLatlong' => $outLatlong);
		array_push($attendanceList, $attJson);
	}
	$output = array('attendanceList' => $attendanceList);
	echo json_encode($output);
}

//Close the connection 
// $conn->close();

?>

<?php
function convertListInOperatorValue($arrName){
	$inOperatorValue = "";
	for ($x = 0; $x < count($arrName); $x++) {
		$inOperatorValue = $inOperatorValue."'".$arrName[$x]."'";
		if($x < count($arrName)-1){
			$inOperatorValue = $inOperatorValue.",";
		}
	}
	return $inOperatorValue;
}
?>