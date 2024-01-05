<?php
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$empId = $jsonData->empId;
$visitDate = $jsonData->visitDate;

$sql = "SELECT d.Activity_Id, h.Site_Name, d.Visit_Date_Time, concat(d.Latitude_Start,',',d.Longitude_Start) as `LatLong`, d.Distance_KM, d.RowType  FROM Distance d left join TransactionHDR h on d.Activity_Id = h.ActivityId where `Emp_Id` = '$empId' and `Visit_Date` = '$visitDate';";
$query=mysqli_query($conn,$sql);
$visitList = array();
$visitCount = 0;
$travelDistance = 0;
while($row = mysqli_fetch_assoc($query)){
	$rowType = $row["RowType"];
	$distanceKM = $row["Distance_KM"];
	$travelDistance += $distanceKM;
	$vc="";
	if($rowType == "Unplan"){
		$visitCount++;
		$vc = $visitCount;
	}

	$visitJson = array(
		'Activity_Id'=>$row["Activity_Id"],
		'Site_Name'=>$row["Site_Name"],
		'Visit_Date_Time'=>$row["Visit_Date_Time"],
		'LatLong'=>$row["LatLong"],
		"Distance_KM"=>$distanceKM,
		'RowType'=>$rowType,
		'VisitCount'=>$vc
	);

	array_push($visitList, $visitJson);
}
$output = array(
	'visitCount' => $visitCount, 
	'travelDistance' => round($travelDistance,2), 
	'visitList' => $visitList
);
echo json_encode($output);

// $sql = "SELECT `UnplannedCount` as visitCount, round(`KMS Travelled`,2) as travelDistance FROM `TA_DA_Report` where `Employee Code` = '$empId' and `Date` = '$visitDate';";
// $query=mysqli_query($conn,$sql);
// $row = mysqli_fetch_assoc($query);
// echo json_encode($row);

?>