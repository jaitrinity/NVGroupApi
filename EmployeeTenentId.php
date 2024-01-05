<?php
class EmployeeTenentId{
	function getTenentIdByEmpId($conn, $empId) {
		$sql = "SELECT * FROM `Employees` where `EmpId` = '$empId' and `Active` = 1 ";
		$result = mysqli_query($conn,$sql);
		$tenentId  = 0;
		while($row = mysqli_fetch_assoc($result)){
			$tenentId = $row["Tenent_Id"];
		}
		return $tenentId;
	}
	function getTenentIdByMobile($conn, $mobile) {
		$sql = "SELECT * FROM `Employees` where `Mobile` = '$mobile' and `Active` = 1 ";
		$result = mysqli_query($conn,$sql);
		$tenentId  = 0;
		while($row = mysqli_fetch_assoc($result)){
			$tenentId = $row["Tenent_Id"];
		}
		return $tenentId;
	}
	// function getEmployeeCirleName($conn, $empId, $tenentId){
	// 	$sql = "SELECT * FROM `Employees` where `EmpId` = '$empId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	// 	$result = mysqli_query($conn,$sql);
	// 	$circleName  = "";
	// 	while($row = mysqli_fetch_assoc($result)){
	// 		$circleName = $row["State"];
	// 	}
	// 	return $circleName;
	// }
	function getEmployeeInfo($conn, $empId){
		$sql = "SELECT `e1`.*, `Role`.`Role` as roleName, e2.`Name` as rmName FROM `Employees` e1 join `Role` on `e1`.`RoleId` = `Role`.`RoleId` left join `Employees` e2 on e1.`RMId` = e2.`EmpId` where e1.`EmpId` = '$empId' and e1.`Active` = 1 ";
		$result = mysqli_query($conn,$sql);
		
		$row = mysqli_fetch_assoc($result);
		$id = $row["Id"];
		$empId = $row["EmpId"];
		$empName = $row["Name"];
		$password = $row["Password"];
		$mobile = $row["Mobile"];
		$emailId = $row["EmailId"];
		$roleId = $row["RoleId"];
		$area = $row["Area"];
		$city = $row["City"];
		$state = $row["State"];
		$rmId = $row["RMId"];
		$fieldUser = $row["FieldUser"];
		$active = $row["Active"];
		$tenentId = $row["Tenent_Id"];
		$roleName = $row["roleName"];
		$rmName = $row["rmName"];
		$empInfo = array(
			'id' => $id,
			'empId' => $empId,
			'empName' => $empName,
			'password' => $password,
			'mobile' => $mobile,
			'emailId' => $emailId,
			'roleId' => $roleId,
			'area' => $area,
			'city' => $city,
			'state' => $state,
			'rmId' => $rmId,
			'fieldUser' => $fieldUser,
			'fieldUserValue' => $fieldUser == 1 ? "Yes" : "No",
			'active' => $active,
			'tenentId' => $tenentId,
			'roleName' => $roleName,
			'rmName' => $rmName,
		);
		return $empInfo;
	}
}
?>