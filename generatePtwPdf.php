<?php 
include("dbConfiguration.php");
require('fpdf184/fpdf.php');

$activityId=$_REQUEST['activityId'];

$sql = "SELECT h.`ActivityId`, h.`STATUS`, h.`VerifierActivityId`, h.`ApproverActivityId`, h.`ThirdActivityId`, h.`FourthActivityId`, h.`FifthActivityId`, h.`SixthActivityId`, h.`Site_Id`, h.`Site_Name`, `a`.`EmpId` as fillingByEmpId, `e`.`Name` as fillerByEmpName, `a`.`ServerDateTime` as filledDate, `a1`.`EmpId` as verifyByEmpId, `e1`.`Name` as verifiedByEmpName, `a1`.`ServerDateTime` as verifiedDate, `a2`.`EmpId` as approveByEmpId, `e2`.`Name` as approvedByEmpName, `a2`.`ServerDateTime` as approvedDate, `a3`.`EmpId` as thirdByEmpId, `e3`.`Name` as thirdByEmpName, `a3`.`ServerDateTime` as thirdByDate, `a4`.`EmpId` as fourthByEmpId, `e4`.`Name` as fourthByEmpName, `a4`.`ServerDateTime` as fourthByDate, `a5`.`EmpId` as fifthByEmpId, `e5`.`Name` as fifthByEmpName, `a5`.`ServerDateTime` as fifthByDate, `a6`.`EmpId` as sixthByEmpId, `e6`.`Name` as sixthByEmpName, `a6`.`ServerDateTime` as sixthByDate FROM `TransactionHDR` h 
join `Activity` a on `h`.`ActivityId` = `a`.`ActivityId`
left join `Activity` a1 on `h`.`VerifierActivityId` = `a1`.`ActivityId` 
left join `Activity` a2 on `h`.`ApproverActivityId` = `a2`.`ActivityId`
left join `Activity` a3 on `h`.`ThirdActivityId` = `a3`.`ActivityId`
left join `Activity` a4 on `h`.`FourthActivityId` = `a4`.`ActivityId`
left join `Activity` a5 on `h`.`FifthActivityId` = `a5`.`ActivityId`
left join `Activity` a6 on `h`.`SixthActivityId` = `a6`.`ActivityId`
join `Employees` e on `a`.`EmpId` = `e`.`EmpId` 
left join `Employees` e1 on `a1`.`EmpId` = `e1`.`EmpId` 
left join `Employees` e2 on `a2`.`EmpId` = `e2`.`EmpId` 
left join `Employees` e3 on `a3`.`EmpId` = `e3`.`EmpId` 
left join `Employees` e4 on `a4`.`EmpId` = `e4`.`EmpId`
left join `Employees` e5 on `a5`.`EmpId` = `e5`.`EmpId`
left join `Employees` e6 on `a6`.`EmpId` = `e6`.`EmpId` where h.`ActivityId` = $activityId";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);
$status = $row["STATUS"];
$ptwStatus = "";
if($status == "PTW_01") $ptwStatus = "PTW Raised";
else if($status == "PTW_02") $ptwStatus = "CDH Approved";
else if($status == "PTW_03") $ptwStatus = "Work Started";
else if($status == "PTW_04") $ptwStatus = "Site evaluated";
else if($status == "PTW_05") $ptwStatus = "Work completed";
else if($status == "PTW_06") $ptwStatus = "Closed PTW";
else if($status == "PTW_90") $ptwStatus = "High Risk";
else if($status == "PTW_98") $ptwStatus = "Cancelled";
else if($status == "PTW_99") $ptwStatus = "Rejected";
else if($status == "PTW_100") $ptwStatus = "Reject by Vendor";

$fillerByEmpName = $row["fillerByEmpName"];
$filledDate = $row["filledDate"];

$verifierActivityId = $row["VerifierActivityId"];
$verifiedByEmpName = $row["verifiedByEmpName"];
$verifiedDate = $row["verifiedDate"];

$approverActivityId = $row["ApproverActivityId"];
$approvedByEmpName = $row["approvedByEmpName"];
$approvedDate = $row["approvedDate"];

$thirdActivityId = $row["ThirdActivityId"];
$thirdByEmpName = $row["thirdByEmpName"];
$thirdByDate = $row["thirdByDate"];

$fourthActivityId = $row["FourthActivityId"];
$fourthByEmpName = $row["fourthByEmpName"];
$fourthByDate = $row["fourthByDate"];

$fifthActivityId = $row["FifthActivityId"];
$fifthByEmpName = $row["fifthByEmpName"];
$fifthByDate = $row["fifthByDate"];

$sixthActivityId = $row["SixthActivityId"];
$sixthByEmpName = $row["sixthByEmpName"];
$sixthByDate = $row["sixthByDate"];

$siteId = $row["Site_Id"];
$siteName = $row["Site_Name"];

