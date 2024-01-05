<?php 
include("dbConfiguration.php");
header('Content-Type: text/html');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailerNew/src/Exception.php';
require 'PHPMailerNew/src/PHPMailer.php';
require 'PHPMailerNew/src/SMTP.php';

// $yesterdayDate = date('Y-m-d', strtotime('-1 day'));
$yesterdayDate = "2023-05-17";
$sql = "SELECT `EmpId`, `Name`, `EmailId` from `Employees` where `EmpId` in (SELECT DISTINCT `RMId` FROM `Employees` where `RMId` is not null and `RMId` <> '') and `Active` = 1";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$rmEmpId = $row["EmpId"];
	$rmName = $row["Name"];
	$rmEmailId = $row["EmailId"];

	$msg = "Dear $rmEmpId-$rmName,"."<br>";
	$msg .= "Please find employee visit of $yesterdayDate".".<br>";
	$msg .= "<table border=1 cellspacing=0 cellpadding=3><thead>";
	$msg .= "<tr style='background-color:#d7ac65;color:white'>";
	$msg .= "<th>Name</th> <th>Site Name</th> <th>Distance</th> <th>Visit Datetime</th>";
	$msg .= "<tr>";
	$msg .= "</thead>";
	$msg .= "<tbody>";

	$sql1 = "SELECT `EmpId`, `Name`  FROM `Employees` WHERE `RMId` = '$rmEmpId' and `Active` = 1";
	$result1 = mysqli_query($conn,$sql1);
	while($row1=mysqli_fetch_assoc($result1)){
		$empId = $row1["EmpId"];
		$empName = $row1["Name"];

		$sql2 = "SELECT h.Site_Name, d.Distance_KM, d.Visit_Date_Time FROM Distance d join TransactionHDR h on d.Activity_Id = h.ActivityId where d.Emp_Id = '$empId' and d.Visit_Date = '$yesterdayDate' and d.Event = 'Submit'";
		$result2 = mysqli_query($conn,$sql2);
		$rowCount = mysqli_num_rows($result2);
		if($rowCount > 0){
			while($row2=mysqli_fetch_assoc($result2)){
				$siteName = $row2["Site_Name"];
				$distanceKM = $row2["Distance_KM"];
				$visitDatetime = $row2["Visit_Date_Time"];
				$msg .= "<tr>";
				$msg .= "<td>$empId-$empName</td> <td>$siteName</td> <td>$distanceKM</td> <td>$visitDatetime</td>";
				$msg .= "</tr>";
			}
		}
		else{
			// $msg .= "<tr style='background-color:#fbacac;color:white'>";
			$msg .= "<tr>";
			$msg .= "<td>$empName</td> <td>No any visit</td> <td>0</td> <td>0</td>";
			$msg .= "</tr>";
		}
	}
	$msg .= "</tbody>";
	$msg .= "</table>";

	echo $msg;

	// $subject = "Visit - ".$yesterdayDate;
	// $toMailId = $rmEmailId;
	// // $toMailId = "jai.prakash@trinityapplab.co.in";
	// $mailStatus = sendMail($msg, $toMailId, $subject);
	// if($mailStatus){
	// 	echo $msg;
	// }
	// else{
	// 	echo "<h1>Something wrong</h1>";
	// }
}

		

?>

<?php 
function sendMail($msg, $toMailId, $subject, $attachment = null){
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
	$mail->addAddress($toMailId);
	// $mail->addAddress("jai.prakash@trinityapplab.co.in");

	$mail->setFrom("communication@trinityapplab.co.in","Visit Report");
	$mail->addAttachment($attachment);
	$mail->isHTML(true);   

	// CC mail's
	// $mail->addCC('helpdesk@trinityapplab.co.in');
	// $mail->addCC('anupama@nvgroup.co.in');
	
	// BCC mail's
	// $mail->addBCC("jai.prakash@trinityapplab.co.in");

	
	$mail->Subject = $subject;
	$mail->Body = "$message<br>";
	
		
	if(!$mail->send())
	{
		// echo 'Mailer Error: ' . $mail->ErrorInfo;
		// echo"<br>Could not send";
		$status = false;
	}
	else{
		// echo "mail sent ";
		$status = true;
	}

	return $status;

}
?>