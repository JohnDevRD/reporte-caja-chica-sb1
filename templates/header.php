<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light min-vh-100 d-flex flex-column">

    <div class="position-fixed top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.75); z-index: 9999;" id="loader">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="mt-2 text-muted small">Cargando datos...</div>
        </div>
    </div>

    <nav class="navbar navbar-dark navbar-gradient shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <div>
                <span class="navbar-brand mb-0 fw-semibold fs-5"><?php echo APP_NAME; ?></span>
                <div class="text-white-50 small"><?php echo APP_SUBTITLE; ?></div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 container-fluid px-4 py-3" style="max-width: 1600px;">
