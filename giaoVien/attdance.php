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

$user = $_SESSION['user'];
$userQuery = "SELECT full_name FROM Users WHERE email = '$user'";
$userResult = $conn->query($userQuery);
$fullName = "";

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $fullName = $userRow['full_name'];
}
$classQuery = "SELECT class_id, class_name FROM Classes";
$classResult = $conn->query($classQuery);
$subjectQuery = "SELECT subject_id, subject_name FROM Subjects";
$subjectResult = $conn->query($subjectQuery);
$students = [];

$classId = isset($_POST['class_id']) ? $_POST['class_id'] : null;
$subjectId = isset($_POST['subject_id']) ? $_POST['subject_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($classId && $subjectId) {

        $classSubjectQuery = "SELECT class_subject_id FROM ClassSubjects WHERE class_id = '$classId' AND subject_id = '$subjectId'";
        $classSubjectResult = $conn->query($classSubjectQuery);
        $classSubjectId = null;

        if ($classSubjectResult && $classSubjectResult->num_rows > 0) {
            $classSubjectRow = $classSubjectResult->fetch_assoc();
            $classSubjectId = $classSubjectRow['class_subject_id'];
        } else {
            echo "<script>alert('Eror');</script>";
        }

        $studentQuery = "
            SELECT Students.student_id, Users.user_id, Users.full_name, Users.email
            FROM Students
            JOIN Users ON Students.user_id = Users.user_id
            WHERE Students.class_id = '$classId'";
        
        $studentResult = $conn->query($studentQuery);

        if ($studentResult->num_rows > 0) {
            while ($row = $studentResult->fetch_assoc()) {
                $students[] = $row; 
            }
        }

        if (isset($_POST['attendance']) && $classSubjectId) {
            foreach ($_POST['attendance'] as $studentId => $status) {
                
                $validStatuses = ['Present', 'Absent', 'Late'];
                if (!in_array($status, $validStatuses)) {
                    continue; 
                }

                $attendanceQuery = "
                    INSERT INTO Attendance (student_id, class_subject_id, date, status)
                    VALUES ('$studentId', '$classSubjectId', CURDATE(), '$status')
                    ON DUPLICATE KEY UPDATE status = '$status'"; 
                if ($conn->query($attendanceQuery) === TRUE) {
                
                } else {
                    echo "Error recording attendance: " . $conn->error;
                }
            }
            echo "<script>alert('Attendance recorded successfully!');</script>";
        }
    } else {
        echo "<script>alert('Please select a class and subject before recording attendance!');</script>";
    }
}


$attendanceStatuses = [];
if ($classId && $subjectId) {
    $classSubjectQuery = "SELECT class_subject_id FROM ClassSubjects WHERE class_id = '$classId' AND subject_id = '$subjectId'";
    $classSubjectResult = $conn->query($classSubjectQuery);
    if ($classSubjectResult->num_rows > 0) {
        $classSubjectRow = $classSubjectResult->fetch_assoc();
        $classSubjectId = $classSubjectRow['class_subject_id'];

        $attendanceQuery = "SELECT student_id, status FROM Attendance WHERE class_subject_id = '$classSubjectId' AND date = CURDATE()";
        $attendanceResult = $conn->query($attendanceQuery);
        while ($attendanceRow = $attendanceResult->fetch_assoc()) {
            $attendanceStatuses[$attendanceRow['student_id']] = $attendanceRow['status'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attdance</title>
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
table {
    border-collapse: collapse;
    width: 100%;
    background-color: #f9f9f9;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

th {
    background-color: #4CAF50;
    color: #fff;
}

td input[type="radio"] {
    margin-right: 10px;
}
</style>
</head>
<body>
<div class="sideMenu" id="side-menu">
        
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
        <div class="name">Menu</div>
        <a href="gv_index.php" >Home</a>
<a href="attdance.php" class="navbar nav-link active text-black" style="background-color:#888888;">Attendance</a>
<a href="class+student.php">Classes and Students</a>
<a href="../login-logout/logout.php"></i>Logout</a>
</div>
	</div>
	<div id="content-area">
    <span onclick="openNav()" style="font-size:20px;cursor:pointer">☰ Menu</span>
    <button class="my_name"><?php echo $fullName; ?></button>
    <div class="content-text">
    <div class="container">
        <h1>Student Attendance</h1>
        <div class="form-container">
            <form method="POST" action="">
                <label for="class_id">Select Class:</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php while ($classRow = $classResult->fetch_assoc()) { ?>
                        <option value="<?php echo $classRow['class_id']; ?>" <?php echo ($classRow['class_id'] == $classId) ? 'selected' : ''; ?>><?php echo $classRow['class_name']; ?></option>
                    <?php } ?>
                </select>

                <label for="subject_id">Select Subject:</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php while ($subjectRow = $subjectResult->fetch_assoc()) { ?>
                        <option value="<?php echo $subjectRow['subject_id']; ?>" <?php echo ($subjectRow['subject_id'] == $subjectId) ? 'selected' : ''; ?>><?php echo $subjectRow['subject_name']; ?></option>
                    <?php } ?>
                </select>

                <button type="submit">Fetch Student List</button>
            </form>
        </div>
        <br>
        <?php if (!empty($students)) { ?>
            <form method="POST" action="">
                <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $subjectId; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) { ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['email']; ?></td>
                                <td>
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Present" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Present') ? 'checked' : ''; ?>> Present
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Absent" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Absent') ? 'checked' : ''; ?>> Absent
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Late" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Late') ? 'checked' : ''; ?>> Late
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <br>
                <button type="submit">Submit Attendance</button>
            </form>
        <?php } ?>
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
