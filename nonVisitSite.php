<?php 
include("dbConfiguration.php");
$sql="SELECT `LocationId`, concat(`Site_Type`, ' - ', `Name`) as `SiteName` FROM `Location` where `LocationId` !=1 and `Is_Active`=1 order by `LocationId`";
$query=mysqli_query($conn,$sql);
// $siteVisitList = array();
$successCount=0;
$failCount=0;
while($row = mysqli_fetch_assoc($query)){
	$locationId = $row["LocationId"];
	$siteName = $row["SiteName"];

	$trSql = "SELECT *  FROM `TransactionHDR` WHERE `Site_Name`='$siteName'";
	$trQuery=mysqli_query($conn,$trSql);
	$rowCount=mysqli_num_rows($trQuery);
	// $visitJson = array(
	// 	'locationId' => $locationId,
	// 	'siteName' => $siteName,
	// 	'rowCount' => $rowCount 
	// );
	// array_push($siteVisitList, $visitJson);
	// if($rowCount == 0){
		$nonVisitSql = "INSERT INTO `NonVisitSite`(`LocationId`, `SiteName`, `VisitCount`) VALUES ($locationId,'$siteName', $rowCount)";
		if(mysqli_query($conn,$nonVisitSql)){
			$successCount++;
		}
		else{
			$failCount++;
		}
	// }
}
// echo json_encode($siteVisitList);
$output = array('successCount' => $successCount, 'failCount' => $failCount);
echo json_encode($output);
?>