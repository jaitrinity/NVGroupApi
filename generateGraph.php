<?php 
include("dbConfiguration.php");
$requestJson = file_get_contents('php://input');
$jsonData=json_decode($requestJson);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$period = $jsonData->period;
$incidentCategory = $jsonData->incidentCategory;
$graphType = $jsonData->graphType;

if($graphType == 1){
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();
	$sql = "";
	if($incidentCategory == ""){
		$sql = "SELECT `Incident_category` as `Category`, COUNT(*) as `Count` FROM `Incident_Graph` where `Period` = '$period' GROUP by `Incident_category` ";
		array_push($tableColumn,"Incident Type");
		array_push($tableColumn,"Incident");
	}
	else{
		$sql = "SELECT `State` as `Category`, COUNT(*) as Count FROM `Incident_Graph` where `Period` = '$period' and `Incident_category` = '$incidentCategory' GROUP by `State`";
		array_push($tableColumn,"Circle");
		array_push($tableColumn,$incidentCategory);
	}

	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "Category"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "Count"){
				array_push($dataArr, $value);
			}
		}
		$json = array('category' => $row["Category"], 'count' => $row["Count"]);
		array_push($tableData, $json);

	}

	
	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $dataArr, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}
else if($graphType == 2){
	$lastThreeMonth = array();
	for ($i = -3; $i < 0; $i++){
	  $m = date('M-Y', strtotime("$i month"));
	  array_push($lastThreeMonth, $m);
	}
	
	$imPeriod = implode("','", $lastThreeMonth);
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Circle");
	array_push($tableColumn,$incidentCategory);

	$sql = "SELECT `State` as `Category`, COUNT(*) as Count FROM `Incident_Graph` where `Period` in ('".$imPeriod."') and `Incident_category` = '$incidentCategory' GROUP by `State`";
	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "Category"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "Count"){
				array_push($dataArr, $value);
			}
		}
		$json = array('category' => $row["Category"], 'count' => $row["Count"]);
		array_push($tableData, $json);

	}

	$dataObj = array('data' => $dataArr, 'label' => $incidentCategory);
	$chartData = array();
	array_push($chartData, $dataObj);

	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}
else if($graphType == 3){
	$labelArr = array();
	$dataArr = array();
	$dataArr1 = array();
	$colorArr = array();
	$colorArr1 = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Circle");
	array_push($tableColumn,"No of Incident");
	array_push($tableColumn,"MTTR(In Min)");

	$sql = "select t.Circle, t.Incident_count, round(t.Incident_minute/t.Incident_count,0) as MTTR from (SELECT Circle, count(*) as Incident_count, sum(incident_minute) as Incident_minute FROM `Incident_MTTR` where Incident_Month = '$period' and `Incident category` = 'Fiber Cut' GROUP by Circle) t";
	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "Circle"){
				array_push($labelArr, $value);
			}
			else if($key == "Incident_count"){
				array_push($dataArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "MTTR"){
				array_push($dataArr1, $value);
				$color1 = getColorHexCode();
				array_push($colorArr1, $color1);
			}
		}
		$json = array('circle' => $row["Circle"], 'count' => $row["Incident_count"], 'mttr' => $row["MTTR"]);
		array_push($tableData, $json);
	}
	$dataObj = array('data' => $dataArr, 'label' => 'No of Incident');
	$dataObj1 = array('data' => $dataArr1, 'label' => 'MTTR(In Min)');
	$chartData = array();
	array_push($chartData, $dataObj);
	array_push($chartData, $dataObj1);
	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'colorArr1' => $colorArr1, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}

else if($graphType == 4){
	$monthNumber = date('m');
	$year = date('Y');
	$quarter = $jsonData->quarter;
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
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Circle");
	array_push($tableColumn,"Total Sites");
	array_push($tableColumn,"PM done");
	array_push($tableColumn,"% Comp");

	$quarter = implode("','", $quaterList);

	$sql = "SELECT t3.State, t3.Site_count, t3.PM_done, round((t3.PM_done/t3.Site_count)*100,0) as 'Done_Percentage' from (select s.State, s.Site_count, (case when t2.PM_done is null then 0 else t2.PM_done end) as PM_done from (SELECT l.State, count(l.Site_Id) as Site_count FROM Location l where l.Site_Id is not null and l.Site_Id != '' and l.Tenent_Id = 2 GROUP BY l.State) s left join (select t1.State, sum(t1.Single_count) as PM_done from (select t.State, t.Site_Id, t.State_site_id, count(t.State_site_id) as Done_count, (case when count(t.State_site_id) > 1 then 1 else count(t.State_site_id) end) as Single_count from (SELECT State, Site_Id, concat(State,' - ',Site_Id) as State_site_id FROM PM_Graph 
	where PM_done_period in ('".$quarter."') ) t GROUP by t.State_site_id) t1 GROUP by t1.State) t2 on s.State = t2.State ) t3 ";

	$gtSql = "SELECT gt1.State, gt1.Site_count, gt1.PM_done, round((gt1.PM_done/gt1.Site_count)*100,0) as 'Done_Percentage' from (select 'Total ' as State, sum(gt.Site_count) as Site_count, sum(gt.PM_done) as PM_done from (SELECT t3.State, t3.Site_count, t3.PM_done, round((t3.PM_done/t3.Site_count)*100,0) as 'Done_Percentage' from (select s.State, s.Site_count, (case when t2.PM_done is null then 0 else t2.PM_done end) as PM_done from (SELECT l.State, count(l.Site_Id) as Site_count FROM Location l where l.Site_Id is not null and l.Site_Id != '' and l.Tenent_Id = 2 GROUP BY l.State) s left join (select t1.State, sum(t1.Single_count) as PM_done from (select t.State, t.Site_Id, t.State_site_id, count(t.State_site_id) as Done_count, (case when count(t.State_site_id) > 1 then 1 else count(t.State_site_id) end) as Single_count from (SELECT State, Site_Id, concat(State,' - ',Site_Id) as State_site_id FROM PM_Graph 
