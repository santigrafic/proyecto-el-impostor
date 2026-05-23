<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: monospace;
            background: #000;
            color: #00ff66;
            font-size: 12px;
        }

        .page-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #000;
        }

        .crt-line {
            height: 2px;
            background: rgba(0, 255, 102, 0.08);
            margin-bottom: 3px;
        }

        .profile-card {
            border: 2px solid #00ff66;
            padding: 20px;
            margin-top: 30px;
            background: #000;
        }

        .title {
            text-align: center;
            font-family: "Press Start 2P", monospace;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 25px;
            border-bottom: 1px solid #00ff66;
            padding-bottom: 10px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 120px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 10px 6px;
            border-bottom: 2px solid #003d1f;
        }

        .label {
            width: 45%;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            text-align: right;
        }

        .stats-section {
            margin-top: 30px;
            border-top: 1px solid #00ff66;
            padding-top: 15px;
        }

        .footer-text {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            font-family: "Press Start 2P", monospace;
            color: #00aa44;
        }

        .terminal-box {
            border: 1px solid #00ff66;
            padding: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <htmlpageheader name="main-header">
        <div style="text-align: center; font-family: 'Press Start 2P', monospace;">
            <h1>EL IMPOSTOR</h1>
        </div>
    </htmlpageheader>

    <sethtmlpageheader name="main-header" value="on" show-this-page="1" />

    <htmlpagefooter name="main-footer">
        <div style="
            border-top: 1px solid #00ff66;
            color: #00ff66;
            font-size: 10px;
            text-align: right;
            padding-top: 5px;
        ">
            Página {PAGENO} / {nbpg}
        </div>
    </htmlpagefooter>

    <sethtmlpagefooter name="main-footer" value="on" />

    <main>
        @yield('content')
    </main>

</body>
</html>