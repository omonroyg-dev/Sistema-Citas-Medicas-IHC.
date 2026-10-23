<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Monroy | Acceso Médico</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            /* Carga tu foto mejorada 'fondo.jpg' con capa translúcida para legibilidad */
            background: linear-gradient(rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.75)), url('fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 15px;
        }

        .main-card {
            width: 100%;
            max-width: 880px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .banner-left {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }

        .clinic-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-weight: 600;
            font-size: 12px;
            padding: 6px 16px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .cat-mascot {
            width: 150px;
            height: 150px;
            object-fit: contain;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.3));
            animation: bounce 3s infinite ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .form-right {
            padding: 40px;
        }

        .btn-custom-blue {
            background-color: #0284c7;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-custom-blue:hover {
            background-color: #0369a1;
            color: white;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        }

        .btn-custom-green {
            background-color: #10b981;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-custom-green:hover {
            background-color: #059669;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
    </style>
</head>
<body>

    <div class="main-card">
        <div class="row g-0">
            <!-- Columna Izquierda: Banner con Gato Médico -->
            <div class="col-md-5 banner-left">
                <div>
                    <div class="clinic-badge mb-3">
                        <i class="fa-solid fa-hospital me-1"></i> Staff Médico Monroy
                    </div>
                    <h3 class="fw-bold mb-2">Clínica de Especialidades Monroy</h3>
                    <p class="small opacity-85">Comprometidos con tu salud. Consulta Externa, Pediatría & Urgencias.</p>
                </div>

                <!-- Mascota Gato Médico -->
                <div class="my-3">
                    <img src="gato_medico.gif" alt="Gato Médico Monroy" class="cat-mascot" 
                         onerror="this.src='https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExdW43MWFsaWs1eTJ1cWgxd2t6d3VrcnQ5dzJxcXZ5bXpjaWVmd3M0eCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/3oKIPnAiaMCws8nOsE/giphy.gif';">
                </div>

                <div class="w-100">
                    <div class="p-2 rounded bg-white bg-opacity-10 border border-white border-opacity-25">
                        <small class="d-block fw-semibold"><i class="fa-solid fa-shield-halved me-1"></i> Atención de Excelencia</small>
                        <small class="opacity-75" style="font-size: 11px;">Consulta Externa & Urgencias 24/7</small>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario de Login -->
            <div class="col-md-7 form-right d-flex flex-column justify-content-center">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-dark mb-1">Acceso Médico</h4>
                    <p class="text-muted small">Ingrese sus credenciales de especialista</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger text-center p-2 mb-3 small rounded-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Credenciales de acceso incorrectas.
                    </div>
                <?php endif; ?>

                <form action="php/login_process.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold mb-1">Usuario Médico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user-doctor"></i></span>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej: admin" required autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold mb-1">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom-blue w-100 mb-3 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> INGRESAR AL SISTEMA
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-2 fw-semibold">Opciones Públicas</p>
                    <a href="cita_online.php" class="btn btn-custom-green w-100 shadow-sm text-decoration-none">
                        <i class="fa-solid fa-globe me-2"></i> Portal de Citas en Línea (Pacientes)
                    </a>
                </div>

                <div class="text-center mt-4">
                    <small class="text-muted" style="font-size: 11px;">
                        Credenciales: <code class="text-primary">admin / admin123</code>
                    </small>
                </div>
            </div>
        </div>
    </div>

</body>
</html>