where PM_done_period in ('".$quarter."') ) t GROUP by t.State_site_id) t1 GROUP by t1.State) t2 on s.State = t2.State ) t3) gt) gt1 ";

	$sql .= ' UNION '.$gtSql;
	// echo $sql;

	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "State"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "Done_Percentage"){
				array_push($dataArr, $value);
			}
		}
		$json = array('circle' => $row["State"], 'siteCount' => $row["Site_count"], 'pmDone' => $row["PM_done"], 'donePercentage' => $row["Done_Percentage"]);
		array_push($tableData, $json);
	}

	$dataObj = array('data' => $dataArr, 'label' => '% Comp');
	$chartData = array();
	array_push($chartData, $dataObj);

	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}

else if($graphType == 5){
	$monthNumber = date('m');
	$year = date('Y');
	$quarter = $jsonData->quarter;
	$siteType = $jsonData->siteType;
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
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Circle");
	array_push($tableColumn,"Total Sites");
	array_push($tableColumn,"PM done");
	array_push($tableColumn,"% Comp");

	$filterSql = "";
	$siteTypeSql = "";
	$gropupBySql = "";
	if($siteType != ""){
		$filterSql .= " and Site_Type = '$siteType' ";
		$siteTypeSql .= " and l.Site_Type = '$siteType' ";
		$gropupBySql .= ", l.Site_Type";
	}

	$quarter = implode("','", $quaterList);

	$sql = "SELECT t3.State, t3.Site_count, t3.PM_done, round((t3.PM_done/t3.Site_count)*100,0) as 'Done_Percentage' from (select s.State, s.Site_count, (case when t2.PM_done is null then 0 else t2.PM_done end) as PM_done from (SELECT l.State, count(l.Site_Id) as Site_count FROM Location l where l.Site_Id is not null and l.Site_Id != '' ".$siteTypeSql." and l.Tenent_Id = 2 GROUP BY l.State".$gropupBySql.") s left join (select t1.State, sum(t1.Single_count) as PM_done from (select t.State, t.Site_Id, t.State_site_id, count(t.State_site_id) as Done_count, (case when count(t.State_site_id) > 1 then 1 else count(t.State_site_id) end) as Single_count from (SELECT State, Site_Id, concat(State,' - ',Site_Id) as State_site_id FROM PM_Graph 
	where PM_done_period in ('".$quarter."') ".$filterSql.") t GROUP by t.State_site_id) t1 GROUP by t1.State) t2 on s.State = t2.State ) t3 ";

	$gtSql = "SELECT gt1.State, gt1.Site_count, gt1.PM_done, round((gt1.PM_done/gt1.Site_count)*100,0) as 'Done_Percentage' from (select 'Total ' as State, sum(gt.Site_count) as Site_count, sum(gt.PM_done) as PM_done from (SELECT t3.State, t3.Site_count, t3.PM_done, round((t3.PM_done/t3.Site_count)*100,0) as 'Done_Percentage' from (select s.State, s.Site_count, (case when t2.PM_done is null then 0 else t2.PM_done end) as PM_done from (SELECT l.State, count(l.Site_Id) as Site_count FROM Location l where l.Site_Id is not null and l.Site_Id != '' ".$siteTypeSql." and l.Tenent_Id = 2 GROUP BY l.State".$gropupBySql." ) s left join (select t1.State, sum(t1.Single_count) as PM_done from (select t.State, t.Site_Id, t.State_site_id, count(t.State_site_id) as Done_count, (case when count(t.State_site_id) > 1 then 1 else count(t.State_site_id) end) as Single_count from (SELECT State, Site_Id, concat(State,' - ',Site_Id) as State_site_id FROM PM_Graph 
		where PM_done_period in ('".$quarter."') ".$filterSql.") t GROUP by t.State_site_id) t1 GROUP by t1.State) t2 on s.State = t2.State ) t3) gt) gt1 ";

	$sql .= ' UNION '.$gtSql;

	// echo $sql;

	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "State"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "Done_Percentage"){
				array_push($dataArr, $value);
			}
		}
		$json = array('circle' => $row["State"], 'siteCount' => $row["Site_count"], 'pmDone' => $row["PM_done"], 'donePercentage' => $row["Done_Percentage"]);
		array_push($tableData, $json);
	}

	$dataObj = array('data' => $dataArr, 'label' => '% Comp');
	$chartData = array();
	array_push($chartData, $dataObj);

	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}

