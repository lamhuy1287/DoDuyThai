<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "ddThai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Eror " . $conn->connect_error);
}

if (!isset($_SESSION['user'])) {
    header("Location: login-logout/login.php");
    exit();
}

$user = $_SESSION['user']; 
$fullName = "";


$stmt = $conn->prepare("SELECT full_name FROM Users WHERE email = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $fullName = $result->fetch_assoc()['full_name'];
}
$stmt->close();


$sql = "
    SELECT 
        a.status,
        COUNT(a.status) AS total_count
    FROM Attendance a
    GROUP BY a.status;
";

$chartData = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $chartData[$row['status']] = (int)$row['total_count'];
    }
} else {
    die("Eror" . $conn->error);
}

$sql = "
    SELECT 
        MONTH(a.date) AS month,
        a.status,
        COUNT(a.status) AS total_count
    FROM Attendance a
    GROUP BY MONTH(a.date), a.status
    ORDER BY MONTH(a.date);
";

$chartDataByMonth = [];
$monthLabels = range(1, 12);
foreach ($monthLabels as $month) {
    $chartDataByMonth[$month] = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
}

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $month = $row['month'];
        $status = $row['status'];
        $count = (int)$row['total_count'];
        $chartDataByMonth[$month][$status] = $count;
    }
} else {
    die("Eror " . $conn->error);
}

$conn->close();

$chartDataJSON = json_encode($chartData, JSON_UNESCAPED_UNICODE);
$chartDataByMonthJSON = json_encode($chartDataByMonth, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance statistics</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            background: #0c787d;
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
        .main-menu a {
            padding: 10px 20px;
            text-decoration: none;
            color: #fff;
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
            transition: 0.3s;
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
            padding: 20px;
            transition: margin-left 0.5s;
        }
        .content-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        canvas {
            max-width: 80% ; 
            max-height: 50%; 
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
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
    <a href="attendance.php" class="navbar nav-link active text-black" style="background-color:#888888;">Attendance Statistics</a>
    <a href="assign_teacher.php">Assign Teacher</a>
    <a href="../login-logout/logout.php">Logout</a>
</div>
	</div>
    <div id="content-area">
        <span onclick="openNav()" style="font-size:20px;cursor:pointer">☰ Menu</span>
        <button class="my_name"><?php echo htmlspecialchars($fullName); ?></button>
        <h1 style="text-align:center;">Attendance statistics</h1>
        <div class="content-text">
            <canvas id="attendanceChart"></canvas>
            <canvas id="monthlyAttendanceChart"></canvas>
        </div>
    </div>
    <script>
      
        const chartData = <?php echo $chartDataJSON; ?>;
        const chartDataByMonth = <?php echo $chartDataByMonthJSON; ?>;

   
        new Chart(document.getElementById('attendanceChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(chartData),
                datasets: [{
                    data: Object.values(chartData),
                    backgroundColor: ['#00ff00', '#ff0000', '#ffff00']
                }]
            }
        });

  
        const labels = Object.keys(chartDataByMonth);
        const datasets = ['Present', 'Absent', 'Late'].map((status, index) => ({
            label: status,
            data: labels.map(month => chartDataByMonth[month][status]),
            backgroundColor: ['#00ff00', '#ff0000', '#ffff00'][index]
        }));

        new Chart(document.getElementById('monthlyAttendanceChart'), {
            type: 'bar',
            data: { labels, datasets }
        });
    </script>
    <script>
        function openNav() {
            document.getElementById("side-menu").style.width = "300px";
            document.getElementById("content-area").style.marginLeft = "300px";
        }
        function closeNav() {
            document.getElementById("side-menu").style.width = "0";
            document.getElementById("content-area").style.marginLeft = "0";
        }
    </script>
</body>
</html>