$transactionDetList = [];
$verifyDetList = [];
$approveDetList = [];
$thirdDetList = [];
$fourthDetList = [];
$fifthDetList = [];
$sixthDetList = [];
$seventhDetList = [];
$eighthDetList = [];
if($activityId != null)
	$transactionDetList = prepareStatusDet($conn,$activityId);

if($verifierActivityId != null)
	$verifyDetList = prepareStatusDet($conn,$verifierActivityId);

if($approverActivityId != null)
	$approveDetList = prepareStatusDet($conn,$approverActivityId);

if($thirdActivityId != null)
	$thirdDetList = prepareStatusDet($conn,$thirdActivityId);

if($fourthActivityId != null)
	$fourthDetList = prepareStatusDet($conn,$fourthActivityId);

if($fifthActivityId != null)
	$fifthDetList = prepareStatusDet($conn,$fifthActivityId);

if($sixthActivityId != null)
	$sixthDetList = prepareStatusDet($conn,$sixthActivityId);

$srCellWidth = 20;
$checkpointCellWidth = 130;
$valueCellWidth = 40;
$r = 40; $g = 193; $b = 244;

class PDF extends FPDF
{
	// Page header
	// function Header()
	// {
	//     // Logo
	//     $this->Image('files/spaceTeleinfra.png',150,6,40);
	//     $this->Ln(10);
	// }

