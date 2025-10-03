<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; font-size: 12px;}
        thead tr { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Tanggal Cetak: {{ now()->format('d F Y') }}</p>
    @include($view, ['data' => $data])
</body>
</html>