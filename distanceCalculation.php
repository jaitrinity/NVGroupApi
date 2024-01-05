<?php
include("dbConfiguration.php");
$api_key = "AIzaSyDkCjzv4fVu7wlsp31Tu0AnpbyQaxm4Kz8";

$yesterdayDate = date('Y-m-d', strtotime('-1 day'));
// $yesterdayDate = '2023-05-03';

$delSql = "DELETE FROM `Distance` where `Visit_Date` = '$yesterdayDate'";
mysqli_query($conn,$delSql);
$delSql = "DELETE FROM `TA_DA_Report` where `Date` = '$yesterdayDate'";
mysqli_query($conn,$delSql);
$successArr = array();
$errorArr = array();

// $sql = "SELECT `EmpId` FROM `Employees` WHERE Active = 1 and Tenent_Id = 1 and `EmpId` = '9906060126' ";
$sql = "SELECT `EmpId` FROM `Employees` WHERE Active = 1 and Tenent_Id = 1 ";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$empId = $row["EmpId"];

	$planSql = "SELECT m.ActivityId, m.EmpId, m.MenuId, a.GeoLocation, a.Event, a.MobileDateTime, 'Plan' as RowType FROM Mapping m join Activity a on m.ActivityId = a.ActivityId and date_format(a.MobileDateTime,'%Y-%m-%d') = '$yesterdayDate' where m.EmpId = '$empId' and m.MenuId in (SELECT `MenuId` from `Menu` where `MenuType` = 1) and m.LocationId != 1 and m.ActivityId != 0";
	
	$nonPlanSql = "SELECT ActivityId, EmpId, MenuId, GeoLocation, Event, MobileDateTime, 'Unplan' as RowType FROM Activity where EmpId = '$empId' and MenuId in (SELECT `MenuId` from `Menu` where `MenuType` = 2) and Event = 'Submit' and date_format(MobileDateTime,'%Y-%m-%d') = '$yesterdayDate'";
	
	$startSql = "SELECT `ActivityId`, `EmpId`, `MenuId`, `GeoLocation`, `Event`, `MobileDateTime`, 'Attendance' as `RowType` FROM `Activity` where `EmpId` = '$empId' and `Event` = 'Start' and date_format(`MobileDateTime`,'%Y-%m-%d') = '$yesterdayDate'";
	
	$endSql = "SELECT `ActivityId`, `EmpId`, `MenuId`, `GeoLocation`, `Event`, `MobileDateTime`, 'Attendance' as `RowType` FROM `Activity` where `EmpId` = '$empId' and `Event` = 'Stop' and date_format(`MobileDateTime`,'%Y-%m-%d') = '$yesterdayDate'";

	$sql = $startSql." UNION ALL ".$planSql." UNION ALL ".$nonPlanSql." UNION ALL ".$endSql;

	// echo $sql.' --- ';

	$rsVisit = mysqli_query($conn,"select * from ($sql) t order by t.`MobileDateTime`");

	
	$cnt=0;
	$origin="";
	$distinations="";
	while($rowV=mysqli_fetch_assoc($rsVisit))
	{
		$rowType=$rowV['RowType'];
		$mobileDateTime=$rowV['MobileDateTime'];
		$actId=$rowV['ActivityId'];
		$event=$rowV['Event'];
		$geoLocation = str_replace("/", ",", $rowV['GeoLocation']);
		$latitude= explode(",", $geoLocation)[0] ;
		$longitude= explode(",", $geoLocation)[1];
		$cnt=$cnt+1;
		if($cnt==1)
		{
			$origin=$latitude.",".$longitude;
			$origin_lat=$latitude;
			$origin_long=$longitude;
			$dest_lat=$latitude;
			$dest_long=$longitude;
			$distance = 0;
			if($origin_lat != $dest_lat){
				$distinations=$latitude."%2C".$longitude;
				$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
				$json_data=file_get_contents($url);	
				$distance=fnlGetDistance($json_data);
			}
			// echo $json_data.'1--------';
			// echo $distance.', ';
			$distanceSql = "insert into Distance (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`, `RowType`) values ('$actId', '$empId', '$yesterdayDate', '$mobileDateTime', '$origin_lat', '$origin_long', '$dest_lat', '$dest_long', '$distance', '$event', '$rowType')";
			if(mysqli_query($conn,$distanceSql)){
				array_push($successArr, $empId);
			}
			else{
				array_push($errorArr, $empId);
			}
		}
		else
		{
			if($latitude!="0")
			{
				$dest_lat=$latitude;
				$dest_long=$longitude;
				$distance = 0;
				if($origin_lat != $dest_lat){
					$distinations=$latitude."%2C".$longitude;
					$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
					$json_data=file_get_contents($url);	
					$distance=fnlGetDistance($json_data);
				}
				// echo $json_data.'2--------';
				// echo $distance.', ';
				$distanceSql = "insert into Distance (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`, `RowType`) values ('$actId', '$empId', '$yesterdayDate', '$mobileDateTime', '$origin_lat', '$origin_long', '$dest_lat', '$dest_long', '$distance', '$event', '$rowType')";
				if(mysqli_query($conn,$distanceSql)){
					array_push($successArr, $empId);
				}
				else{
					array_push($errorArr, $empId);
				}
				$origin=$latitude.",".$longitude;
				$origin_lat=$latitude;
				$origin_long=$longitude;
				
			}
		}
	}
}

$output = new StdClass;
$output -> distanceResponse = array('date' => $yesterdayDate, 'successArr' => $successArr, 'errorArr' => $errorArr);

$taDaResponse = CallAPI("GET","http://www.trinityapplab.co.in/NVGroup/taDaReport.php?yesterdayDate=$yesterdayDate","");
$output -> taDaResponse = json_decode($taDaResponse);
echo json_encode($output);

file_put_contents('/var/www/trinityapplab.co.in/NVGroup/log/distanceCalculatelog_'.date("Y").'.log', json_encode($output)."\n", FILE_APPEND);

?>
<?php
function fnlGetDistance($json_data)
{
	$json_a=json_decode($json_data,true);
	$total_distance=0;
	foreach($json_a as $key => $value) 
	{
		if($key=="rows")
		{
			foreach($value as $key1 => $value1) 
			{
				foreach($value1 as $key2 => $value2) 
				{
					foreach($value2 as $key3 => $value3) 
					{
						foreach($value3 as $key4 => $value4) 
						{
							if($key4=="distance")
							{
								foreach($value4 as $key5 => $value5) 
								{
									if($key5=="text")
									{
										// $total_distance=$total_distance + str_replace(" km","",$value5);
										$dist = $value5;
										// echo $dist;
										if(strpos($dist, 'km') !== false){
											// echo $dist;
											$dist1 = str_replace(" km","",$dist);
											// echo $dist1.'--';
											$dist = $dist1*1000;
										}
										else{
											$dist1 = str_replace(" m","",$dist);
											// echo $dist1.'--';
											$dist = $dist1;
										}
										$total_distance = ($total_distance + $dist)/1000;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return $total_distance;
}
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
