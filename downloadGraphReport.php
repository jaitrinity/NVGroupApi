<?php 
include("dbConfiguration.php");
function getSafeRequestValue($key){
	$val = $_REQUEST[$key];
	return isset($val)? $val:"";
}
$jsonData = getSafeRequestValue('jsonData');
$jsonData=json_decode($jsonData);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$period = $jsonData->period;
$incidentCategory = $jsonData->incidentCategory;
$quarter = $jsonData->quarter;
$siteType = $jsonData->siteType;
$metroSiteType = $jsonData->metroSiteType;
$graphType = $jsonData->graphType;
$millisecond = $jsonData->millisecond;
$currentTime = time();
// if($currentTime >= $millisecond){
// 	sessionExpired();
// }
// else 
if($graphType == 1){
	$filterSql = "";
	if($incidentCategory != ""){
		$filterSql .= " and `Incident category` = '$incidentCategory' ";
	}
	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site Id`, `Site Name`, `Incident category`, `Material Damaged`, `Incident Date`, `Incident Time`, `Incident description`, `Location (Lat Long)`, `Entered By`, `Approved status By L1`, `Approved status By L2` FROM (select @sr:=0) as sr, `Incident_Management_Report` where `Period` = '$period' ".$filterSql;

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Incident_Management_Graph.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 2){
	$lastThreeMonth = array();
	for ($i = -3; $i < 0; $i++){
	  $m = date('M-Y', strtotime("$i month"));
	  array_push($lastThreeMonth, $m);
	}
	$imPeriod = implode("','", $lastThreeMonth);
	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site Id`, `Site Name`, `Incident category`, `Material Damaged`, `Incident Date`, `Incident Time`, `Incident description`, `Location (Lat Long)`, `Entered By`, `Approved status By L1`, `Approved status By L2` FROM (select @sr:=0) as sr, `Incident_Management_Report` where `Period` in ('".$imPeriod."') and `Incident category` = '$incidentCategory' ";

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Repeated_Incident_in_last_three_month.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 3){
	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site Id`, `Site Name`, `Incident category`, `Incident Date`, `Incident Time`, `Incident description`, `Location (Lat Long)`, `Entered By`, `Approved status By L1`, `L1 Close Date` as `Incident Close Date`, `L1 Close Time` as `Incident Close Time`, `L1 Close Remark` as `Incident Close Description`, `incident_minute` as `Total Incident (in Min)` FROM (select @sr:=0) as sr, `Incident_MTTR` where Incident_Month = '$period' and `Incident category` = 'Fiber Cut' ";

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=MTTR_of_Fiber_Cut.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 4){
	$monthNumber = date('m');
	$year = date('Y');
	$quaterList = array();
	if($quarter == "Q1"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Apr-'.$year);
		array_push($quaterList, 'May-'.$year);
		array_push($quaterList, 'Jun-'.$year);
	}
	else if($quarter == "Q2"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Jul-'.$year);
		array_push($quaterList, 'Aug-'.$year);
		array_push($quaterList, 'Sep-'.$year);
	}
	else if($quarter == "Q3"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Oct-'.$year);
		array_push($quaterList, 'Nov-'.$year);
		array_push($quaterList, 'Dec-'.$year);
	}
	else if($quarter == "Q4"){
		if($monthNumber > 3){
			$year = $year + 1;
		}
		array_push($quaterList, 'Jan-'.$year);
		array_push($quaterList, 'Feb-'.$year);
		array_push($quaterList, 'Mar-'.$year);
	}

	$quarter = implode("','", $quaterList);

	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site_Id`, `Site_Name`, `Site Type`, `PM Done Date`, `Airtel Site Id`, `Airtel Load`, `MTNL/BSNL Site Id`, `MTNL/BSNL Load`, `VIL Site Id`, `VIL Load`, `RJIO Site Id`, `RJIO Load`, `No. of FE`, `Serial No. OF FE 1`, `Refilling date of FE 1`, `Expiry date of FE 1`, `Serial No. OF FE 2`, `Refilling date of FE 2`, `Expiry date of FE 2`, `Serial No. OF FE 3`, `Refilling date of FE 3`, `Expiry date of FE 3`, `Serial No. OF FE 4`, `Refilling date of FE 4`, `Expiry date of FE 4`, `Serial No. OF FE 5`, `Refilling date of FE 5`, `Expiry date of FE 5`, `Pole Type`, `No. of Pole`, `Pole Height`, `Airtel RRH`, `Airtel MW`, `Airtel GSM`, `MTNL/BSNL RRH`, `MTNL/BSNL MW`, `MTNL/BSNL GSM`, `VIL RRH`, `VIL MW`, `VIL GSM`, `RJIO RRH`, `RJIO MW`, `RJIO GSM`, `SMPS Make`, `No. of RM`, `No. of faulty`, `BB Make & Model`, `No. of BB`, `SOC &SOH status`, `Capacity in AH 1`, `Capacity in AH 2`, `Capacity in AH 3`, `PM done By`, `PM approved By:L1` FROM (select @sr:=0) as sr, `PM_Report` where PM_done_period in ('".$quarter."') ";

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Preventive_Maintenance.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 5){
	$monthNumber = date('m');
	$year = date('Y');
	$quaterList = array();
	if($quarter == "Q1"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Apr-'.$year);
		array_push($quaterList, 'May-'.$year);
		array_push($quaterList, 'Jun-'.$year);
	}
	else if($quarter == "Q2"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Jul-'.$year);
		array_push($quaterList, 'Aug-'.$year);
		array_push($quaterList, 'Sep-'.$year);
	}
	else if($quarter == "Q3"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Oct-'.$year);
		array_push($quaterList, 'Nov-'.$year);
		array_push($quaterList, 'Dec-'.$year);
	}
	else if($quarter == "Q4"){
		if($monthNumber > 3){
			$year = $year + 1;
		}
		array_push($quaterList, 'Jan-'.$year);
		array_push($quaterList, 'Feb-'.$year);
		array_push($quaterList, 'Mar-'.$year);
	}

	$quarter = implode("','", $quaterList);

	$filterSql = "";
	if($siteType != ""){
		$siteType = str_replace("plus","+", $siteType);
		$filterSql .= " and `Site_CAT` = '$siteType' ";
	}

	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site_Id`, `Site_Name`, `Site Type`, `PM Done Date`, `Airtel Site Id`, `Airtel Load`, `MTNL/BSNL Site Id`, `MTNL/BSNL Load`, `VIL Site Id`, `VIL Load`, `RJIO Site Id`, `RJIO Load`, `No. of FE`, `Serial No. OF FE 1`, `Refilling date of FE 1`, `Expiry date of FE 1`, `Serial No. OF FE 2`, `Refilling date of FE 2`, `Expiry date of FE 2`, `Serial No. OF FE 3`, `Refilling date of FE 3`, `Expiry date of FE 3`, `Serial No. OF FE 4`, `Refilling date of FE 4`, `Expiry date of FE 4`, `Serial No. OF FE 5`, `Refilling date of FE 5`, `Expiry date of FE 5`, `Pole Type`, `No. of Pole`, `Pole Height`, `Airtel RRH`, `Airtel MW`, `Airtel GSM`, `MTNL/BSNL RRH`, `MTNL/BSNL MW`, `MTNL/BSNL GSM`, `VIL RRH`, `VIL MW`, `VIL GSM`, `RJIO RRH`, `RJIO MW`, `RJIO GSM`, `SMPS Make`, `No. of RM`, `No. of faulty`, `BB Make & Model`, `No. of BB`, `SOC &SOH status`, `Capacity in AH 1`, `Capacity in AH 2`, `Capacity in AH 3`, `PM done By`, `PM approved By:L1` FROM (select @sr:=0) as sr, `PM_Report` where PM_done_period in ('".$quarter."') ".$filterSql;

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Site_Type_wise_PM_Status.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 6){
	$monthNumber = date('m');
	$year = date('Y');
	$quaterList = array();
	if($quarter == "Q1"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Apr-'.$year);
		array_push($quaterList, 'May-'.$year);
		array_push($quaterList, 'Jun-'.$year);
	}
	else if($quarter == "Q2"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Jul-'.$year);
		array_push($quaterList, 'Aug-'.$year);
		array_push($quaterList, 'Sep-'.$year);
	}
	else if($quarter == "Q3"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Oct-'.$year);
		array_push($quaterList, 'Nov-'.$year);
		array_push($quaterList, 'Dec-'.$year);
	}
	else if($quarter == "Q4"){
		if($monthNumber > 3){
			$year = $year + 1;
		}
		array_push($quaterList, 'Jan-'.$year);
		array_push($quaterList, 'Feb-'.$year);
		array_push($quaterList, 'Mar-'.$year);
	}

	$quarter = implode("','", $quaterList);

	$filterSql = "";

	if($metroSiteType == "High_R_Site"){
		$filterSql .= " and `High_Revenue_Site` = 1 ";
	}
	else if($metroSiteType == "ISQ"){
		$filterSql .= " and `ISQ` = 1 ";
	}
	else if($metroSiteType == "Retail_IBS"){
		$filterSql .= " and `Retail_IBS` = 1 ";
	}
	else{
		$filterSql .= " and `Airport_Metro` = '$metroSiteType' ";
	}

	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site_Id`, `Site_Name`, `Site Type`, `PM Done Date`, `Airtel Site Id`, `Airtel Load`, `MTNL/BSNL Site Id`, `MTNL/BSNL Load`, `VIL Site Id`, `VIL Load`, `RJIO Site Id`, `RJIO Load`, `No. of FE`, `Serial No. OF FE 1`, `Refilling date of FE 1`, `Expiry date of FE 1`, `Serial No. OF FE 2`, `Refilling date of FE 2`, `Expiry date of FE 2`, `Serial No. OF FE 3`, `Refilling date of FE 3`, `Expiry date of FE 3`, `Serial No. OF FE 4`, `Refilling date of FE 4`, `Expiry date of FE 4`, `Serial No. OF FE 5`, `Refilling date of FE 5`, `Expiry date of FE 5`, `Pole Type`, `No. of Pole`, `Pole Height`, `Airtel RRH`, `Airtel MW`, `Airtel GSM`, `MTNL/BSNL RRH`, `MTNL/BSNL MW`, `MTNL/BSNL GSM`, `VIL RRH`, `VIL MW`, `VIL GSM`, `RJIO RRH`, `RJIO MW`, `RJIO GSM`, `SMPS Make`, `No. of RM`, `No. of faulty`, `BB Make & Model`, `No. of BB`, `SOC &SOH status`, `Capacity in AH 1`, `Capacity in AH 2`, `Capacity in AH 3`, `PM done By`, `PM approved By:L1` FROM (select @sr:=0) as sr, `PM_Report` where PM_done_period in ('".$quarter."') ".$filterSql;

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Airport_Metro_PM_Status.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 7){
	$monthNumber = date('m');
	$year = date('Y');
	$quarter = $jsonData->quarter;
	$quaterList = array();
	if($quarter == "Q1"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, '4-'.$year);
		array_push($quaterList, '5-'.$year);
		array_push($quaterList, '6-'.$year);
	}
	else if($quarter == "Q2"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, '7-'.$year);
		array_push($quaterList, '8-'.$year);
		array_push($quaterList, '9-'.$year);
	}
	else if($quarter == "Q3"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, '10-'.$year);
		array_push($quaterList, '11-'.$year);
		array_push($quaterList, '12-'.$year);
	}
	else if($quarter == "Q4"){
		if($monthNumber > 3){
			$year = $year + 1;
		}
		array_push($quaterList, '1-'.$year);
		array_push($quaterList, '2-'.$year);
		array_push($quaterList, '3-'.$year);
	}

	for($i=0;$i<count($quaterList);$i++){
		$qua = $quaterList[$i];
		$quaExplode = explode("-", $qua);
		$month = $quaExplode[0];
		if($month<10)$month = '0'.$month;
		$year = $quaExplode[1];
		$days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
		$start = 1; $end = 7;
		$total = 0;
		while($days>$total){
		    $week[] = get_week_array($start,$end);
		    $total = $total+$end;
		    $start = $total+1;
		    $end = 7;
		}
	}

	$allDate = array();
	for($w=0;$w<count($week);$w++){
		$wwArr = $week[$w];
		for($ww=0;$ww<count($wwArr);$ww++){
			array_push($allDate, $wwArr[$ww]);
		}
	}

	$dd = implode("','", $allDate);

	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, `Circle`, `Site_Id`, `Site_Name`, `Site Type`, `PM Done Date`, `Airtel Site Id`, `Airtel Load`, `MTNL/BSNL Site Id`, `MTNL/BSNL Load`, `VIL Site Id`, `VIL Load`, `RJIO Site Id`, `RJIO Load`, `No. of FE`, `Serial No. OF FE 1`, `Refilling date of FE 1`, `Expiry date of FE 1`, `Serial No. OF FE 2`, `Refilling date of FE 2`, `Expiry date of FE 2`, `Serial No. OF FE 3`, `Refilling date of FE 3`, `Expiry date of FE 3`, `Serial No. OF FE 4`, `Refilling date of FE 4`, `Expiry date of FE 4`, `Serial No. OF FE 5`, `Refilling date of FE 5`, `Expiry date of FE 5`, `Pole Type`, `No. of Pole`, `Pole Height`, `Airtel RRH`, `Airtel MW`, `Airtel GSM`, `MTNL/BSNL RRH`, `MTNL/BSNL MW`, `MTNL/BSNL GSM`, `VIL RRH`, `VIL MW`, `VIL GSM`, `RJIO RRH`, `RJIO MW`, `RJIO GSM`, `SMPS Make`, `No. of RM`, `No. of faulty`, `BB Make & Model`, `No. of BB`, `SOC &SOH status`, `Capacity in AH 1`, `Capacity in AH 2`, `Capacity in AH 3`, `PM done By`, `PM approved By:L1` FROM (select @sr:=0) as sr, `PM_Report` where date_format(`PM Done Date`,'%Y-%m-%d') in ('".$dd."') ";

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=Weekly_PM_progress_graph.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}
else if($graphType == 8){
	$monthNumber = date('m');
	$year = date('Y');
	$quaterList = array();
	if($quarter == "Q1"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Apr-'.$year);
		array_push($quaterList, 'May-'.$year);
		array_push($quaterList, 'Jun-'.$year);
	}
	else if($quarter == "Q2"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Jul-'.$year);
		array_push($quaterList, 'Aug-'.$year);
		array_push($quaterList, 'Sep-'.$year);
	}
	else if($quarter == "Q3"){
		if($monthNumber < 4){
			$year = $year - 1;
		}
		array_push($quaterList, 'Oct-'.$year);
		array_push($quaterList, 'Nov-'.$year);
		array_push($quaterList, 'Dec-'.$year);
	}
	else if($quarter == "Q4"){
		if($monthNumber > 3){
			$year = $year + 1;
		}
		array_push($quaterList, 'Jan-'.$year);
		array_push($quaterList, 'Feb-'.$year);
		array_push($quaterList, 'Mar-'.$year);
	}

	$quarter = implode("','", $quaterList);

	$sql = "SELECT (@sr := @sr+1) as `Sr. No.`, p.`Circle`, p.`Site_Id`, p.`Site_Name`, p.`Site Type`, p.`PM Done Date`, p.`Airtel Site Id`, p.`Airtel Load`, p.`MTNL/BSNL Site Id`, p.`MTNL/BSNL Load`, p.`VIL Site Id`, p.`VIL Load`, p.`RJIO Site Id`, p.`RJIO Load`, p.`No. of FE`, p.`Serial No. OF FE 1`, p.`Refilling date of FE 1`, p.`Expiry date of FE 1`, p.`Serial No. OF FE 2`, p.`Refilling date of FE 2`, p.`Expiry date of FE 2`, p.`Serial No. OF FE 3`, p.`Refilling date of FE 3`, p.`Expiry date of FE 3`, p.`Serial No. OF FE 4`, p.`Refilling date of FE 4`, p.`Expiry date of FE 4`, p.`Serial No. OF FE 5`, p.`Refilling date of FE 5`, p.`Expiry date of FE 5`, p.`Pole Type`, p.`No. of Pole`, p.`Pole Height`, p.`Airtel RRH`, p.`Airtel MW`, p.`Airtel GSM`, p.`MTNL/BSNL RRH`, p.`MTNL/BSNL MW`, p.`MTNL/BSNL GSM`, p.`VIL RRH`, p.`VIL MW`, p.`VIL GSM`, p.`RJIO RRH`, p.`RJIO MW`, p.`RJIO GSM`, p.`SMPS Make`, p.`No. of RM`, p.`No. of faulty`, p.`BB Make & Model`, p.`No. of BB`, p.`SOC &SOH status`, p.`Capacity in AH 1`, p.`Capacity in AH 2`, p.`Capacity in AH 3`, p.`PM done By`, p.`PM approved By:L1` FROM (select @sr:=0) as sr, Punchpoint_Report pr join PM_Report p on pr.ActivityId = p.ActivityId where pr.Period in ('".$quarter."') GROUP by pr.ActivityId ";

	$result = mysqli_query($conn,$sql);
	$row=mysqli_fetch_assoc($result);
	$columnName = array();
	foreach ($row as $key => $value) {
		array_push($columnName, $key);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=PM_punchpoint.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columnName);

	mysqli_data_seek($result, 0);
	while($row=mysqli_fetch_assoc($result)){
		$exportData = array();
		foreach ($columnName as $key => $value) {
			array_push($exportData, $row[$value]);
		}
		fputcsv($output, $exportData);
	}
}

?>

<?php
header('Content-Type: text/html');
function sessionExpired(){
	echo "<h1>Session Expired.</h1>";
}
function get_week_array($start,$end){
    global $month, $year, $days;
    for($i=0;$i<$end;$i++){
        if($start<10)$array[] = $year.'-'.$month.'-0'.$start;
        else $array[] = $year.'-'.$month.'-'.$start;
        $start = $start+1;
        if($start==$days+1)break;
    }
    return $array;
}
?>