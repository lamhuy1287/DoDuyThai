<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ddThai";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$user = $_SESSION['user']; // This should be the logged-in user's ID or identifier
$userQuery = "SELECT full_name FROM Users WHERE email = '$user'"; // Modify to your login identifier if it's not 'email'
$userResult = $conn->query($userQuery);
$fullName = "";

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $fullName = $userRow['full_name'];
}

$sql = "SELECT c.class_id, c.class_name, COUNT(s.student_id) AS total_students
        FROM Classes c
        LEFT JOIN Students s ON c.class_id = s.class_id
        GROUP BY c.class_id";
$result = $conn->query($sql);


$class_data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $class_id = $row["class_id"];
        $class_name = $row["class_name"];
        $total_students = $row["total_students"];


        $sql_students = "SELECT u.full_name, u.email
                         FROM Students s
                         JOIN Users u ON s.user_id = u.user_id
                         WHERE s.class_id = $class_id";
        $result_students = $conn->query($sql_students);

        $students = [];
        if ($result_students->num_rows > 0) {
            while($student = $result_students->fetch_assoc()) {
                $students[] = $student;
            }
        }

   
        $class_data[] = [
            'class_name' => $class_name,
            'total_students' => $total_students,
            'students' => $students
        ];
    }
} else {
    $class_data = null;
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin lớp học và sinh viên</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <style>
        body {
            
            background-color: #f4f4f9;
            
            padding: 20px;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .class-info {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .class-info h3 {
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            background-color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
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
<a href="gv_index.php" >Home</a>
<a href="attdance.php">Attendance</a>
<a href="class+student.php" class="navbar nav-link active text-black" style="background-color:#888888;">Classes and Students</a>
<a href="../login-logout/logout.php"></i>Logout</a>
</div>
	</div>
	<div id="content-area">
    <span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
    
    <div class="container">
        <h1>Class and Student Information</h1>

        <?php if ($class_data): ?>
            <?php foreach ($class_data as $class): ?>
                <div class="class-info">
                    <h3>Class: <?php echo $class['class_name']; ?> - Total Students: <?php echo $class['total_students']; ?></h3>

                    <?php if (!empty($class['students'])): ?>
                        <table>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                            </tr>
                            <?php foreach ($class['students'] as $student): ?>
                                <tr>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['email']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>No students in this class.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No classes available.</p>
        <?php endif; ?>

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
