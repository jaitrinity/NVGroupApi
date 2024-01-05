<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
mysqli_set_charset($conn,'utf8');

include("dbConfiguration.php");
require 'EmployeeTenentId.php';

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/NVGroup/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
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


 	$classObj = new EmployeeTenentId();
	$empInfo = $classObj->getEmployeeInfo($conn,$empId);
	$tenentId = $empInfo["tenentId"];
	$state = $empInfo["state"];

	if($actId == null  && $actId == ''){
		
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
						$shopType = "";
						foreach($checklist as $k=>$v)
						{
							$answer=$v['value'];
							$chkp_idArray=explode("_",$v['Chkp_Id']);

							if(count($chkp_idArray) > 1){
								// for dependent
								$chkp_id = $chkp_idArray[1];
								if($chkp_id == 2 || $chkp_id == 11 || $chkp_id == 160){
									$shopName = $answer;
									$siteName = $answer;
								}
								if($chkp_id == 7 || $chkp_id == 12 || $chkp_id == 161){
									$hotelName = $answer;
									$siteName = $answer;
								}
								if($chkp_id == 8 || $chkp_id == 13 || $chkp_id == 162){
									$officeName = $answer;
									$siteName = $answer;
								}
								if($chkp_id == 177){
									$shopType = $answer;
								}
							}
							else{
								$chkp_id = $chkp_idArray[0];
								if($chkp_id == 1 || $chkp_id == 10 || $chkp_id == 159){
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
			//Change in Mapping table from now onwards
			if($assignId != ""){
				$updateAssignTaskSql = "Update Mapping set ActivityId = '$activityId' where MappingId = $assignId";
				mysqli_query($conn,$updateAssignTaskSql);

			}
			
		
	}
	else{
		$sql = "SELECT r1.Verifier_Role, r1.Approver_Role FROM `Menu` r1 where r1.MenuId = '$mId' ";
		$result = mysqli_query($conn,$sql);
		$row = mysqli_fetch_assoc($result);
		$verifier_Role = $row["Verifier_Role"];
		$approver_Role = $row["Approver_Role"];

		$activitySql = "Insert into Activity(DId,MappingId,EmpId,MenuId,LocationId,Event,GeoLocation,Distance,MobileDateTime,MobileTimestamp,Tenent_Id)"
						." values ('$dId','$mapId','$empId','$mId','$lId','$event','$geolocation','$distance','$date1','$transactionId',$tenentId)";
		//echo $activitySql;
		if(mysqli_query($conn,$activitySql)){
			$activityId = mysqli_insert_id($conn);
		}
		
		$isFinallySubmit = "";
		if($checklist != null && count($checklist) != 0){
			$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Start`, `End`, `ActivityId`, `Tenent_Id`, `CreateDateTime`) 
			values ('$empId', '$mId', '$lId', curdate(), curdate(), '$activityId', '$tenentId', current_timestamp) ";
			mysqli_query($conn,$insertMapping);

			if($actId == null  && $actId == ''){
				$mappingId = $conn->insert_id;
				$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`,`Lat_Long`, `FakeGPS_App`) VALUES 
				('$activityId','Created','$geolocation','$fakeGpsMessage')";
				
				if(mysqli_query($conn,$insertInTransHdr)){
					$lastTransHdrId = $conn->insert_id;
					$acEmpId = "";
					$siteName = "";
					foreach($checklist as $k=>$v)
					{
						$answer=$v['value'];
						$chkp_idArray=explode("_",$v['Chkp_Id']);

						if(count($chkp_idArray) > 1){
							$chkp_id = $chkp_idArray[1];
							if($chkp_id == 4726 || $chkp_id == 4717 || $chkp_id == 4929){
								$siteName = $answer;
							}
						}
						else{
							$chkp_id = $chkp_idArray[0];
							if($chkp_id == 4726 || $chkp_id == 4717 || $chkp_id == 4929){
								$siteName = $answer;
							}
							if($chkp_id == 5196){
								$acEmpId = $answer;
							}
						}	
						
						$dependent=$v['Dependent'];
						if($dependent == ""){
							$dependent = 0;
						}
						
						$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
						mysqli_query($conn,$insertInTransDtl);
						
					}

					if($siteName != ''){
						$explodeSiteName = explode(" --- ", $siteName);
						$siteId = $explodeSiteName[1];
						$siteNamee = $explodeSiteName[0];
						$updateHdr = "update TransactionHDR set Site_Id = '$siteId', Site_Name = '$siteNamee' where SRNo = $lastTransHdrId ";
						mysqli_query($conn,$updateHdr);

						$verifierMobile = "";
						if($verifier_Role != null && $verifier_Role !=''){
							$sql2 = "SELECT el.Emp_Id FROM Location l join EmployeeLocationMapping el on l.LocationId = el.LocationId where l.Name = '$siteNamee' 
							and l.Site_Id = '$siteId' and el.Role = '$verifier_Role' and l.Tenent_Id = $tenentId ";
							$result2 = mysqli_query($conn,$sql2);
							while ($row2 = mysqli_fetch_assoc($result2)) {
								$verifierMobile .= $row2["Emp_Id"].',';
							}
							$verifierMobile = substr($verifierMobile, 0, strlen($verifierMobile)-1);
						}

						$approverMobile = "";
						if($approver_Role != null && $approver_Role !=''){
							$sql3 = "SELECT el.Emp_Id FROM Location l join EmployeeLocationMapping el on l.LocationId = el.LocationId where l.Name = '$siteNamee' 
							and l.Site_Id = '$siteId' and el.Role = '$approver_Role' and l.Tenent_Id = $tenentId";
							$result3 = mysqli_query($conn,$sql3);
							while ($row3 = mysqli_fetch_assoc($result3)) {
								$approverMobile .= $row3["Emp_Id"].',';
							}
							$approverMobile = substr($approverMobile, 0, strlen($approverMobile)-1);
						}

						$updateMapping = "update Mapping set Verifier = '$verifierMobile', Approver = '$approverMobile' where MappingId = $mappingId and 
						Tenent_Id = $tenentId ";
						mysqli_query($conn,$updateMapping);

					}

					if($acEmpId != ""){
						$explodeAcEmpId = explode(" --- ", $acEmpId);
						$acEmpId = $explodeAcEmpId[0];
						$acEmpName = $explodeAcEmpId[1];

						$updateMapping = "update Mapping set Verifier = '$acEmpId' where MappingId = $mappingId and Tenent_Id = $tenentId ";
						mysqli_query($conn,$updateMapping);
					}
					
				}
			}
			else{
				$isAllSave = false;
				$assignTech = "";
				$actName = "";
				foreach($checklist as $k=>$v)
				{
					$answer=$v['value'];
					$chkp_idArray=explode("_",$v['Chkp_Id']);

					if(count($chkp_idArray) > 1){
						$chkp_id = $chkp_idArray[1];
					}
					else{
						$chkp_id = $chkp_idArray[0];
						if($chkp_id == 5197){
							$isFinallySubmit = $answer;
						}
						if($chkp_id == 5255){
							$assignTech = $answer;
						}
						if($chkp_id == 5256){
							$actName = $answer;
						}
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

				if($actName == "Approve"){
					$explodeAssignTech = explode(" --- ", $assignTech);
					$assignTechId = $explodeAssignTech[0];
					$assignTechName = $explodeAssignTech[1];

					$updateMapping = "update Mapping set Approver = '$assignTechId' where ActivityId = $actId and Tenent_Id = $tenentId ";
					mysqli_query($conn,$updateMapping);
				}
			}
				
		}
		//Change in Mapping table from now onwards
		if($assignId != ""){
			$updateAssignTaskSql = "Update Mapping set ActivityId = '$activityId' where MappingId = $assignId";
			mysqli_query($conn,$updateAssignTaskSql);

		}
		if($mId == 279){
			if($actId != null && $actId != ''){
				$selectTransHdrSql = "Select * from TransactionHDR  where ActivityId = $actId";
				$selectTransHdrResult = mysqli_query($conn,$selectTransHdrSql);
				$thRow=mysqli_fetch_array($selectTransHdrResult);
				if($thRow['Status'] == 'Created'){
					if($isFinallySubmit != 'No'){
						$updateTransHdrSql = "Update TransactionHDR set Status = 'Verified', VerifierActivityId = '$activityId', Verify_Final_Submit = '$isFinallySubmit' where ActivityId = $actId";
					}
					else{
						$updateTransHdrSql = "Update TransactionHDR set VerifierActivityId = '$activityId', Verify_Final_Submit = '$isFinallySubmit' 
						where ActivityId = $actId";
					}
				}
				else if($thRow['Status'] == 'Verified'){
					if($isFinallySubmit != 'No'){
						$updateTransHdrSql = "Update TransactionHDR set Status = 'Approved',ApproverActivityId = '$activityId' where ActivityId = $actId";
					}
				}
				
				mysqli_query($conn,$updateTransHdrSql);
			}

		}
		else if($actId != null && $actId != ''){
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


	
	$output = "";
	if($lastTransHdrId != ""){
		$output -> error = "200";
		$output -> message = "success";
		$output -> TransID = "$activityId";
	}
	else{
		$output -> error = "0";
		$output -> message = "something wrong";
		$output -> TransID = "$activityId";
	}
	echo json_encode($output);
	file_put_contents('/var/www/trinityapplab.in/html/NVGroup/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
	
}



?>

<?php
function CallAPI($method, $url, $data)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
	//echo $result."\n";
    curl_close($curl);

    return $result;
}
?>