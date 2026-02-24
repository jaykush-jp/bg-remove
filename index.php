<?php
$resultImage = '';
$error = '';

if (isset($_POST['submit']) && isset($_FILES['image'])) {
    $api_key = 'E68FmKZNuwjEGhTNnreATgSM'; 
    $image = $_FILES['image']['tmp_name'];

    // Ensure uploads folder exists
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($image) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.remove.bg/v1.0/removebg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'image_file' => new CURLFile($image),
            'size' => 'auto'
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Api-Key: $api_key"
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = 'Curl error: ' . curl_error($ch);
        } elseif ($http_code != 200) {
            $error = "API Error ($http_code): $response";
        } else {
            $output_file = $uploadDir . uniqid() . '.png';
            if (file_put_contents($output_file, $response)) {
                $resultImage = $output_file;
            } else {
                $error = "Failed to save the output image.";
            }
        }

        curl_close($ch);
    } else {
        $error = 'Please select an image.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Background Remover</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- Reset & Fonts --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* --- Background Gradient & Animation --- */
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* --- Container Styling --- */
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            text-align: center;
            animation: slideUp 1s ease forwards;
        }

        @keyframes slideUp {
            0% {
                transform: translateY(50px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* --- Heading --- */
        h1 {
            font-size: 2.2rem;
            color: #333;
            margin-bottom: 20px;
            position: relative;
        }

        h1::after {
            content: '';
            width: 80px;
            height: 4px;
            background: #764ba2;
            display: block;
            margin: 10px auto 0;
            border-radius: 2px;
            animation: scaleLine 1s ease forwards;
        }

        @keyframes scaleLine {
            0% {
                transform: scaleX(0);
            }

            100% {
                transform: scaleX(1);
            }
        }

        /* --- File Input Styling --- */
        input[type=file] {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        input[type=file]:hover {
            border-color: #764ba2;
            transform: scale(1.05);
        }

        /* --- Submit Button --- */
        input[type=submit] {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        input[type=submit]:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
        }

        /* --- Result Image --- */
        .result img {
            max-width: 100%;
            margin-top: 20px;
            border-radius: 15px;
            border: 5px solid #764ba2;
            animation: fadeIn 1s ease forwards;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* --- Download Button --- */
        .download-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #28a745, #218838);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* --- Error Message --- */
        .error {
            color: #ff4b5c;
            margin-top: 15px;
            font-weight: 600;
        }

        /* --- Responsive --- */
        @media(max-width: 500px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            input[type=submit],
            .download-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Background Remover</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required><br>
            <input type="submit" name="submit" value="Remove Background">
        </form>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($resultImage): ?>
            <div class="result">
                <h2>Result:</h2>
                <img src="<?php echo $resultImage; ?>" alt="Background Removed"><br>
                <a href="<?php echo $resultImage; ?>" class="download-btn" download>Download Image</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>