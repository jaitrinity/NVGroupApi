<?php 
include("dbConfiguration.php");
require 'PHPExcel/Classes/PHPExcel.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailerNew/src/Exception.php';
require 'PHPMailerNew/src/PHPMailer.php';
require 'PHPMailerNew/src/SMTP.php';


$yesterdayDate = date('Y-m-d', strtotime('-1 day'));
// $yesterdayDate = "2023-05-01";

$format = 'yyyy-mm-dd';
$dateTimeFormat = "yyyy-mm-dd HH:mm:ss";

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

	$sql = "SELECT ta.*, e.`State`, e.`Name` , e.`City` FROM `TA_DA_Report` ta join `Employees` e on ta.`Employee Code` = e.`EmpId` where ta.Date = '$yesterdayDate'";
	// $sql = "SELECT ta.*, e.`State`, e.`Name` , e.`City` FROM `TA_DA_Report` ta join `Employees` e on ta.`Employee Code` = e.`EmpId` Order by ta.`Date` desc";

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
$objPHPExcel->getActiveSheet()->setTitle('TA_DA');
$objPHPExcel->setActiveSheetIndex(0);
$filename='TA_DA.xlsx';

header("Content-Type: application/vnd.ms-excel");
header("Cache-Control: max-age=0");

header("Content-Disposition: attachment;filename=".$filename);
$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
$objWriter->save("/var/www/trinityapplab.co.in/NVGroup/files/".$filename);
$msg = "Dear Team, "."<br>";
$msg .= "Please find TA_DA report for $yesterdayDate date."."<br><br>";
// $msg .= "PFA"."<br><br>";
$msg .= "Regards"."<br>";
$msg .= "Trinity Automation Team.";
sendMail($msg, "/var/www/trinityapplab.co.in/NVGroup/files/".$filename);
?>

<?php 
function sendMail($msg, $attachment){
	$status = false;

	$message = $msg;
	
	$mail = new PHPMailer;
	
	$mail->isSMTP();                                      
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'communication@trinityapplab.co.in';
	$mail->Password = 'communication@Trinity';   
	$mail->Port = 587;
	$mail->SMTPSecure = 'tls';
	
	// To mail's
	$mail->addAddress("anupama@nvgroup.co.in");
	$mail->addAddress("akhilbhatnagar@nvgroup.co.in");
	// $mail->addAddress("jai.prakash@trinityapplab.co.in");

	$mail->setFrom("communication@trinityapplab.co.in","Visit Report");
	$mail->addAttachment($attachment);
	$mail->isHTML(true);   

	// CC mail's
	$mail->addCC('helpdesk@trinityapplab.co.in');
	// $mail->addCC('anupama@nvgroup.co.in');
	
	// BCC mail's
	// $mail->addBCC("jai.prakash@trinityapplab.co.in");

	
	$mail->Subject = 'Visit Report';
	$mail->Body = "$message<br>";
	
		
	if(!$mail->send())
	{
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		echo"<br>Could not send";
		$status = false;
	}
	else{
		echo "mail sent ";
		$status = true;
	}

	return $status;

}
?>