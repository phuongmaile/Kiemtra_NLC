<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">PHP Example</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container my-3">
        <nav class="alert alert-primary" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
        </nav>

        <!-- Form upload file -->
        <form class="row" method="POST" enctype="multipart/form-data">
            <div class="col">
                <div class="mb-3">
                    <input type="file" accept=".txt" class="form-control" name="file">
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Import database</button>
            </div>
        </form>

        <?php
        if (isset($_POST['submit'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                // Nhận đường dẫn file tạm
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileType = $_FILES['file']['type'];

                // Kiểm tra xem file có phải là TXT hay không
                if ($fileType == 'text/plain') {
                    // 1. Đọc dữ liệu file txt
                    $file = fopen($fileTmpPath, 'r');
                    $successCount = 0;
                    $failCount = 0;

                    // 2. Kết nối đến database
                    $server = "localhost";
                    $database = "db_nguyen_linh_chi"; 
                    $username = "root";
                    $password = "";

                    $conn = new mysqli($server, $username, $password, $database);

                    if ($conn->connect_error) {
                        die('Connection failed: ' . $conn->connect_error);
                    }

                    // 2.1 Đọc từng dòng trong file txt
                    while (($line = fgetcsv($file)) !== FALSE) {
                        $title = $conn->real_escape_string($line[0]);
                        $description = $conn->real_escape_string($line[1]);
                        $imageUrl = $conn->real_escape_string($line[2]);

                        // 2.2 Kiểm tra xem tiêu đề đã tồn tại hay chưa
                        $checkQuery = "SELECT * FROM Course WHERE Title='$title'";
                        $result = $conn->query($checkQuery);

                        if ($result->num_rows == 0) {
                            // 2.3 Chưa tồn tại thì chèn vào database
                            $insertQuery = "INSERT INTO Course (Title, Description, ImageUrl) VALUES ('$title', '$description', '$imageUrl')";
                            if ($conn->query($insertQuery) === TRUE) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        } else {
                            $failCount++;
                        }
                    }

                    fclose($file);
                    $conn->close();

                    // 3. Thông báo kết quả
                    echo '<div class="alert alert-info mt-2" role="alert">';
                    echo "$successCount records inserted successfully, $failCount records failed.";
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning" role="alert">Vui lòng tải lên file .txt!</div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">Lỗi tải file!</div>';
            }
        }
        ?>

        <!-- Hiển thị dữ liệu từ database -->
        <hr>

        <?php
        // Kết nối đến database
        $server = "localhost";
        $database = "db_nguyen_linh_chi"; 
        $username = "root";
        $password = "";

        $conn = new mysqli($server, $username, $password, $database);

        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        // Lấy dữ liệu từ bảng Course
        $query = "SELECT * FROM Course";
        $result = $conn->query($query);

        // Hiển thị dữ liệu dưới dạng card
        echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
        if ($result->num_rows > 0) {
            while ($course = $result->fetch_assoc()) {
                echo '<div class="col">
                        <div class="card">
                            <img src="' . htmlspecialchars($course['ImageUrl']) . '" class="card-img-top" alt="' . htmlspecialchars($course['Title']) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($course['Title']) . '</h5>
                                <p class="card-text">' . htmlspecialchars($course['Description']) . '</p>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p>No data found.</p>';
        }
        echo '</div>';

        $conn->close();
        ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
