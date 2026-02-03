<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="{{ basePath }}/dist/styles.css">
</head>
<body>
    <main>
        {{ content }}
    </main>

    <script>
        window.basePath = '{{ basePath }}';
    </script>
    <script src="{{ basePath }}/dist/bundle.js"></script>
</body>
</html>