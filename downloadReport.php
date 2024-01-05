<?php 
include("dbConfiguration.php");
require 'PHPExcel/Classes/PHPExcel.php';
function getSafeRequestValue($key){
	$val = $_REQUEST[$key];
	return isset($val)? $val:"";
}

$jsonData = getSafeRequestValue('jsonData');
$jsonData=json_decode($jsonData);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$loginEmpState = $jsonData->loginEmpState;
$fromDate = $jsonData->fromDate;
$toDate = $jsonData->toDate;
$tenentId = $jsonData->tenentId;
$reportType = $jsonData->reportType;
$millisecond = $jsonData->millisecond;
$currentTime = time();
$format = 'yyyy-mm-dd';
$dateTimeFormat = "yyyy-mm-dd HH:mm:ss";
// if($currentTime >= $millisecond){
// 	unauthorizedAccess();
// }
// TA_DA
// else {
	if($reportType == 1){
		$objPHPExcel = new PHPExcel();
		$border_style= array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array(
						'argb' => '000000'
					)
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        	)
		);

		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',"State")
            ->setCellValue('B1',"Employee Code")
            ->setCellValue('C1',"Employee Name")
            ->setCellValue('D1',"HQ")
            ->setCellValue('E1',"Date")
            ->setCellValue('F1',"Check-in time")
            ->setCellValue('G1',"Check-out time")
            ->setCellValue('H1',"Working Hours")
            ->setCellValue('I1',"Visit Count")
            ->setCellValue('J1',"KMS Travelled");

        $filterSql = "";
        if($loginEmpRole == "Admin"){
		
		}
		else if($loginEmpRole == "RM"){
			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
			$filterSql .= " and (ta.`Employee Code` in ($empSql) or ta.`Employee Code` = '$loginEmpId')";
		}
		else if($loginEmpRole == "SH"){
			$explodeState = explode(",", $loginEmpState);
			$implodeState = implode("','", $explodeState);

			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = $tenentId and `Active` = 1";
			$filterSql .= " and (ta.`Employee Code` in ($empSql) or ta.`Employee Code` = '$loginEmpId') ";
		}
		else{
			$filterSql .= " and ta.`Employee Code` = '$loginEmpId' ";
		}

       	$sql = "SELECT ta.*, e.`State`, e.`Name` , e.`City` FROM `TA_DA_Report` ta join `Employees` e on ta.`Employee Code` = e.`EmpId` where 1=1 ".$filterSql;

       	if($fromDate != "")
       		$sql .= "and ta.`Date` >= '$fromDate' ";
       	if($toDate != "")
       		$sql .= "and ta.`Date` <= '$toDate' ";
       	$sql .= "Order by ta.`Date` desc";
       	// echo $sql;
       	$result = mysqli_query($conn,$sql);
       	$cellRow = 1;
		while($row=mysqli_fetch_assoc($result)){
			$cellRow++;

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$cellRow,$row["State"])
			->setCellValue('B'.$cellRow,$row["Employee Code"])
			->setCellValue('C'.$cellRow,$row["Name"])
			->setCellValue('D'.$cellRow,$row["City"])
			->setCellValue('E'.$cellRow,$row["Date"])
			->setCellValue('F'.$cellRow,$row["Check-In Time"])
			->setCellValue('G'.$cellRow,$row["Check-Out Time"])
			->setCellValue('H'.$cellRow,$row["WorkingHours"])
			->setCellValue('I'.$cellRow,$row["UnplannedCount"])
			->setCellValue('J'.$cellRow,$row["KMS Travelled"]);
			
		}

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getStyle("A1:J1")->getFont()->setBold(true);
        for($i=1;$i<=$cellRow;$i++){
        	$sheet->getStyle("A".$i.":J".$i)->applyFromArray($border_style);
        	if($i != 1){
        		$dataDate = $sheet->getCellByColumnAndRow(4, $i)->getValue();
			    $sheet->setCellValueByColumnAndRow(4, $i,  PHPExcel_Shared_Date::PHPToExcel($dataDate));
			    $sheet->getStyleByColumnAndRow(4, $i) ->getNumberFormat()->setFormatCode($format);

			    $checkInDatetime = $sheet->getCellByColumnAndRow(5, $i)->getValue();
			    if($checkInDatetime != ""){
			    	$sheet->setCellValueByColumnAndRow(5, $i,  PHPExcel_Shared_Date::PHPToExcel($checkInDatetime));
				    $sheet->getStyleByColumnAndRow(5, $i) ->getNumberFormat()->setFormatCode($dateTimeFormat);
			    }
				    
			    $checkOutDatetime = $sheet->getCellByColumnAndRow(6, $i)->getValue();
			    if($checkOutDatetime != ""){
			    	$sheet->setCellValueByColumnAndRow(6, $i,  PHPExcel_Shared_Date::PHPToExcel($checkOutDatetime));
				    $sheet->getStyleByColumnAndRow(6, $i) ->getNumberFormat()->setFormatCode($dateTimeFormat);
			    }
        	}
        }
		// $sheet->getDefaultStyle()->applyFromArray($border_style);
	    $objPHPExcel->getActiveSheet()->setTitle('TA_DA');
	    $objPHPExcel->setActiveSheetIndex(0);
	    $filename='TA_DA'.'_'.date('Y-m-d H:i:s');

	    header('Content-Type: application/vnd.ms-excel');
	    header('Cache-Control: max-age=0');
	    // $fileExt = "xls";
	    $fileExt = "xlsx";
	    if($fileExt == "xls"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
	    }
	    else if($fileExt == "xlsx"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
	    }
	    $objWriter->save('php://output');
	    exit;
	}
	// Attendance
	else if($reportType == 2){
		$objPHPExcel = new PHPExcel();
		$border_style= array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array(
						'argb' => '000000'
					)
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        	)
		);

		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',"ActivityId")
            ->setCellValue('B1',"State")
            ->setCellValue('C1',"Employee Code")
            ->setCellValue('D1',"Employee Name")
            ->setCellValue('E1',"HQ")
            ->setCellValue('F1',"Date")
            ->setCellValue('G1',"Outlet Name")
            ->setCellValue('H1',"Remark");

        $cellRow = 1;
       	// For only Plan -- Start --
       	// $sql = "SELECT * FROM `DailyVisit` where 1=1 ";
       	// if($fromDate != "")
       	// 	$sql .= "and `SubmitDate` >= '$fromDate' ";
       	// if($toDate != "")
       	// 	$sql .= "and `SubmitDate` <= '$fromDate'";
       	// For only Plan -- End --

       	// For Plan and incident -- Start --
       	$planSql = "SELECT * FROM `DailyVisit` where 1=1 ";
       	$nonPlanSql = "SELECT * FROM `Incident_DailyVisit` where 1=1 ";
       	if($loginEmpRole == "Admin"){
		
		}
		else if($loginEmpRole == "RM"){
			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
			$planSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
			$nonPlanSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
		}
		else if($loginEmpRole == "SH"){
			$explodeState = explode(",", $loginEmpState);
			$implodeState = implode("','", $explodeState);

			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = $tenentId and `Active` = 1";
			$planSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
			$nonPlanSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
		}
		else{
			$planSql .= " and `EmpId` = '$loginEmpId' ";
			$nonPlanSql .= " and `EmpId` = '$loginEmpId' ";
		}

       	if($fromDate != ""){
       		$planSql .= "and `SubmitDate` >= '$fromDate' ";
       		$nonPlanSql .= "and `SubmitDate` >= '$fromDate' ";
       	}
       	if($toDate != ""){
       		$planSql .= "and `SubmitDate` <= '$toDate'";
       		$nonPlanSql .= "and `SubmitDate` <= '$toDate'";
       	}
       	$sql = "SELECT * from (".$planSql." UNION ".$nonPlanSql.") t order by t.ActivityId desc";
       	// echo $sql;

       	$result = mysqli_query($conn,$sql);
		while($row=mysqli_fetch_assoc($result)){
			$cellRow++;

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$cellRow,$row["ActivityId"])
			->setCellValue('B'.$cellRow,$row["EmpState"])
			->setCellValue('C'.$cellRow,$row["EmpId"])
			->setCellValue('D'.$cellRow,$row["EmpName"])
			->setCellValue('E'.$cellRow,$row["EmpHQ"])
			->setCellValue('F'.$cellRow,$row["SubmitDate"])
			->setCellValue('G'.$cellRow,$row["OutletName"])
			->setCellValue('H'.$cellRow,$row["Remark"]);
			
		}

        $sheet = $objPHPExcel->getActiveSheet();
        for($i=1;$i<=$cellRow;$i++){
        	$sheet->getStyle("A".$i.":H".$i)->applyFromArray($border_style);
        	if($i != 1){
        		$dataDate = $sheet->getCellByColumnAndRow(5, $i)->getValue();
			    $sheet->setCellValueByColumnAndRow(5, $i,  PHPExcel_Shared_Date::PHPToExcel( $dataDate ));
			    $sheet->getStyleByColumnAndRow(5, $i) ->getNumberFormat()->setFormatCode($format);
        	}
        }
		// $sheet->getDefaultStyle()->applyFromArray($border_style);
	    $objPHPExcel->getActiveSheet()->setTitle('Attendance');
	    $objPHPExcel->setActiveSheetIndex(0);
	    $filename='Attendance'.'_'.date('Y-m-d H:i:s');

	    header('Content-Type: application/vnd.ms-excel');
	    header('Cache-Control: max-age=0');
	    // $fileExt = "xls";
	    $fileExt = "xlsx";
	    if($fileExt == "xls"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
	    }
	    else if($fileExt == "xlsx"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
	    }
	    $objWriter->save('php://output');
	    exit;
	}
	// Daily Visit
	else if($reportType == 3){
		$objPHPExcel = new PHPExcel();
		$border_style= array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array(
						'argb' => '000000'
					)
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        	)
		);

		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',"ActivityId")
            ->setCellValue('B1',"State")
            ->setCellValue('C1',"Date")
            ->setCellValue('D1',"Employee Code")
            ->setCellValue('E1',"Employee Name")
            ->setCellValue('F1',"Outlet Name")
            ->setCellValue('G1',"Contact Person Name")
            ->setCellValue('H1',"Brand Visibility")
            ->setCellValue('I1',"Remarks");

        $cellRow = 2;
        // For only Plan -- Start --
       	// $sql = "SELECT * FROM `DailyVisit` where 1=1 ";
       	// if($fromDate != "")
       	// 	$sql .= "and `SubmitDate` >= '$fromDate' ";
       	// if($toDate != "")
       	// 	$sql .= "and `SubmitDate` <= '$fromDate'";
       	// For only Plan -- End --

       	// For Plan and incident -- Start --
       	$planSql = "SELECT * FROM `DailyVisit` where 1=1 ";
       	$nonPlanSql = "SELECT * FROM `Incident_DailyVisit` where 1=1 ";
       	if($loginEmpRole == "Admin"){
		
		}
		else if($loginEmpRole == "RM"){
			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
			$planSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
			$nonPlanSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
		}
		else if($loginEmpRole == "SH"){
			$explodeState = explode(",", $loginEmpState);
			$implodeState = implode("','", $explodeState);

			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = $tenentId and `Active` = 1";
			$planSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
			$nonPlanSql .= " and (`EmpId` in ($empSql) or `EmpId` = '$loginEmpId') ";
		}
		else{
			$planSql .= " and `EmpId` = '$loginEmpId' ";
			$nonPlanSql .= " and `EmpId` = '$loginEmpId' ";
		}
		
       	if($fromDate != ""){
       		$planSql .= "and `SubmitDate` >= '$fromDate' ";
       		$nonPlanSql .= "and `SubmitDate` >= '$fromDate' ";
       	}
       	if($toDate != ""){
       		$planSql .= "and `SubmitDate` <= '$toDate'";
       		$nonPlanSql .= "and `SubmitDate` <= '$toDate'";
       	}
       	$sql = "SELECT * from (".$planSql." UNION ".$nonPlanSql.") t order by t.ActivityId desc";
       	// For Plan and incident -- End --

       	$result = mysqli_query($conn,$sql);
		while($row=mysqli_fetch_assoc($result)){
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$cellRow,$row["ActivityId"])
			->setCellValue('B'.$cellRow,$row["EmpState"])
			->setCellValue('C'.$cellRow,$row["SubmitDate"])
			->setCellValue('D'.$cellRow,$row["EmpId"])
			->setCellValue('E'.$cellRow,$row["EmpName"])
			->setCellValue('F'.$cellRow,$row["OutletName"])
			->setCellValue('G'.$cellRow,$row["Contact Person"])
			->setCellValue('H'.$cellRow,$row["Location Pic"])
        	->setCellValue('I'.$cellRow,$row["Remark"]);
			$cellRow++;
		}


        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getStyle("A1:I1")->getFont()->setBold( true );
        for($i=1;$i<$cellRow;$i++){
        	$sheet->getStyle("A".$i.":I".$i)->applyFromArray($border_style);
        	if($i != 1){
        		$dataDate = $sheet->getCellByColumnAndRow(2, $i)->getValue();
			    $sheet->setCellValueByColumnAndRow(2, $i,  PHPExcel_Shared_Date::PHPToExcel( $dataDate ));
			    $sheet->getStyleByColumnAndRow(2, $i) ->getNumberFormat()->setFormatCode($format);
        	}
        }
	    $objPHPExcel->getActiveSheet()->setTitle('Daily_Visit');
	    $objPHPExcel->setActiveSheetIndex(0);
	    $filename='Daily_Visit'.'_'.date('Y-m-d H:i:s');

	    header('Content-Type: application/vnd.ms-excel');
	    header('Cache-Control: max-age=0');
	    // $fileExt = "xls";
	    $fileExt = "xlsx";
	    if($fileExt == "xls"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
	    }
	    else if($fileExt == "xlsx"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
	    }
	    $objWriter->save('php://output');
	    exit;
	}
	else if($reportType == 4){
		// $sql="SELECT `LocationId`, concat(`Site_Type`, ' - ', `Name`) as `SiteName` FROM `Location` where `LocationId` !=1 and `Is_Active`=1 order by `LocationId`";
		$sql="SELECT * FROM `NonVisitSite` where `VisitCount`=0";
		$query=mysqli_query($conn,$sql);
		$nonVisitSiteList=array();
		while($row = mysqli_fetch_assoc($query)){
			$locationId = $row["LocationId"];
			$siteName = $row["SiteName"];

			// $trSql = "SELECT *  FROM `TransactionHDR` WHERE `Site_Name`='$siteName'";
			// $trQuery=mysqli_query($conn,$trSql);
			// $rowCount=mysqli_num_rows($trQuery);
			// if($rowCount == 0){
				$nonJson = array('locationId' => $locationId, 'siteName'=>$siteName);
				array_push($nonVisitSiteList, $nonJson);
			// }
		}

		$objPHPExcel = new PHPExcel();
		$border_style= array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array(
						'argb' => '000000'
					)
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        	)
		);

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1',"Location Id")
            ->setCellValue('B1',"Site Name");

		$cellRow = 1;
		for($i=0;$i<count($nonVisitSiteList);$i++){
			$cellRow++;
	   		$dataObj = $nonVisitSiteList[$i];
	   		
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$cellRow,$dataObj["locationId"])
			->setCellValue('B'.$cellRow,$dataObj["siteName"]);
		}

		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->getStyle("A1:B1")->getFont()->setBold(true);
		for($i=1;$i<=$cellRow;$i++){
			$sheet->getStyle("A".$i.":B".$i)->applyFromArray($border_style);
		}

		$filename='Non Visit Site';
	    $objPHPExcel->getActiveSheet()->setTitle($filename);
	    $objPHPExcel->setActiveSheetIndex(0);
	 	

	    header('Content-Type: application/vnd.ms-excel');
	    header('Cache-Control: max-age=0');
	    // $fileExt = "xls";
	    $fileExt = "xlsx";
	    if($fileExt == "xls"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
	    }
	    else if($fileExt == "xlsx"){
	    	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
	    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
	    }
	    $objWriter->save('php://output');
	    exit;
	}
	// Meter Reading Report
	// else if($reportType == 4){
	
	// }
	// Export location
	else if($reportType == 7){
		$loginEmpState = $jsonData->loginEmpState;
		$activeType = $jsonData->activeType;
		$filterSql = "";
		if($loginEmpRole == "Admin"){
		
		}
		else if($loginEmpRole == "RM"){
			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
			$filterSql .= " and (el.Emp_Id in ($empSql) or el.Emp_Id = '$loginEmpId')";
		}
		else if($loginEmpRole == "SH"){
			$empSql = "SELECT DISTINCT `EmpId` FROM `Employees` WHERE `EmpId` != '$loginEmpId' and `State` in ('$implodeState') and `Tenent_Id` = $tenentId and `Active` = 1";
			$filterSql .= " and (el.Emp_Id in ($empSql) or el.Emp_Id = '$loginEmpId') ";
		}

		// $sql = "SELECT `LocationId`, `State`, `Name` as `Site Name`, `Site_Type` as `Site Type`, (case when `Is_Active` = 1 then 'Active' else 'Deactive' end) as `Active` FROM `Location` where `LocationId` != 1 ".$filterSql." and `Is_Active` in ($activeType) ";
		
		$sql = "SELECT l.`LocationId`, l.`State`, l.`Name` as `Site Name`, l.`Site_Type` as `Site Type`, e.`Name` as `Site Create By`, `Site_CAT` as `Shop Type`, (case when l.`Is_Active` = 1 then 'Active' else 'Deactive' end) as `Active` FROM Location l join EmployeeLocationMapping el on l.LocationId = el.LocationId ".$filterSql." left join Employees e on el.Emp_Id = e.EmpId where 1=1 and l.`Tenent_Id` = $tenentId and l.`Is_Active` in ($activeType) ";

		// echo $sql;

		$result = mysqli_query($conn,$sql);
		$row=mysqli_fetch_assoc($result);
		$columnName = array();
		foreach ($row as $key => $value) {
			array_push($columnName, $key);
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=Location.csv');
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
	// Export Employee location mapping
	else if($reportType == 8){
		$sql = "SELECT loc.State, loc.Name as `Site Name`, emp.Name as `Employee Name`, ro.Role as `Role` FROM EmployeeLocationMapping empLoc join Location loc on empLoc.LocationId = loc.LocationId left join Employees emp on empLoc.Emp_Id = emp.EmpId left join Role ro on emp.RoleId = ro.RoleId ";
		$result = mysqli_query($conn,$sql);
		$row=mysqli_fetch_assoc($result);
		$columnName = array();
		foreach ($row as $key => $value) {
			array_push($columnName, $key);
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=EmpLocationMapping.csv');
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
	// Export Exployee
	else if($reportType == 9){
		$sql = "SELECT e.`Name` as `Emp Name`, e.`Mobile`, e.`EmailId`, r.`Role`, e.`State`, e2.`Name` as RM, e.`Active` FROM `Employees` e left join `Role` r on e.`RoleId` = r.`RoleId` left join `Employees` e2 on e.`RMId` = e2.`EmpId` ";

		$result = mysqli_query($conn,$sql);
		$row=mysqli_fetch_assoc($result);
		$columnName = array();
		foreach ($row as $key => $value) {
			array_push($columnName, $key);
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=Employee.csv');
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
// }

?>
<?php
header('Content-Type: text/html');
function unauthorizedAccess(){
	echo "<h1>Session Expired.</h1>";
}
function getNaIfNull($value){
	return $value == null ? "NA" : $value;
}
?>