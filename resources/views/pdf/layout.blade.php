<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead th {
            background-color: #dee2e6;
        }
        th, td {
            border: 1px solid #cfd2dc;
            padding: 4px;
        }
    </style>
</head>
<body>
    <!-- HEADER reutilizable -->
    <htmlpageheader name="main-header">
        <div style="text-align: center; font-weight: bold; font-size: 13px;">
            <img src="{{ public_path('images/claqueta.png') }}"
                 width="30"
                 style="vertical-align: middle;">
            
            <span style="vertical-align: middle;">
                @yield('title', 'Documento')
            </span>
        </div>
    </htmlpageheader>
    <sethtmlpageheader name="main-header" value="on" show-this-page="1" />
    <!-- FOOTER reutilizable -->
    <htmlpagefooter name="main-footer">
        <div style="border-top: 1px solid #999; font-size: 10px; text-align: right;">
            Página {PAGENO} / {nbpg}
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="main-footer" value="on" />
    <!-- CONTENIDO -->
    <main>
        @yield('content')
    </main>
</body>
</html>