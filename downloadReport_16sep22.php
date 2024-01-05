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
            ->setCellValue('I1',"Visits Planned")
            ->setCellValue('J1',"Actual Visits")
            ->setCellValue('K1',"Unplanned Visit")
            ->setCellValue('L1',"KMS Travelled");

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
			->setCellValue('I'.$cellRow,$row["Visits Planned"])
			->setCellValue('J'.$cellRow,$row["Actual Visits"])
			->setCellValue('K'.$cellRow,$row["UnplannedCount"])
			->setCellValue('L'.$cellRow,$row["KMS Travelled"]);
			
		}

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getStyle("A1:L1")->getFont()->setBold(true);
        for($i=1;$i<=$cellRow;$i++){
        	$sheet->getStyle("A".$i.":L".$i)->applyFromArray($border_style);
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
            ->setCellValue('H1',"Attendance")
            ->setCellValue('J1',"Duration ( Hours)")
            ->setCellValue('K1',"Remark")
            ->mergeCells('A1:A2')->mergeCells('B1:B2')->mergeCells('C1:C2')
            ->mergeCells('D1:D2')->mergeCells('E1:E2')->mergeCells('F1:F2')
            ->mergeCells('G1:G2')->mergeCells('H1:I1')->mergeCells('J1:J2')
            ->mergeCells('K1:K2');

        $objPHPExcel->setActiveSheetIndex(0)
        	->setCellValue('H2',"Check-in time")
        	->setCellValue('I2',"Check-out time");

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
			->setCellValue('H'.$cellRow,$row["CheckInTime"])
			->setCellValue('I'.$cellRow,$row["CheckOutTime"])
			->setCellValue('J'.$cellRow,$row["DurationOfVisitInHours"])
			->setCellValue('K'.$cellRow,$row["Remark"]);
			
		}

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getStyle("A1:K2")->getFont()->setBold(true);
        for($i=1;$i<=$cellRow;$i++){
        	$sheet->getStyle("A".$i.":K".$i)->applyFromArray($border_style);
        	if($i != 1 && $i != 2){
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
            ->setCellValue('H1',"Contact Person Mobile")
            ->setCellValue('I1',"Check-in Time")
            ->setCellValue('J1',"Check-Out Time")
            ->setCellValue('K1',"Duration of visit")
            ->setCellValue('L1',"Brand Visibility")
            ->setCellValue('M1',"Stock Availibility")
            ->setCellValue('N1',"Stock Availibility")
            ->setCellValue('O1',"Stock Availibility")
            ->setCellValue('R1',"Stock Availibility")
            ->setCellValue('U1',"Stock Availibility")
            ->setCellValue('W1',"Stock Availibility")
            ->setCellValue('Z1',"Stock Availibility")
            ->setCellValue('AE1',"Stock Availibility")
            ->setCellValue('AG1',"Stock Availibility")
            ->setCellValue('AJ1',"Remarks")
            ->mergeCells('O1:Q1')->mergeCells('R1:T1')
            ->mergeCells('U1:V1')->mergeCells('W1:Y1')
            ->mergeCells('Z1:AD1')->mergeCells('AE1:AF1')
            ->mergeCells('AG1:AI1');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('L2',"Pic")
            ->setCellValue('M2',"SMOKE VODKA CLASSIC")
            ->setCellValue('N2',"SMOKE VODKA ANISEED")
            ->setCellValue('O2',"ROYAL ENVY")
            ->setCellValue('R2',"DISCOVERY ELITE PREMIUM WHISKY")
            ->setCellValue('U2',"BLUE MOON INDIAN  GIN")
            ->setCellValue('W2',"NVs ENYY BLUE PREMIUM WHISKY")
            ->setCellValue('Z2',"BLUE MOOD PREMIUM WHISKY")
            ->setCellValue('AE2',"BLUE MOOD PREMIUM WHISKY")
            ->setCellValue('AG2',"MOJA RUM")
            ->setCellValue('AJ2',"")
           	->mergeCells('O2:Q2')->mergeCells('R2:T2')
            ->mergeCells('U2:V2')->mergeCells('W2:Y2')
            ->mergeCells('Z2:AD2')->mergeCells('AE2:AF2')
            ->mergeCells('AG2:AI2');
        $objPHPExcel->setActiveSheetIndex(0)
        	->setCellValue('M3',"750 ml")
        	->setCellValue('N3',"750 ml")
        	->setCellValue('O3',"750 ml")
        	->setCellValue('P3',"375 ml")
        	->setCellValue('Q3',"180 ml")
        	->setCellValue('R3',"750 ml")
        	->setCellValue('S3',"375 ml")
        	->setCellValue('T3',"180 ml")
        	->setCellValue('U3',"750 ml")
        	->setCellValue('V3',"370 ml")
        	->setCellValue('W3',"750 ml")
        	->setCellValue('X3',"370 ml")
        	->setCellValue('Y3',"180 ml")
        	->setCellValue('Z3',"750 ml")
        	->setCellValue('AA3',"370 ml")
        	->setCellValue('AB3',"180 ml")
        	->setCellValue('AC3',"90 ml")
        	->setCellValue('AD3',"60 ml")
        	->setCellValue('AE3',"750 ml")
        	->setCellValue('AF3',"180 ml")
        	->setCellValue('AG3',"750 ml")
        	->setCellValue('AH3',"375 ml")
        	->setCellValue('AI3',"180 ml")
        	->setCellValue('AJ3',"");

        $cellRow = 4;
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
			->setCellValue('H'.$cellRow,$row["Contact Person Mobile"])
			->setCellValue('I'.$cellRow,$row["CheckInTime"])
			->setCellValue('J'.$cellRow,$row["CheckOutTime"])
			->setCellValue('K'.$cellRow,$row["DurationOfVisitInHours"])
			->setCellValue('L'.$cellRow,$row["Location Pic"])
			->setCellValue('M'.$cellRow,getNaIfNull($row["SMOKE VODKA CLASSIC - 750 Ml"]))
        	->setCellValue('N'.$cellRow,getNaIfNull($row["SMOKE VODKA ANISEED - 750 Ml"]))
        	->setCellValue('O'.$cellRow,getNaIfNull($row["ROYAL ENVY - 750 Ml"]))
        	->setCellValue('P'.$cellRow,getNaIfNull($row["ROYAL ENVY - 375 Ml"]))
        	->setCellValue('Q'.$cellRow,getNaIfNull($row["ROYAL ENVY - 180 Ml"]))
        	->setCellValue('R'.$cellRow,getNaIfNull($row["DISCOVERY ELITE PREMIUM WHISKY - 750 Ml"]))
        	->setCellValue('S'.$cellRow,getNaIfNull($row["DISCOVERY ELITE PREMIUM WHISKY - 375 MI"]))
        	->setCellValue('T'.$cellRow,getNaIfNull($row["DISCOVERY ELITE PREMIUM WHISKY - 180 Ml"]))
        	->setCellValue('U'.$cellRow,getNaIfNull($row["BLUE MOON INDIAN GIN - 750 MI"]))
        	->setCellValue('V'.$cellRow,getNaIfNull($row["BLUE MOON INDIAN GIN - 375 MI"]))
        	->setCellValue('W'.$cellRow,getNaIfNull($row["NVs ENYY BLUE PREMIUM WHISKY - 750 MI"]))
        	->setCellValue('X'.$cellRow,getNaIfNull($row["NVs ENYY BLUE PREMIUM WHISKY - 375 MI"]))
        	->setCellValue('Y'.$cellRow,getNaIfNull($row["NVs ENYY BLUE PREMIUM WHISKY - 180 MI"]))
        	->setCellValue('Z'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 750 MI"]))
        	->setCellValue('AA'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 370 MI"]))
        	->setCellValue('AB'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 180 MI"]))
        	->setCellValue('AC'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 90 MI"]))
        	->setCellValue('AD'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 60 MI"]))
        	->setCellValue('AE'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 750 MI"]))
        	->setCellValue('AF'.$cellRow,getNaIfNull($row["BLUE MOOD PREMIUM WHISKY - 375 MI"]))
        	->setCellValue('AG'.$cellRow,getNaIfNull($row["MOJA RUM - 750 MI"]))
        	->setCellValue('AH'.$cellRow,getNaIfNull($row["MOJA RUM - 375 MI"]))
        	->setCellValue('AI'.$cellRow,getNaIfNull($row["MOJA RUM - 180 MI"]))
        	->setCellValue('AJ'.$cellRow,$row["Remark"]);
			$cellRow++;
		}


        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getStyle("A1:AJ3")->getFont()->setBold( true );
        for($i=1;$i<$cellRow;$i++){
        	$sheet->getStyle("A".$i.":AJ".$i)->applyFromArray($border_style);
        	if($i != 1 && $i != 2 && $i != 3){
        		$dataDate = $sheet->getCellByColumnAndRow(2, $i)->getValue();
			    $sheet->setCellValueByColumnAndRow(2, $i,  PHPExcel_Shared_Date::PHPToExcel( $dataDate ));
			    $sheet->getStyleByColumnAndRow(2, $i) ->getNumberFormat()->setFormatCode($format);

			    $checkInDatetime = $sheet->getCellByColumnAndRow(8, $i)->getValue();
			    if($checkInDatetime != ""){
			    	$sheet->setCellValueByColumnAndRow(8, $i,  PHPExcel_Shared_Date::PHPToExcel($checkInDatetime));
				    $sheet->getStyleByColumnAndRow(8, $i) ->getNumberFormat()->setFormatCode($dateTimeFormat);
			    }
				    
			    $checkOutDatetime = $sheet->getCellByColumnAndRow(9, $i)->getValue();
			    if($checkOutDatetime != ""){
			    	$sheet->setCellValueByColumnAndRow(9, $i,  PHPExcel_Shared_Date::PHPToExcel($checkOutDatetime));
				    $sheet->getStyleByColumnAndRow(9, $i) ->getNumberFormat()->setFormatCode($dateTimeFormat);
			    }
        	}
        }
		// $sheet->getDefaultStyle()->applyFromArray($border_style);
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
	// Meter Reading Report
	// else if($reportType == 4){
	// 	$filterSql = "";
	// 	if($loginEmpRole != 'Admin' && $loginEmpRole != 'SpaceWorld' && $loginEmpRole != "Management"){
	// 		$filterSql .= "and m.`Emp Id` = '$loginEmpId' ";
	// 	}
		

	// 	$sql = "SELECT m.`ActivityId`, l.`State` as `Circle`, l.`City`, m.`Site Name`, m.`Site Id`, l.`Site_Type` as `Site Type`, m.`Submit By`, m.`Submit Date`, m.`Do you have sub meter?`, m.`Main Meter Reading`, m.`Sub Meter Reading`, m.`Sub Meter Reading 2`, m.`Sub Meter Reading 3`, m.`Sub Meter Reading 4`, m.`Remark` FROM `Meter_Reading_Report` m join `Location` l on m.`Site Id` = l.`Site_Id` and m.`Site Name` = l.`Name` WHERE 1=1 ".$filterSql;

	// 	if($fromDate != "")
	// 		$sql .= "and m.`ServerDateTime` >= '$fromDate' ";
	// 	if($toDate != "")
	// 		$sql .= "and m.`ServerDateTime` <= '$toDate' ";
	// 	$sql .= "order by m.`ActivityId` desc";

	// 	$result = mysqli_query($conn,$sql);
	// 	$row=mysqli_fetch_assoc($result);
	// 	$columnName = array();
	// 	foreach ($row as $key => $value) {
	// 		array_push($columnName, $key);
	// 	}

	// 	header('Content-Type: text/csv; charset=utf-8');
	// 	header('Content-Disposition: attachment; filename=Meter_Reading_Report.csv');
	// 	$output = fopen('php://output', 'w');
	// 	fputcsv($output, $columnName);

	// 	mysqli_data_seek($result, 0);
	// 	while($row=mysqli_fetch_assoc($result)){
	// 		$exportData = array();
	// 		foreach ($columnName as $key => $value) {
	// 			array_push($exportData, $row[$value]);
	// 		}
	// 		fputcsv($output, $exportData);
	// 	}
	// }
	// Training Report
	// else if($reportType == 5){
	// 	$filterSql = "";
	// 	if($loginEmpRole != 'Admin' && $loginEmpRole != 'SpaceWorld' && $loginEmpRole != "Management"){
	// 		$filterSql .= "and `Emp Id` = '$loginEmpId' ";
	// 	}
	// 	$sql = "SELECT `Training Name`, `Submit By`, `Submit Date`, `Total Question`, `Correct`, `Incorrect`, `Percentage`, `Result` FROM `Training_Report` WHERE 1=1 ".$filterSql;

	// 	if($fromDate != "")
	// 		$sql .= "and `ServerDateTime` >= '$fromDate' ";
	// 	if($toDate != "")
	// 		$sql .= "and `ServerDateTime` <= '$toDate' ";
	// 	$sql .= "order by `ActivityId` desc";

	// 	$result = mysqli_query($conn,$sql);
	// 	$row=mysqli_fetch_assoc($result);
	// 	$columnName = array();
	// 	foreach ($row as $key => $value) {
	// 		array_push($columnName, $key);
	// 	}

	// 	header('Content-Type: text/csv; charset=utf-8');
	// 	header('Content-Disposition: attachment; filename=Training_Report.csv');
	// 	$output = fopen('php://output', 'w');
	// 	fputcsv($output, $columnName);

	// 	mysqli_data_seek($result, 0);
	// 	while($row=mysqli_fetch_assoc($result)){
	// 		$exportData = array();
	// 		foreach ($columnName as $key => $value) {
	// 			array_push($exportData, $row[$value]);
	// 		}
	// 		fputcsv($output, $exportData);
	// 	}
	// }
	// Punchpoint Report
	// else if($reportType == 6){
	// 	$filterSql = "";
	// 	if($loginEmpRole != 'Admin' && $loginEmpRole != 'SpaceWorld' && $loginEmpRole != "Management"){
	// 		$filterSql .= "and `EmpId` = '$loginEmpId' ";
	// 	}
	// 	$sql = "SELECT `ActivityId` as Report_Id, `Site Id`, `Site Name`, `Submit By`, `Submit Date`, `Description`, `Status`, `Remark` 
	// 	FROM `Punchpoint_Report` WHERE 1=1 ".$filterSql;

	// 	if($fromDate != "")
	// 		$sql .= "and `MobileDateTime` >= '$fromDate' ";
	// 	if($toDate != "")
	// 		$sql .= "and `MobileDateTime` <= '$toDate' ";
	// 	$sql .= "order by `ActivityId` desc";

	// 	$result = mysqli_query($conn,$sql);
	// 	$row=mysqli_fetch_assoc($result);
	// 	$columnName = array();
	// 	foreach ($row as $key => $value) {
	// 		array_push($columnName, $key);
	// 	}

	// 	header('Content-Type: text/csv; charset=utf-8');
	// 	header('Content-Disposition: attachment; filename=Punchpoint_Report.csv');
	// 	$output = fopen('php://output', 'w');
	// 	fputcsv($output, $columnName);

	// 	mysqli_data_seek($result, 0);
	// 	while($row=mysqli_fetch_assoc($result)){
	// 		$exportData = array();
	// 		foreach ($columnName as $key => $value) {
	// 			array_push($exportData, $row[$value]);
	// 		}
	// 		fputcsv($output, $exportData);
	// 	}
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