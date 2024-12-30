<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "ddThai";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Eror" . mysqli_connect_error());
  }
  if (!isset($_SESSION['user'])) {
    header("Location: login-logout/login.php");
    exit();
}
$user = $_SESSION['user']; // This should be the logged-in user's ID or identifier
$userQuery = "SELECT full_name FROM Users WHERE email = '$user'"; // Modify to your login identifier if it's not 'email'
$userResult = $conn->query($userQuery);
$fullName = "";

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $fullName = $userRow['full_name'];
}
$attendanceQuery = " SELECT 
    Students.student_id,
    Users.full_name AS student_name,
    Classes.class_name,
    Semesters.semester_name,
    Subjects.subject_name,
    Teachers.full_name AS teacher_name,
    Attendance.date AS attendance_date,
    Attendance.status AS attendance_status
FROM 
    Students
JOIN 
    Users ON Students.user_id = Users.user_id
JOIN 
    Classes ON Students.class_id = Classes.class_id
JOIN 
    Semesters ON Classes.semester_id = Semesters.semester_id
JOIN 
    ClassSubjects ON Classes.class_id = ClassSubjects.class_id
JOIN 
    Subjects ON ClassSubjects.subject_id = Subjects.subject_id
JOIN 
    Users AS Teachers ON ClassSubjects.teacher_id = Teachers.user_id
LEFT JOIN 
    Attendance ON Students.student_id = Attendance.student_id 
               AND ClassSubjects.class_subject_id = Attendance.class_subject_id
WHERE
    Users.email = '$user'  
ORDER BY 
    Students.student_id, Attendance.date;
";
    $studentInfoQuery = "SELECT 
                            Users.full_name AS student_name,
                            Classes.class_name,
                            Users.email AS student_email
                            
                        FROM 
                            Students
                        JOIN 
                            Users ON Students.user_id = Users.user_id
                        JOIN 
                            Classes ON Students.class_id = Classes.class_id
                        
                        WHERE 
                            Users.email = '$user'";  
    $attendanceResult = $conn->query($attendanceQuery);
    $studentInfoResult = $conn->query($studentInfoQuery);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sinh viên</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
	<style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
}
.sideMenu {
	height: 100%;
	width: 0;
	position: fixed;
	z-index: 1;
	top: 0;
	left: 0;
	background:  #0c787d;
	overflow-x: hidden;
	transition: 0.5s;
	padding-top: 60px;
}
.main-menu h2 {
	text-align: center;
	letter-spacing: 7px;
	color: #fff;
	background: #111;
	padding: 20px 0;
}
.sideMenu a {
	padding: 8px 8px 8px 32px;
	text-decoration: none;
	color: #fff;
	display: block;
	transition: 0.3s;
	font-size: 18px;
	margin-bottom: 20px;
	text-transform: uppercase;
	
}
.sideMenu a i {
	padding-right: 15px;
}
.main-menu a:hover {
	color: #f1f1f1;
	background: #BBBBBB;
}
.sideMenu .closebtn {
	position: absolute;
	top: 0;
	right: 25px;
	font-size: 36px;
	margin-left: 50px;
}
#content-area {
	transition: margin-left .5s;
	padding: 16px;
}

.name {
	text-align: center;
    font-size: 30px;
    color: aliceblue;
	margin: 20px;
}
.my_name {
	float: right;
    padding: 10px;
    color: aliceblue;
    background: crimson;
    border: 0;
    border-radius: 10px;
}  
.navbar{
	font-size:20px
}       
    </style>
</head>
<body>
	<div class="sideMenu" id="side-menu">
        
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
    <div class="name">Menu</div>
    <a href="sv_index.php"class="navbar nav-link active text-black" style="background-color:#888888;">Information</a>
    <a href="../login-logout/logout.php">Logout</a>
</div>
	</div>
	<div id="content-area">
    <span onclick="openNav()" style="font-size:20px;cursor:pointer">☰ Menu</span>
    <button class="my_name"><?php echo $fullName; ?></button>

    <div class="student-info">
        <h3>Student Information</h3>
        <?php
        if ($studentInfoResult->num_rows > 0) {
            $studentInfo = $studentInfoResult->fetch_assoc(); // Fetch student information
            echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                    <tr style='background-color: #0c787d; color: white;'>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Student Name</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Class</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Email</th>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$studentInfo['student_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$studentInfo['class_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$studentInfo['student_email']}</td>
                    </tr>
                  </table>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>Student information not found.</p>";
        }
        ?>
    </div>

    <div class="content-text">
        <h3>Attendance Status</h3>
        <?php
        if ($attendanceResult->num_rows > 0) {
            echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                    <tr style='background-color: #0c787d; color: white;'>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Student</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Class</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Semester</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Subject</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Teacher</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Date</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Status</th>
                    </tr>";
            while ($row = $attendanceResult->fetch_assoc()) {
                echo "<tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['student_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['class_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['semester_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['subject_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['teacher_name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['attendance_date']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$row['attendance_status']}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>No attendance records found.</p>";
        }
        ?>
    </div>
</div>

	<script>
	function openNav() {
	 document.getElementById("side-menu").style.width = "300px";
	 document.getElementById("content-area").style.marginLeft = "300px"; 
	}

	function closeNav() {
	 document.getElementById("side-menu").style.width = "0";
	 document.getElementById("content-area").style.marginLeft= "0";  
	}
	</script>
</body>
</html>
            