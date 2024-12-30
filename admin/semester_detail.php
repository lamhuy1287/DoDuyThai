<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "ddThai";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Eror " . mysqli_connect_error());
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
if (isset($_POST['semester_id'])) {
    $semester_id = $_POST['semester_id'];
} else {

    echo "No semester ";
    exit();
}

$semesterQuery = "
    SELECT 
        semester_name, 
        start_date, 
        end_date 
    FROM Semesters 
    WHERE semester_id = $semester_id
";
$semesterResult = $conn->query($semesterQuery);
$semester = $semesterResult->fetch_assoc();


$classesQuery = "
    SELECT class_name 
    FROM Classes 
    WHERE semester_id = $semester_id
";
$classesResult = $conn->query($classesQuery);

// Truy vấn danh sách môn học trong học kỳ
$subjectsQuery = "
    SELECT subject_name 
    FROM Subjects 
    WHERE semester_id = $semester_id
";
$subjectsResult = $conn->query($subjectsQuery);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Semester-detail</title>
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
    </style>
</head>
<body>
	<div class="sideMenu" id="side-menu">
        
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
        <div class="name">Menu</div>

        <a href="index.php"></i>Home</a>
        <a href="information.php" >User Information</a>
        <a href="semester.php" class="nav-link active text-black" style="background-color:#888888;">Semester Information</a>
        <a href="classes.php">Class Information</a>
        <a href="attendance_chart.php">Attendance Statistics</a>
        <a href="../login-logout/logout.php"></i>Logout</a>
</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
        <hr>
		<h1>Semester Details</h1>

<?php if ($semester): ?>
    <h2>Semester Name: <?php echo $semester['semester_name']; ?></h2>
    <p><strong>Start Date:</strong> <?php echo $semester['start_date']; ?></p>
    <p><strong>End Date:</strong> <?php echo $semester['end_date']; ?></p>

    <h3>Class List</h3>
    <table>
        <?php if ($classesResult->num_rows > 0): ?>
            <?php while ($class = $classesResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $class['class_name']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td>No classes available</td></tr>
        <?php endif; ?>
    </table>

    <h3>Subject List</h3>
    <table>
        <?php if ($subjectsResult->num_rows > 0): ?>
            <?php while ($subject = $subjectsResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $subject['subject_name']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td>No subjects available</td></tr>
        <?php endif; ?>
    </table>
<?php else: ?>
    <p>The semester does not exist.</p>
<?php endif; ?>

<button class="btn btn-danger m-2" onclick="window.location.href='semester.php'">Go Back</button>
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
            