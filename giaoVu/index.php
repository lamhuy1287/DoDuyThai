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
?>
<!DOCTYPE html>
<html>
<head>
	<title>admin</title>
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
	transition: margin-left 0.5s;
	padding: 16px;
}
.content-text {
	padding: 100px 180px;
	text-align: center;
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
    <a href="index.php" class="navbar nav-link active text-black" style="background-color:#888888;">Home</a>
    <a href="student.php">Student Information</a>
    <a href="teacher.php">Teacher Information</a>
    <a href="attendance.php">Attendance Statistics</a>
    <a href="assign_teacher.php">Assign Teacher</a>
    <a href="../login-logout/logout.php">Logout</a>

</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:20px;cursor:pointer">☰ Menu</span>
		<button class="my_name"><?php echo $fullName; ?></button>
		<div class="content-text">
			
            
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
            