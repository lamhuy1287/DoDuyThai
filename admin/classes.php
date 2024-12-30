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
// Lấy học kỳ hiện tại
$currentSemesterQuery = "
    SELECT semester_id, semester_name 
    FROM Semesters 
    WHERE CURRENT_DATE BETWEEN start_date AND end_date
";
$currentSemesterResult = $conn->query($currentSemesterQuery);

if ($currentSemesterResult->num_rows > 0) {
    $currentSemester = $currentSemesterResult->fetch_assoc();
    $semester_id = $currentSemester['semester_id'];
    $semester_name = $currentSemester['semester_name'];
} else {
    die("Eror");
}
$classesQuery = "SELECT class_id, class_name FROM Classes WHERE semester_id = $semester_id";
$classesResult = $conn->query($classesQuery);

$semestersQuery = "SELECT semester_id, semester_name FROM Semesters";
$semestersResult = $conn->query($semestersQuery);

if (isset($_POST['create_class'])) {
    $className = $_POST['class_name'];
    $semesterId = $_POST['semester_id'];

    // Insert the class into the database
    $insertClassQuery = "INSERT INTO Classes (class_name, semester_id) VALUES ('$className', $semesterId)";
    if ($conn->query($insertClassQuery) === TRUE) {
        echo "Class has been created successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>List of classes</title>
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
	background: teal;
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
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            
        }
        th {
            background-color: #f2f2f2;
        }
        .details {
            display: none;
            width: 100%;
        }
        
        .container {
            display: flex;
            justify-content:center;
            align-items: flex-start;
        }
        .details-table {
            margin-right: 20px;
            
        }
        
    </style>
    <script>
        function toggleDetails(classId) {
            const detailsDiv = document.getElementById(`details-${classId}`);
            if (detailsDiv.style.display === "none") {
                detailsDiv.style.display = "block";
            } else {
                detailsDiv.style.display = "none";
            }
        }
    </script>
</head>
<body>
<div class="sideMenu" id="side-menu">
        
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
        <div class="name">Menu</div>

        <a href="index.php"></i>Home</a>
        <a href="information.php" >User Information</a>
        <a href="semester.php">Semester Information</a>
        <a href="classes.php"  class="nav-link active text-black" style="background-color:#888888;">Class Information</a>
        <a href="attendance_chart.php">Attendance Statistics</a>
        <a href="../login-logout/logout.php"></i>Logout</a>
</div>
	</div>
	<div id="content-area">
    <span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
    <h1>Create New Class</h1>
<form method="POST" action="">
    <label for="class_name">Class Name:</label>
    <input type="text" id="class_name" name="class_name" required>
    
    <label for="semester_id">Select Semester:</label>
    <select id="semester_id" name="semester_id" required>
        <?php if ($semestersResult->num_rows > 0): ?>
            <?php while ($semester = $semestersResult->fetch_assoc()): ?>
                <option value="<?php echo $semester['semester_id']; ?>">
                    <?php echo $semester['semester_name']; ?>
                </option>
            <?php endwhile; ?>
        <?php else: ?>
            <option value="">No semesters available</option>
        <?php endif; ?>
    </select>
    
    <input type="submit" name="create_class" value="Create Class">
</form>
    <h1>Class List</h1>
    <h2>Current Semester: <?php echo $semester_name; ?></h2>

    <table>
        <tr>
            <th>Class Name</th>
            <th>Action</th>
        </tr>
        <?php if ($classesResult->num_rows > 0): ?>
            <?php while ($class = $classesResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $class['class_name']; ?></td>
                    <td>
                        <button onclick="toggleDetails(<?php echo $class['class_id']; ?>)">Show Details</button>
                    </td>
                </tr>
                <tr id="details-<?php echo $class['class_id']; ?>" class="details">
                    <td colspan="2" class="detailss">
                        <?php
                        $classId = $class['class_id'];

                        // Retrieve the list of students
                        $studentsQuery = "
                            SELECT Users.full_name 
                            FROM Students 
                            INNER JOIN Users ON Students.user_id = Users.user_id 
                            WHERE Students.class_id = $classId
                        ";
                        $studentsResult = $conn->query($studentsQuery);

                        // Retrieve the list of subjects
                        $subjectsQuery = "
                            SELECT subject_name 
                            FROM Subjects 
                            WHERE semester_id = $semester_id
                        ";
                        $subjectsResult = $conn->query($subjectsQuery);
                        ?>
                        <div class="container">
                            <div class="details-table">
                                <h3>Student List</h3>
                                <table>
                                    <tr><th>Student Name</th></tr>
                                    <?php if ($studentsResult->num_rows > 0): ?>
                                        <?php while ($student = $studentsResult->fetch_assoc()): ?>
                                            <tr><td><?php echo $student['full_name']; ?></td></tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td>No students available.</td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <div class="details-table">
                                <h3>Subject List</h3>
                                <table>
                                    <tr><th>Subject Name</th></tr>
                                    <?php if ($subjectsResult->num_rows > 0): ?>
                                        <?php while ($subject = $subjectsResult->fetch_assoc()): ?>
                                            <tr><td><?php echo $subject['subject_name']; ?></td></tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td>No subjects available.</td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No classes available in this semester.</td></tr>
        <?php endif; ?>
    </table>
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
