<?php
include("dbConfiguration.php");
require 'EmployeeTenentId.php';

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.co.in/NVGroup/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json,true);
$req = $jsonData[0];
//echo json_encode($req);

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

// if($mId == 9){
// 	$result1 = CallAPI("POST","http://www.trinityapplab.in/NVGroup/inciToPlanSaveCheckpoint.php",$json);
// 	echo $result1;
// 	return;
// }


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
		$existOutput = new StdClass;
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

	// for Incident and First TODO task user.
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
					$siteName = "";
					$siteType = "";
					$shopName = "";
					$hotelName = "";
					$officeName = "";
					$l13Name = "";
					$shopType = "";
					foreach($checklist as $k=>$v)
					{
						$answer=$v['value'];
						$chkp_idArray=explode("_",$v['Chkp_Id']);

						if(count($chkp_idArray) > 1){
							// for dependent
							$chkp_id = $chkp_idArray[1];
							// if($chkp_id == 2 || $chkp_id == 11 || $chkp_id == 160){
							if(in_array($chkp_id, $shopNameCpIdList)){
								$shopName = $answer;
								$siteName = $answer;
							}
							// if($chkp_id == 7 || $chkp_id == 12 || $chkp_id == 161){
							if(in_array($chkp_id, $hotelNameCpIdList)){
								$hotelName = $answer;
								$siteName = $answer;
							}
							// if($chkp_id == 8 || $chkp_id == 13 || $chkp_id == 162){
							if(in_array($chkp_id, $officeNameCpIdList)){
								$officeName = $answer;
								$siteName = $answer;
							}
							if($chkp_id == 221 || $chkp_id == 222){
								$l13Name = $answer;
								$siteName = $answer;
							}
							if($chkp_id == 177){
								$shopType = $answer;
							}
						}
						else{
							$chkp_id = $chkp_idArray[0];
							// if($chkp_id == 1 || $chkp_id == 10 || $chkp_id == 159 || $chkp_id == 216){
							if(in_array($chkp_id, $siteTypeCpIdList)){
								$siteType = $answer;
							}
						}	
						
						$dependent=$v['Dependent'];
						if($dependent == ""){
							$dependent = 0;
						}

						$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`) VALUES (?,?,?,?)";
						$stmt = $conn->prepare($insertInTransDtl);
						$stmt->bind_param("iisi", $activityId, $chkp_id, $answer, $dependent);
						try {
							$stmt->execute();
						} catch (Exception $e) {
							
						}
						
						// $insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
						// mysqli_query($conn,$insertInTransDtl);
						
					}

					if($mId == 1){
						$insertLocationSql = "INSERT INTO `Location`(`State`, `Name`, `Site_Type`, `Site_CAT`, `GeoCoordinates`, `Tenent_Id`) ";
						if($shopName !=""){
							$insertLocationSql.= " VALUES ('$state', '$shopName', '$siteType', '$shopType', '$geolocation', $tenentId) ";
						}
						if($hotelName !=""){
							$insertLocationSql.= " VALUES ('$state', '$hotelName', '$siteType', '$shopType', '$geolocation', $tenentId) ";
						}
						if($officeName !=""){
							$insertLocationSql.= " VALUES ('$state', '$officeName', '$siteType', '$shopType', '$geolocation', $tenentId) ";
						}
						if($l13Name !=""){
							$insertLocationSql.= " VALUES ('$state', '$l13Name', '$siteType', '$shopType', '$geolocation', $tenentId) ";
						}
						
						if(mysqli_query($conn,$insertLocationSql)){
							$insetLocationId = $conn->insert_id;

							$insertEmpLocSql = "INSERT INTO `EmployeeLocationMapping`(`LocationId`, `Emp_Id`, `Tenent_Id`) VALUES ($insetLocationId, '$empId', $tenentId)";
							mysqli_query($conn,$insertEmpLocSql);

						}
					}
						

					if($siteName !=""){
						$updateHdr = "update TransactionHDR set Site_Name = '$siteType - $siteName' where SRNo = $lastTransHdrId ";
						mysqli_query($conn,$updateHdr);
					}
				}
				
		}
		// Update `ActivityId` in `Mapping` table after first of TODO task user.
		if($assignId != ""){
			$type .= "7,";
			$updateAssignTaskSql = "Update Mapping set ActivityId = '$activityId' where MappingId = $assignId";
			mysqli_query($conn,$updateAssignTaskSql);

		}
	}
	// For second or more TODO task user.
	else{
		$type .= "2,";
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

			// not in use below if block.
			if($actId == null  && $actId == ''){
				$type .= "3,";
				$mappingId = $conn->insert_id;
				$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`,`Lat_Long`, `FakeGPS_App`) VALUES 
				('$activityId','Created','$geolocation','$fakeGpsMessage')";
				
				if(mysqli_query($conn,$insertInTransHdr)){
					$lastTransHdrId = $conn->insert_id;
					foreach($checklist as $k=>$v)
					{
						$answer=$v['value'];
						$chkp_idArray=explode("_",$v['Chkp_Id']);

						if(count($chkp_idArray) > 1){
							$chkp_id = $chkp_idArray[1];
						}
						else{
							$chkp_id = $chkp_idArray[0];
						}	
						
						$dependent=$v['Dependent'];
						if($dependent == ""){
							$dependent = 0;
						}
						
						$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
						mysqli_query($conn,$insertInTransDtl);
						
					}
				}
			}
			// for insert details of second or more TODO task checkpoint in `TransactionDTL` in table.
			else{
				$type .= "4,";
				$isAllSave = false;
				foreach($checklist as $k=>$v)
				{
					$answer=$v['value'];
					$chkp_idArray=explode("_",$v['Chkp_Id']);

					if(count($chkp_idArray) > 1){
						$chkp_id = $chkp_idArray[1];
					}
					else{
						$chkp_id = $chkp_idArray[0];
					}	
					
					$dependent=$v['Dependent'];
					if($dependent == ""){
						$dependent = 0;
					}
					
					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) 
					VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
					if(mysqli_query($conn,$insertInTransDtl)){
						$isAllSave = true;
					}
					
				}

				if($isAllSave){
					$lastTransHdrId = $activityId;

				}

			}
				
		}
		// not in use below if block.
		if($assignId != ""){
			$type .= "5,";
			$updateAssignTaskSql = "Update Mapping set ActivityId = '$activityId' where MappingId = $assignId";
			mysqli_query($conn,$updateAssignTaskSql);

		}
		// for update second or more user `ActivityId` in `TransactionHDR` table
		if($actId != null && $actId != ''){
			$type .= "6";
			$selectTransHdrSql = "Select * from TransactionHDR  where ActivityId = $actId";
			$selectTransHdrResult = mysqli_query($conn,$selectTransHdrSql);
			$thRow=mysqli_fetch_array($selectTransHdrResult);
			if($thRow['Status'] == 'Created'){
				$updateTransHdrSql = "Update TransactionHDR set Status = 'Verified', VerifierActivityId = '$activityId' where ActivityId = $actId";
				
			}
			else if($thRow['Status'] == 'Verified'){
				$updateTransHdrSql = "Update TransactionHDR set Status = 'Approved', ApproverActivityId = '$activityId' where ActivityId = $actId";
			}
			
			mysqli_query($conn,$updateTransHdrSql);
		}
	}


	
	$output = new StdClass;
	if($lastTransHdrId != ""){
		$output -> error = "200";
		$output -> message = "success - $type";
		$output -> TransID = "$activityId";
	}
	else{
		$output -> error = "0";
		$output -> message = "something wrong";
		$output -> TransID = "$activityId";
	}
	echo json_encode($output);
	file_put_contents('/var/www/trinityapplab.co.in/NVGroup/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
	
}



?>

<?php
// function CallAPI($method, $url, $data)
// {
//     $curl = curl_init();

//     switch ($method)
//     {
//         case "POST":
//             curl_setopt($curl, CURLOPT_POST, 1);

//             if ($data)
//                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//             break;
//         default:
//             if ($data)
//                 $url = sprintf("%s?%s", $url, http_build_query($data));
//     }

//     curl_setopt($curl, CURLOPT_URL, $url);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

//     $result = curl_exec($curl);
// 	//echo $result."\n";
//     curl_close($curl);

//     return $result;
// }
?>