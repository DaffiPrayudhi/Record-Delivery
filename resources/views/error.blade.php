<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #2c3e50; 
            color: white; 
            font-family: 'Calibri', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            overflow: hidden;
        }

        .error-container {
            background: rgba(38, 38, 39, 0.7);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
        }

        h1 {
            font-size: 5em;
            font-weight: bold;
            color:rgb(255, 255, 255);
            margin-bottom: 20px;
            animation: fadeIn 1s ease-out;
        }

        p {
            font-size: 1.2em;
            margin-bottom: 20px;
            animation: fadeIn 1s ease-out;
        }

        .error-icon {
            font-size: 100px;
            margin-bottom: 20px;
            color: #e74c3c;
            animation: pulse 1.5s infinite;
        }

        .btn-reload {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            font-size: 1.2em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-reload:hover {
            background-color: #2980b9;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Error!</h1>
        <p>Terjadi kesalahan dalam program. Tolong hubungi pihak terkait mengenai error.</p>
    </div>

</body>
</html>
