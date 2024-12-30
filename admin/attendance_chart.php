<?php
session_start();
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = ""; // Thay bằng mật khẩu của bạn
$dbname = "ddThai";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Truy vấn thống kê tổng quan
$sql = "SELECT 
    a.status,
    COUNT(a.status) AS total_count
FROM Attendance a
GROUP BY a.status;
";

$result = $conn->query($sql);

// Xử lý dữ liệu thành định dạng JSON
$chartData = [];
while ($row = $result->fetch_assoc()) {
    $chartData[$row['status']] = (int)$row['total_count'];
}

// Truy vấn thống kê status theo tháng
$sql = "
SELECT 
    MONTH(a.date) AS month,
    a.status,
    COUNT(a.status) AS total_count
FROM Attendance a
GROUP BY MONTH(a.date), a.status
ORDER BY MONTH(a.date);
";

$result = $conn->query($sql);

$chartDataByMonth = [];
$monthLabels = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];


foreach ($monthLabels as $month) {
    $chartDataByMonth[$month] = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
}

while ($row = $result->fetch_assoc()) {
    $month = $row['month'];
    $status = $row['status'];
    $count = (int)$row['total_count'];

    $chartDataByMonth[$month][$status] = $count;
}

$conn->close();

$chartDataJSON = json_encode($chartData);
$chartDataByMonthJSON = json_encode($chartDataByMonth);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall attendance statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
.name {
	text-align: center;
    font-size: 30px;
    color: aliceblue;
	margin: 20px;
}
.content-text {
    display: flex;
    flex-direction: column;
    align-items: center; 
    gap: 30px; 
    padding: 20px;
    margin: auto;
}

canvas {
    max-width: 80% ; 
    max-height: 50%; 
    border: 1px solid #ccc; 
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
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
        <a href="semester.php">Semester Information</a>
        <a href="classes.php">Class Information</a>
        <a href="attendance_chart.php" class="nav-link active text-black" style="background-color:#888888;">Attendance Statistics</a>
        <a href="../login-logout/logout.php"></i>Logout</a>
    </div>
</div>
<div id="content-area">
    <span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
    <h1 style="text-align: center;">Attendance Overview Statistics</h1>
    <div class="content-text">
    <canvas id="attendanceChart"></canvas>
    <canvas id="monthlyAttendanceChart"></canvas>

    <script>
        // Data from PHP
        const chartData = <?php echo $chartDataJSON; ?>;
        const chartDataByMonth = <?php echo $chartDataByMonthJSON; ?>;

        // Prepare data for Chart.js
        const labels = Object.keys(chartData); // Attendance statuses (Present, Absent, Late)
        const data = Object.values(chartData); // Corresponding counts

        // Render the overview chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie', // Pie chart
            data: {
                labels: labels, // Statuses
                datasets: [{
                    label: 'Total Count',
                    data: data, // Counts
                    backgroundColor: ['#ff0000', '#00ff00', '#ffff00'], // Colors (red, green, yellow)
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }, // Place the legend at the bottom
                    title: { 
                        display: true, 
                        text: 'Overall Attendance Statistics',
                        font: { size: 16 }
                    }
                }
            }
        });

        // Prepare data for the monthly chart
        const monthlyLabels = Object.keys(chartDataByMonth);
        const monthlyData = monthlyLabels.map(month => {
            return [
                chartDataByMonth[month]['Present'],
                chartDataByMonth[month]['Absent'],
                chartDataByMonth[month]['Late']
            ];
        });

        // Render the monthly chart
        const monthlyCtx = document.getElementById('monthlyAttendanceChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar', // Bar chart
            data: {
                labels: monthlyLabels, // Months
                datasets: [
                    {
                        label: 'Present',
                        data: monthlyData.map(data => data[0]),
                        backgroundColor: '#00ff00'
                    },
                    {
                        label: 'Absent',
                        data: monthlyData.map(data => data[1]),
                        backgroundColor: '#ff0000'
                    },
                    {
                        label: 'Late',
                        data: monthlyData.map(data => data[2]),
                        backgroundColor: '#ffff00'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { 
                        display: true, 
                        text: 'Monthly Attendance Statistics',
                        font: { size: 16 }
                    }
                }
            }
        });
    </script>
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