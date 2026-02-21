<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle ?? 'Member Area'); ?> - Cloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YcnS/1WR6zNiflkPFNJgzmQ3lMIYGkCUbiO6" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .form-card {
            max-width: 480px;
            margin: 2rem auto;
        }
        .navbar-brand i { font-size: 1.4rem; }
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
