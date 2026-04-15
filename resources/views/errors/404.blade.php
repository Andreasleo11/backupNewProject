<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Page Not Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Outfit:wght@600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0e14;
            --accent-primary: #6366f1;
            --accent-secondary: #a855f7;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Orbs */
        .orb {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            z-index: -1;
            animation: move 20s infinite alternate;
        }

        .orb-1 {
            background: var(--accent-primary);
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            background: var(--accent-secondary);
            bottom: -150px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes move {
            from {
                transform: translate(0, 0);
            }

            to {
                transform: translate(100px, 150px);
            }
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 3rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 90%;
            width: 500px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 8rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.05em;
        }

        h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        p {
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .home-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            text-decoration: none;
            padding: 1rem 2.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .home-button:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
            letter-spacing: 1px;
        }

        .home-button:active {
            transform: scale(0.95);
        }

        /* Subtle Glow effect on 404 */
        .glow {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8rem;
            font-weight: 700;
            color: var(--accent-primary);
            filter: blur(30px);
            opacity: 0.3;
            z-index: -1;
            animation: pulse 4s infinite alternate;
        }

        @keyframes pulse {
            0% {
                opacity: 0.2;
                transform: translateX(-50%) scale(1);
            }

            100% {
                opacity: 0.4;
                transform: translateX(-50%) scale(1.1);
            }
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 6rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .container {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <div class="glow">404</div>
        <h1>404</h1>
        <h2>Lost in space?</h2>
        <p>The record or page you're searching for is currently unreachable or has been moved to another dimension.</p>
        <a href="/" class="home-button">Return to Base</a>
    </div>
</body>

</html>
