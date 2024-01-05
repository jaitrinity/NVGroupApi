<?php 
include("dbConfiguration.php");

$successArr = array();
$errorArr = array();

$yesterdayDate = $_REQUEST["yesterdayDate"];
// echo $yesterdayDate;
$sql = "SELECT DISTINCT `Emp_Id` FROM `Distance` where `Visit_Date` = '$yesterdayDate'";
$query = mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$checkInTime = "";
	$checkOutTime = "";
	$distance = "";
	$unplanCount = 0;
	$workingHours = 0;
	$empId = $row["Emp_Id"];
	$startSql = "SELECT 'CheckIn' as `Type`, `Visit_Date_Time` as `Value`, 0 as UnplanCount FROM `Distance` where `Visit_Date` = '$yesterdayDate' and `Event` = 'Start' and `Emp_Id` = '$empId' order by `Visit_Date_Time` asc limit 0,1";
	$endSql = "SELECT 'CheckOut' as `Type`, `Visit_Date_Time` as `Value`, 0 as UnplanCount FROM `Distance` where `Visit_Date` = '$yesterdayDate' and `Event` = 'Stop' and `Emp_Id` = '$empId' order by `Visit_Date_Time` desc limit 0,1";
	$distanceSql = "SELECT 'TotalDistance' as `Type`, sum(`Distance_KM`) as `Value`, sum(case when `RowType` = 'Unplan' then 1 else 0 end) as UnplanCount FROM `Distance` where `Visit_Date` = '$yesterdayDate' and `Emp_Id` = '$empId'";

	$sql1 = '('.$startSql.') UNION ('.$endSql.') UNION ('.$distanceSql.')';
	// echo $sql1;
	$query1 = mysqli_query($conn,$sql1);
	while($row1 = mysqli_fetch_assoc($query1)){
		$type = $row1["Type"];
		$value = $row1["Value"];
		$uC = $row1["UnplanCount"];
		if($type == "CheckIn"){
			$checkInTime = $value;
		}
		else if($type == "CheckOut"){
			$checkOutTime = $value;
		}
		else if($type == "TotalDistance"){
			$distance = $value;
			$unplanCount = $uC;
		}
	}

	if($checkInTime != "" && $checkOutTime != ""){
		$whSql = "SELECT CONCAT(FLOOR(t.WorkingMint/60),'H ',MOD(t.WorkingMint,60),'M') as WorkingHours from (SELECT TIMESTAMPDIFF(MINUTE,'$checkInTime','$checkOutTime') WorkingMint from DUAL) t";
		$whQuery = mysqli_query($conn,$whSql);
		$whRow = mysqli_fetch_assoc($whQuery);
		$workingHours = $whRow["WorkingHours"];
	}

	$sql2 = "SELECT count(`Start`) as `VisitsPlanned`, sum(case when TktStatus = 'Completed' then 1 else 0 end) as `ActualVisits` FROM `MappingView` where `EmpId` = '$empId' and `Start` = '$yesterdayDate' and `Active` = 1 GROUP by `EmpId`, `Start`";
	$query2 = mysqli_query($conn,$sql2);
	$row2 = mysqli_fetch_assoc($query2);
	$visitsPlanned = $row2["VisitsPlanned"];
	$actualVisits = $row2["ActualVisits"];
	$visitsPlanned = $visitsPlanned == null ? 0 : $visitsPlanned;
	$actualVisits = $actualVisits == null ? 0 : $actualVisits;

	$insertTaDaSql = "INSERT INTO `TA_DA_Report`(`Employee Code`, `Date`, `Check-In Time`, `Check-Out Time`, `WorkingHours`, `Visits Planned`, `Actual Visits`, `KMS Travelled`, `UnplannedCount`, `CreateDate`) VALUES ('$empId', '$yesterdayDate', '$checkInTime', '$checkOutTime', '$workingHours', '$visitsPlanned', '$actualVisits', '$distance', $unplanCount, current_timestamp)";
	// echo $insertTaDaSql;
	if(mysqli_query($conn,$insertTaDaSql)){
		array_push($successArr, $empId);
	}
	else{
		array_push($errorArr, $empId);
	}
}

$output = array('date' => $yesterdayDate, 'successArr' => $successArr, 'errorArr' => $errorArr);
echo json_encode($output);
?>