else if($graphType == 6){
	// In progress
}
else if($graphType == 7){
	$monthNumber = date('m');
	$year = date('Y');
	$quarter = $jsonData->quarter;
	$siteType = $jsonData->siteType;
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
	$dateStr = "";
	for($aa=0;$aa<count($allDate);$aa++){
		$ii = $aa + 1;
		$dateStr .= $allDate[$aa];
		// if($ii / 7 != 1) $dateStr .= ",";
		if($ii % 7 == 0) $dateStr .= ":";
		else $dateStr .= ",";
	}
	// echo "<pre>";print_r($allDate);
	$dateStr = rtrim($dateStr,",");
	
	$sqlArr = array();
	$weekArr = explode(":", $dateStr);
	for($j=0;$j<count($weekArr);$j++){
		$ww = "week ".($j+1);
		$wwArr = explode(",", $weekArr[$j]);
		$weekDate = implode(",", $wwArr);
		$dd = implode("','", $wwArr);
		$tempSql = "SELECT '$ww' as `Week`, '".$weekDate."' as `WeekDate`, count(*) as `PM_done` FROM `PM_Graph` where date_format(`MobileDateTime`,'%Y-%m-%d') in ('".$dd."') ";
		array_push($sqlArr, $tempSql);
	}
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Week");
	array_push($tableColumn,"PM Done");

	$sql = implode(" UNION ", $sqlArr);
	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "Week"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "PM_done"){
				array_push($dataArr, $value);
			}
		}
		$json = array('week' => $row["Week"], 'weekDate' => $row["WeekDate"], 'pmDone' => $row["PM_done"]);
		array_push($tableData, $json);
	}

	$dataObj = array('data' => $dataArr, 'label' => 'PM Done');
	$chartData = array();
	array_push($chartData, $dataObj);

	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);

	// $sqlArr = array();
	// for($j=0;$j<count($week);$j++){
	// 	$ww = "week ".($j+1);
	// 	$wwArr = $week[$j];
		
	// 	$dd = implode("','", $wwArr);

	// 	$tempSql = "SELECT '$ww' as `Week`, count(*) as `PM_done` FROM `PM_Graph` where date_format(`MobileDateTime`,'%Y-%m-%d') in ('".$dd."') ";
	// 	array_push($sqlArr, $tempSql);
	// }

	// $labelArr = array();
	// $dataArr = array();
	// $tableColumn = array();
	// $tableData = array();

	// array_push($tableColumn,"Week");
	// array_push($tableColumn,"PM Done");

	// $sql = implode(" UNION ", $sqlArr);
	// $query = mysqli_query($conn,$sql);
	// while ($row = mysqli_fetch_assoc($query)) {
	// 	foreach ($row as $key => $value) {
	// 		if($key == "Week"){
	// 			array_push($labelArr, $value);
	// 		}
	// 		else if($key == "PM_done"){
	// 			array_push($dataArr, $value);
	// 		}
	// 	}
	// 	$json = array('week' => $row["Week"], 'pmDone' => $row["PM_done"]);
	// 	array_push($tableData, $json);
	// }

	// $dataObj = array('data' => $dataArr, 'label' => 'PM Done');
	// $chartData = array();
	// array_push($chartData, $dataObj);

	// $output = array();
	// $output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	// echo json_encode($output);


	// echo "<pre>";print_r($week);
}
else if($graphType == 8){
	$colorArr = array();
	$labelArr = array();
	$dataArr = array();
	$tableColumn = array();
	$tableData = array();

	array_push($tableColumn,"Circle");
	array_push($tableColumn,"Punchpoint");

	$sql = "SELECT `State`, sum(case when Status = 'Not Ok' then 1 else 0 end) as `Punchpoint` FROM `Punchpoint_Report` where `Period` = '$period' GROUP by `State` ";
	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		foreach ($row as $key => $value) {
			if($key == "State"){
				array_push($labelArr, $value);
				$color = getColorHexCode();
				array_push($colorArr, $color);
			}
			else if($key == "Punchpoint"){
				array_push($dataArr, $value);
			}
		}
		$json = array('state' => $row["State"], 'punchpoint' => $row["Punchpoint"]);
		array_push($tableData, $json);

	}

	$dataObj = array('data' => $dataArr, 'label' => 'Punchpoint');
	$chartData = array();
	array_push($chartData, $dataObj);

	$output = array();
	$output = array('labelArr' => $labelArr, 'dataArr' => $chartData, 'colorArr' => $colorArr, 'tableColumn' => $tableColumn, 'tableData' => $tableData);
	echo json_encode($output);
}
?>

<?php 
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
function getColorHexCode(){
	$characters = "0123456789ABCDEF";
	$charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 6; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return "#".$randomString;
}
?>