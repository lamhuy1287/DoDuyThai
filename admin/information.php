<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "ddThai";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed" . mysqli_connect_error());
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
// Retrieve class information
$classQuery = "SELECT * FROM Classes"; // Assuming you have a Classes table
$classResult = $conn->query($classQuery);

// Retrieve academic staff information
$GiaoVuQuery = "SELECT * FROM Users WHERE role='Academic Staff'";
$GiaoVuResult = $conn->query($GiaoVuQuery);

// Retrieve teacher information
$GiaoVienQuery = "SELECT * FROM Users WHERE role='Teacher'";
$GiaoVienResult = $conn->query($GiaoVienQuery);

// Retrieve student information
$SinhVienQuery = "SELECT 
    Students.student_id, 
    Users.user_id,
    Users.full_name, 
    Users.email, 
    Users.role, 
    Classes.class_name
FROM Students 
JOIN Users ON Students.user_id = Users.user_id
JOIN Classes ON Students.class_id = Classes.class_id
WHERE Users.role = 'Student'";
$SinhVienResult = $conn->query($SinhVienQuery);

// Handle add, edit, and delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Retrieve form data
        $fullName = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        $classId = isset($_POST['class_id']) ? $_POST['class_id'] : null;

        // Check if the email already exists
        $checkEmailQuery = "SELECT * FROM Users WHERE email = '$email'";
        $checkEmailResult = $conn->query($checkEmailQuery);

        if ($checkEmailResult->num_rows > 0) {
            echo "<script>alert('Email already exists! Please use a different email.');</script>";
        } else {
            // Insert the user into the Users table
            $insertQuery = "INSERT INTO Users (full_name, email, password, role) VALUES ('$fullName', '$email', '$password', '$role')";
            if ($conn->query($insertQuery)) {
                // If the user is a student, add to the Students table
                if ($role == 'Student' && $classId) {
                    $userId = $conn->insert_id; // Get the ID of the newly inserted user
                    $insertStudentQuery = "INSERT INTO Students (user_id, class_id) VALUES ('$userId', '$classId')";
                    $conn->query($insertStudentQuery);
                }
                echo "<script>alert('User added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding user.');</script>";
            }
        }
    }
}



?>
<!DOCTYPE html>
<html>

<head>
    <title>admin</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family= Roboto:wght@400;700&display=swap" rel="stylesheet">
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

        h1 {
            text-align: center;
        }

        .content-text {
            padding: 0px 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 2px solid #000;

        }

        th,
        td {
            padding: 12px;
            text-align: center;

        }

        th {
            background-color: #f2f2f2;
            /* Màu nền cho tiêu đề bảng */
        }

        .toc {
            margin: 20px 0;
            /* Khoảng cách cho mục lục */
            text-align: center;
            /* Căn giữa mục lục */
        }

        .toc a {
            margin: 0 15px;
            text-decoration: none;
            color: #0c787d;
            font-weight: bold;
        }
        .name {
	text-align: center;
    font-size: 30px;
    color: aliceblue;
	margin: 20px;
}
        .form-container {
            margin: 20px 0;
            text-align: center;
        }
    </style>
    <script>
        function toggleClassSelect(role) {
            const classSelect = document.getElementById('class-select');
            if (role === 'Student') {
                classSelect.style.display = 'block';
            } else {
                classSelect.style.display = 'none';
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
        <a href="information.php" class="nav-link active text-black" style="background-color:#888888;">User Information</a>
        <a href="semester.php">Semester Information</a>
        <a href="classes.php">Class Information</a>
        <a href="attendance_chart.php">Attendance Statistics</a>
        <a href="../login-logout/logout.php"></i>Logout</a>
    </div>
</div>
<div id="content-area">
    <span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
    <h1>User Information</h1>
    <div class="toc">
        <a href="#giao-vu">Academic Staff Information</a>
        <a href="#giao-vien">Teacher Information</a>
        <a href="#sinh-vien">Student Information</a>
    </div>
    <hr>

    <div class="form-container">
        <h2>Add User Information</h2>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" onchange="toggleClassSelect(this.value)" required>
                <option value="Academic Staff">Academic Staff</option>
                <option value="Teacher">Teacher</option>
                <option value="Student">Student</option>
            </select>

            <div id="class-select" style="display:none;">
                <select name="class_id">
                    <option value="">Select Class</option>
                    <?php
                    if ($classResult->num_rows > 0) {
                        while ($classRow = $classResult->fetch_assoc()) {
                            echo "<option value='" . $classRow['class_id'] . "'>" . $classRow['class_name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <br>
            <br>
            <button type="submit" name="add">Add</button>
        </form>
    </div>

    <div class="content-text">
        <h2 id="giao-vu"><u>Academic Staff Information</u></h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($GiaoVuResult->num_rows > 0) {
                while ($row = $GiaoVuResult->fetch_assoc()) {
                    $user_id = $row["user_id"];
                    echo "<tr>
                            <td>" . $row['user_id'] . "</td>
                            <td>" . $row['full_name'] . "</td>
                            <td>" . $row['email'] . "</td>
                            <td>" . $row['role'] . "</td>
                            <td style='margin-left:15px;'>
                                <form method='POST' action='edit.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Edit</button>
                                </form>
                                <form method='POST' action='delete.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No academic staff information available.</td></tr>";
            }
            ?>
        </table>
        <hr>
        <h2 id="giao-vien"><u>Teacher Information</u></h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($GiaoVienResult->num_rows > 0) {
                while ($row = $GiaoVienResult->fetch_assoc()) {
                    $user_id = $row["user_id"];
                    echo "<tr>
                            <td>" . $row['user_id'] . "</td>
                            <td>" . $row['full_name'] . "</td>
                            <td>" . $row['email'] . "</td>
                            <td>" . $row['role'] . "</td>
                            <td style='margin-left:15px;'>
                                <form method='POST' action='edit.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Edit</button>
                                </form>
                                <form method='POST' action='delete.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No teacher information available.</td></tr>";
            }
            ?>
        </table>
        <hr>
        <h2 id="sinh-vien"><u>Student Information</u></h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($SinhVienResult->num_rows > 0) {
                while ($row = $SinhVienResult->fetch_assoc()) {
                    $user_id = $row["user_id"];
                    echo "<tr>
                            <td>" . $row['user_id'] . "</td>
                            <td>" . $row['student_id'] . "</td>
                            <td>" . $row['full_name'] . "</td>
                            <td>" . $row['email'] . "</td>
                            <td>" . $row['role'] . "</td>
                            <td>" . $row['class_name'] . "</td>
                            <td style='margin-left:15px;'>
                                <form method='POST' action='edit.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Edit</button>
                                </form>
                                <form method='POST' action='delete.php' style='display:inline;'>
                                    <input name='user_id' value='$user_id' type='hidden'>
                                    <button type='submit' class='btn btn-danger m-2'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No student information available.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

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