	// // Page footer
	function Footer()
	{
	    // Position at 1.5 cm from bottom
	    $this->SetY(-15);
	    // Arial italic 8
	    $this->SetFont('Arial','I',8);
	    // Page number
	    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
  
// Instantiate and use the FPDF class 
$pdf = new PDF();
$pdf->AliasNbPages();
//Add a new page
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,7,"Site - ".$siteName.' ('.$siteId.')',0);
$pdf->Ln(7);

$pdf->SetFont('Times','',12);
$pdf->Cell(0,7,"PTW status - ".$ptwStatus,0);
  
for($i=0;$i<count($transactionDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$fillerByEmpName.' on '.$filledDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$transactionDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$transactionDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$transactionDetList[$i]->value,1);
	$pdf->Ln(7);
}

for($i=0;$i<count($verifyDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$verifiedByEmpName.' on '.$verifiedDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$verifyDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$verifyDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$verifyDetList[$i]->value,1);
	$pdf->Ln(7);
}

for($i=0;$i<count($approveDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$approvedByEmpName.' on '.$approvedDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$approveDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$approveDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$approveDetList[$i]->value,1);
	$pdf->Ln(7);
}

for($i=0;$i<count($thirdDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$thirdByEmpName.' on '.$thirdByDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$thirdDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$thirdDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$thirdDetList[$i]->value,1);
	$pdf->Ln(7);
}

for($i=0;$i<count($sixthDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$sixthByEmpName.' on '.$sixthByDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$sixthDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$sixthDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$sixthDetList[$i]->value,1);
	$pdf->Ln(7);
}

$auditSql = "SELECT p.ActivityId, p.AuditActivityId, a.MobileDateTime, a.EmpId, e.Name, a.MenuId, m.Fourth as AuditChkId FROM PTWAudit p join Activity a on p.AuditActivityId = a.ActivityId join Employees e on a.EmpId = e.EmpId join Menu m on a.MenuId = m.MenuId where p.ActivityId = $activityId ORDER by p.AuditActivityId";

$auditResult = mysqli_query($conn,$auditSql);
while($auditRow = mysqli_fetch_assoc($auditResult)){
	$auditBy = $auditRow["Name"];
	$auditDate = $auditRow["MobileDateTime"];
	$auditActId = $auditRow["AuditActivityId"];
	$auditChkId = $auditRow["AuditChkId"];
	$auditChkDet = prepareStatusDet($conn,$auditActId);

	for($j=0;$j<count($auditChkDet);$j++){
		if($j==0){
			$pdf->Ln(7);
			$pdf->SetFont('Times','',12);
			$pdf->Cell(0,7,"Audit By ".$auditBy.' on '.$auditDate,0);
			$pdf->Ln(7);

			$pdf->SetFillColor($r,$g,$b);
			$pdf->SetDrawColor(25,25,12);
			$pdf->SetTextColor(255,255,255);
			$pdf->SetFont('Times','B',12);
			$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
			$pdf->SetFont('Times','B',12);
			$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
			$pdf->SetFont('Times','B',12);
			$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
			$pdf->Ln(7);
		}
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Times','',12);
		$pdf->Cell($srCellWidth,7,$auditChkDet[$j]->srNumber,1);
		$pdf->SetFont('Times','',12);
		$pdf->Cell($checkpointCellWidth,7,$auditChkDet[$j]->checkpoint,1);
		$pdf->SetFont('Times','',12);
		$pdf->Cell($valueCellWidth,7,$auditChkDet[$j]->value,1);
		$pdf->Ln(7);
	}
}

for($i=0;$i<count($fourthDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$fourthByEmpName.' on '.$fourthByDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$fourthDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$fourthDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$fourthDetList[$i]->value,1);
	$pdf->Ln(7);
}

for($i=0;$i<count($fifthDetList);$i++){
	if($i==0){
		$pdf->Ln(7);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(0,7,"By ".$fifthByEmpName.' on '.$fifthByDate,0);
		$pdf->Ln(7);

		$pdf->SetFillColor($r,$g,$b);
		$pdf->SetDrawColor(25,25,12);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($srCellWidth,7,"SR No",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($checkpointCellWidth,7,"Checkpoint",1,0,'',true);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell($valueCellWidth,7,"Value",1,0,'',true);
		$pdf->Ln(7);
	}
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($srCellWidth,7,$fifthDetList[$i]->srNumber,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($checkpointCellWidth,7,$fifthDetList[$i]->checkpoint,1);
	$pdf->SetFont('Times','',12);
	$pdf->Cell($valueCellWidth,7,$fifthDetList[$i]->value,1);
	$pdf->Ln(7);
}

// return the generated output
$pdf->Output();
?>

<?php 
function prepareStatusDet($conn, $transId){
	$sql = "SELECT `Checkpoints`.`CheckpointId`, `Checkpoints`.`Description`, `TransactionDTL`.`Value`, `TransactionDTL`.`DependChkId`, `TransactionDTL`.`Lat_Long`, `TransactionDTL`.`Date_time`, `Checkpoints`.`Value` as cp_options, `Checkpoints`.`TypeId` FROM  `TransactionDTL` join `Checkpoints` on  `TransactionDTL`.`ChkId` = `Checkpoints`.`CheckpointId`  WHERE `TransactionDTL`.`ActivityId` = '$transId' order by `TransactionDTL`.`SRNo`";
	
	$query=mysqli_query($conn,$sql);

	$dependCheckpointDetList = [];
	while($roww = mysqli_fetch_assoc($query)){
		$checkpointIdd = $roww["CheckpointId"];
		$descriptionn = $roww["Description"];
		$valuee = $roww["Value"];
		$imgLatLongg = explode(":", $roww["Lat_Long"])[0];
		$imgDatetimee = explode(",", $roww["Date_time"])[0];
		$typeIdd = $roww["TypeId"];
		$dependChkIdd = $roww["DependChkId"];
		if($dependChkIdd != 0){
			$jsonDett = "";
			$jsonDett -> checkpointId = $checkpointIdd;
			$jsonDett -> checkpoint = $descriptionn;
			$jsonDett -> value = $valuee;
			$jsonDett -> typeId = $typeIdd;
			$jsonDett -> imgLatLong = $imgLatLongg;
			$jsonDett -> imgDatetime = $imgDatetimee;
			$jsonDett -> dependChkId = $dependChkIdd;
			
			array_push($dependCheckpointDetList,$jsonDett);
		}
	}

	mysqli_data_seek( $query, 0);

	

	$statusDetList = [];
	$sr = 1;
	while($row = mysqli_fetch_assoc($query)){

		$imgLatLong = explode(":", $row["Lat_Long"])[0] ;
		$imgDatetime = explode(",", $row["Date_time"])[0] ;
		$dependChkId = $row["DependChkId"];
		if($dependChkId == 0){
			$json = "";
			$json -> optionList = [];
			$json -> srNumber = $sr;
			$json -> typeId = $row["TypeId"];
			$json -> checkpointId = $row["CheckpointId"];
			$json -> checkpoint = $row["Description"];
			$json -> options = $row["cp_options"];
			$json -> value = $row["Value"];
			$json -> imgLatLong = $imgLatLong;
			$json -> imgDatetime = $imgDatetime;

			array_push($statusDetList,$json);

			$depSrNo = 1;
			for($j=0;$j<count($dependCheckpointDetList);$j++){
				$dependentChpId = $dependCheckpointDetList[$j]->checkpointId;
				$dependentChp = $dependCheckpointDetList[$j]->checkpoint;
				$dependenChpValue = $dependCheckpointDetList[$j]->value;
				$dependenTypeId = $dependCheckpointDetList[$j]->typeId;
				$dependenDependChkId = $dependCheckpointDetList[$j]->dependChkId;
				$dependenImgLatLong = $dependCheckpointDetList[$j]->imgLatLong;
				$dependenImgDatetime = $dependCheckpointDetList[$j]->imgDatetime;

				if($row["CheckpointId"] == $dependenDependChkId){
					$jsonDettt = "";
					$jsonDettt -> srNumber = $sr.'.'.$depSrNo;
					$jsonDettt -> checkpointId = $dependentChpId;
					$jsonDettt -> checkpoint = $dependentChp;
					$jsonDettt -> value = $dependenChpValue;
					$jsonDettt -> typeId = $dependenTypeId;
					$jsonDettt -> imgLatLong = $dependenImgLatLong;
					$jsonDettt -> imgDatetime = $dependenImgDatetime;
					array_push($statusDetList,$jsonDettt);
					$depSrNo++;
				}	
			}

			$sr++;
		}		

	}

	return $statusDetList;

}
?>