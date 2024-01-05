<?php 
include("dbConfiguration.php");
$todayDate = $_REQUEST["todayDate"];
$empIdd = $_REQUEST["empId"];

$filterSql = "";
if($empIdd == "all"){
	// for all employee attendance.
}
else{
	$filterSql = "and `EmpId` = '$empIdd' ";
}

$startSql = "SELECT DISTINCT `EmpId` FROM `Activity` where 1=1 ".$filterSql." and date(`MobileDateTime`) = '$todayDate' and `Event` = 'start' ";

$successArr = array();
$errorArr = array();
$startQuery=mysqli_query($conn,$startSql);
while($row = mysqli_fetch_assoc($startQuery)){
	$empId = $row["EmpId"];
	if(in_array($empId, $successArr)){
		// 
	}
	else{
		$eventSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and date(`MobileDateTime`) = '$todayDate' and `Event` in ('start','stop') ORDER BY `MobileDateTime` desc LIMIT 0,1";
		$eventQuery = mysqli_query($conn,$eventSql);
		$eventRow = mysqli_fetch_assoc($eventQuery);
		$evt = $eventRow["Event"];
		if($evt == "start"){
			$dId = $eventRow["DId"];
			$mappingId = $eventRow["MappingId"];
			$menuId = $eventRow["MenuId"];
			$locationId = $eventRow["LocationId"];
			$geolocation = $eventRow["GeoLocation"];
			$distance = $eventRow["Distance"];
			$mobileDateTime = $eventRow["MobileDateTime"];
			$tenentId = $eventRow["Tenent_Id"];

			// $insertStop = "INSERT into `Activity` (`DId`, `MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `Distance`, `Tenent_Id`, `MobileDateTime`) Values ($dId, '$mappingId', '$empId', $menuId, '$locationId', 'stop', '$geolocation', '$distance', $tenentId, current_timestamp) ";
			
			$insertStop = "INSERT into `Activity` (`DId`, `MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `Distance`, `Tenent_Id`, `MobileDateTime`) Values ($dId, '$mappingId', '$empId', $menuId, '$locationId', 'stop', '$geolocation', '$distance', $tenentId, '$mobileDateTime') ";
			if(mysqli_query($conn,$insertStop)){
				array_push($successArr, $empId);
			}
			else{
				array_push($errorArr, $empId);
			}
		}
	}
}

$output = new StdClass;
$output -> startStopResponse = array('date' => $todayDate, 'successArr' => $successArr, 'errorArr' => $errorArr);

echo json_encode($output);

// file_put_contents('/var/www/trinityapplab.in/html/NVGroup/log/attedanceStartStoplog_'.date("Y").'.log', json_encode($output)."\n", FILE_APPEND);

?>