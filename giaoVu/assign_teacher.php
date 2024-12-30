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

$teachersQuery = "SELECT user_id, full_name FROM Users WHERE role = 'Giáo viên'";
$teachersResult = $conn->query($teachersQuery);


$subjectsQuery = "SELECT subject_id, subject_name FROM Subjects";
$subjectsResult = $conn->query($subjectsQuery);

$classesQuery = "SELECT class_id, class_name FROM Classes";
$classesResult = $conn->query($classesQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $schedule = $_POST['schedule'];

    $assignQuery = "INSERT INTO ClassSubjects (class_id, subject_id, teacher_id, schedule) 
                    VALUES ('$class_id', '$subject_id', '$teacher_id', '$schedule')";
    if ($conn->query($assignQuery) === TRUE) {
        $message = "Teacher assignment successful!";
    } else {
        $message = "Eror " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher assignment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    <a href="index.php" >Home</a>
    <a href="student.php">Student Information</a>
    <a href="teacher.php">Teacher Information</a>
    <a href="attendance.php">Attendance Statistics</a>
    <a href="assign_teacher.php" class="navbar nav-link active text-black" style="background-color:#888888;">Assign Teacher</a>
    <a href="../login-logout/logout.php">Logout</a>
</div>
	</div>
	<div id="content-area">
    <span onclick="openNav()" style="font-size:20px;cursor:pointer">☰ Menu</span>
    <button class="my_name"><?php echo $fullName; ?></button>
    <div class="content-text">
        <div class="container mt-5">
            <h2 class="text-center">Assign Teacher</h2>

            <?php if (isset($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="class_id">Select Class</label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">-- Select Class --</option>
                        <?php while ($class = $classesResult->fetch_assoc()): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo $class['class_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Select Subject</label>
                    <select id="subject_id" name="subject_id" class="form-control" required>
                        <option value="">-- Select Subject --</option>
                        <?php while ($subject = $subjectsResult->fetch_assoc()): ?>
                            <option value="<?php echo $subject['subject_id']; ?>">
                                <?php echo $subject['subject_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacher_id">Select Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-control" required>
                        <option value="">-- Select Teacher --</option>
                        <?php while ($teacher = $teachersResult->fetch_assoc()): ?>
                            <option value="<?php echo $teacher['user_id']; ?>">
                                <?php echo $teacher['full_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule">Teaching Schedule (Optional)</label>
                    <input type="text" id="schedule" name="schedule" class="form-control" placeholder="E.g., Monday, 8:00 - 10:00">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Assign</button>
            </form>
        </div>
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
