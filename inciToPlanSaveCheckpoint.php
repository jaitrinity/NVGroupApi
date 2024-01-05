<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
mysqli_set_charset($conn,'utf8');

include("dbConfiguration.php");
require 'EmployeeTenentId.php';

$json = file_get_contents('php://input');
$jsonData=json_decode($json,true);
$req = $jsonData[0];

$mapId=$req['mappingId'];
$empId=$req['Emp_id'];
$mId=$req['M_Id'];
$lId=$req['locationId'];
$event=$req['event'];
$geolocation=$req['geolocation'];
$geolocation = str_replace(",", "/", $geolocation);
$distance=$req['distance'];
$mobiledatetime=$req['mobiledatetime'];
$fakeGpsMessage=$req['fakeGpsMessage'];

$caption = $req['caption'];
$transactionId = $req['timeStamp'];
$checklist = $req['checklist'];
$dId = $req['did'];
$assignId = $req['assignId'];
$actId = $req['activityId'];
$lastTransHdrId = "";
$activityId = 0;

if ((strpos($mobiledatetime, 'AM') !== false) || (strpos($mobiledatetime, 'PM')) || (strpos($mobiledatetime, 'am') !== false) || (strpos($mobiledatetime, 'pm')))   {
	$date = date_create_from_format("Y-m-d h:i:s A","$mobiledatetime");
	$date1 = date_format($date,"Y-m-d H:i:s");
}
else{
	$date1 = $mobiledatetime;
}

 if($lId == ""){
 	$lId = '1';
 }

 if($mId == ''){
 	$mId = '0';
 }

 if($mapId == ''){
 	$mapId = '0';
 }
 
 if($actId == ''){
	 $actId = null;
 }

 if($event == 'Submit'){
 	$existSql = "SELECT `ActivityId` FROM `Activity` where `MobileTimestamp` = '$transactionId' and Event = 'Submit'";
	$existResult = mysqli_query($conn,$existSql);
	$existRowCount=mysqli_num_rows($existResult);
	if($existRowCount !=0){
		$existrow = mysqli_fetch_assoc($existResult);
		$existActId = $existrow["ActivityId"];
		$existOutput = "";
		$existOutput -> error = "200";
		$existOutput -> message = "success";
		$existOutput -> TransID = "$existActId";
		echo json_encode($existOutput);
		return;
	}

	$siteTypeCpIdList = array();
	$cpIdSql = "SELECT `CheckpointId`  FROM `Checkpoints` WHERE `Description` = 'Choose Location *'";
	$query1 = mysqli_query($conn,$cpIdSql);
	while($row1 = mysqli_fetch_assoc($query1)){
		array_push($siteTypeCpIdList, $row1["CheckpointId"]);
	}

	$shopNameCpIdList = array();
	$cpIdSql = "SELECT `CheckpointId`  FROM `Checkpoints` WHERE `Description` = 'Shop name *'";
	$query2 = mysqli_query($conn,$cpIdSql);
	while($row2 = mysqli_fetch_assoc($query2)){
		array_push($shopNameCpIdList, $row2["CheckpointId"]);
	}

	$hotelNameCpIdList = array();
	$cpIdSql = "SELECT `CheckpointId`  FROM `Checkpoints` WHERE `Description` = 'Hotel name *'";
	$query3 = mysqli_query($conn,$cpIdSql);
	while($row3 = mysqli_fetch_assoc($query3)){
		array_push($hotelNameCpIdList, $row3["CheckpointId"]);
	}

	$officeNameCpIdList = array();
	$cpIdSql = "SELECT `CheckpointId`  FROM `Checkpoints` WHERE `Description` = 'Office name *'";
	$query4 = mysqli_query($conn,$cpIdSql);
	while($row4 = mysqli_fetch_assoc($query4)){
		array_push($officeNameCpIdList, $row4["CheckpointId"]);
	}


 	$classObj = new EmployeeTenentId();
	$empInfo = $classObj->getEmployeeInfo($conn,$empId);
	$tenentId = $empInfo["tenentId"];
	$state = $empInfo["state"];

	// for Incident
	$type = "";
	if($actId == null && $actId == ''){
		$type .= "1,";
		$activitySql = "Insert into Activity(DId,MappingId,EmpId,MenuId,LocationId,Event,GeoLocation,Distance,MobileDateTime,MobileTimestamp,Tenent_Id)"
						." values ('$dId','$mapId','$empId','$mId','$lId','$event','$geolocation','$distance','$date1','$transactionId',$tenentId)";
		//echo $activitySql;
		if(mysqli_query($conn,$activitySql)){
			$activityId = mysqli_insert_id($conn);
		}
		
		if($checklist != null && count($checklist) != 0){
			$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Start`, `End`, `ActivityId`, `Tenent_Id`, `CreateDateTime`) 
			values ('$empId', '$mId', '$lId', curdate(), curdate(), '$activityId', '$tenentId', current_timestamp) ";
			mysqli_query($conn,$insertMapping);

			$mappingId = $conn->insert_id;
			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`,`Lat_Long`, `FakeGPS_App`) VALUES 
			('$activityId','Created','$geolocation','$fakeGpsMessage')";
			
			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;
				$newlocId = 1;
				$siteName = "";
				$siteType = "";
				foreach($checklist as $k=>$v)
				{
					$answer=$v['value'];
					$chkp_idArray=explode("_",$v['Chkp_Id']);

					if(count($chkp_idArray) > 1){
						// for dependent
						$chkp_id = $chkp_idArray[1];
						if(in_array($chkp_id, $shopNameCpIdList)){
							$exp = explode(" --- ", $answer);
							$newlocId = $exp[0];
							$siteName = $exp[1];
						}
						else if(in_array($chkp_id, $hotelNameCpIdList)){
							$exp = explode(" --- ", $answer);
							$newlocId = $exp[0];
							$siteName = $exp[1];
						}
						else if(in_array($chkp_id, $officeNameCpIdList)){
							$exp = explode(" --- ", $answer);
							$newlocId = $exp[0];
							$siteName = $exp[1];
						}
					}
					else{
						$chkp_id = $chkp_idArray[0];
						if(in_array($chkp_id, $siteTypeCpIdList)){
							$siteType = $answer;
						}
					}	
					
					$dependent=$v['Dependent'];
					if($dependent == ""){
						$dependent = 0;
					}
					
					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
					mysqli_query($conn,$insertInTransDtl);
					
				}
				
				if($siteName !=""){
					$updateHdr = "UPDATE TransactionHDR set Site_Name = '$siteType - $siteName' where SRNo = $lastTransHdrId ";
					mysqli_query($conn,$updateHdr);
				}

				$newMenuId = 1;
				if($siteType == 'Shop'){
					$newMenuId = 12;
				}
				else if($siteType == 'Hotel'){
					$newMenuId = 13;
				}
				else if($siteType == 'Office'){
					$newMenuId = 14;
				}
				$updateAssignTaskSql = "UPDATE `Mapping` set `MenuId` = $newMenuId, `LocationId` = '$newlocId', `Verifier` = '$empId', `Approver` = '$empId' where `MappingId` = $mappingId";
				mysqli_query($conn,$updateAssignTaskSql);
			}
				
		}
	}

	$output = "";
	if($lastTransHdrId != ""){
		$output -> error = "200";
		$output -> message = "success - inc - $type";
		$output -> TransID = "$activityId";
	}
	else{
		$output -> error = "0";
		$output -> message = "something wrong";
		$output -> TransID = "$activityId";
	}
	echo json_encode($output);
	// file_put_contents('/var/www/trinityapplab.in/html/NVGroup/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
	
}

?>