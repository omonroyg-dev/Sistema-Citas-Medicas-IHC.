# 🏥 Sistema Web de Citas Médicas Portable - Clínica Monroy

Sistema web desarrollado para la **Clínica de Especialidades Monroy**, diseñado para operar de forma portable en entornos locales. Incluye un portal público interactivo para pacientes y una consola administrativa privada para la gestión del personal médico.

---

## 🚀 Características Principales

- **Portal Público (Pacientes):**
  - Agendamiento en línea de citas médicas.
  - Búsqueda y verificación de estado de citas ingresando el número de cédula.
  - Cancelación directa de citas por parte del paciente.
- **Panel Administrativo (Staff Médico):**
  - Autenticación segura de usuarios.
  - Dashboard con métricas clave en tiempo real (KPIs de pacientes y citas del día).
  - Control modular de Pacientes, Doctores, Citas, Farmacia, Laboratorio y Caja.
  - Opción para imprimir el listado diario de atención médica.
- **Autonomía y Portabilidad:**
  - Despliegue automático en puerto local mediante script por lotes `.bat` sin necesidad de configurar servidores web avanzados.
  - Conexión segura mediante PDO con consultas preparadas contra inyecciones SQL.

---

## 🛠️ Tecnologías Utilizadas

- **Frontend:** HTML5, CSS3, JavaScript (ES6), Bootstrap 5.3, FontAwesome 6.4, SweetAlert2.
- **Backend:** PHP 8.x (Servidor ejecutable integrado CLI).
- **Base de Datos:** MySQL / MariaDB (Motor InnoDB, `utf8mb4_unicode_ci`).
- **Despliegue Local:** Script Batch (`.bat`).

---

## 📂 Estructura del Proyecto

```text
Sistema_Medico_Portable/
│
├── config/
│   └── db.php                  # Configuración de conexión PDO
├── database/
│   └── schema.sql              # Script SQL de creación e inicialización de la BD
├── php/
│   ├── login_process.php       # Procesador de autenticación
│   ├── guardar_cita.php        # Procesador de registro de citas y pacientes
│   └── cancelar_cita.php       # Procesador de anulación de citas
├── EJECUTAR_SISTEMA.bat        # Lanzador automático del servidor web
├── fondo.jpg                   # Imagen de fondo institucional
├── gato_medico.gif             # Mascota animada del sistema
├── index.php                   # Portal de acceso médico (Login)
├── cita_online.php             # Portal público de pacientes
├── dashboard.php               # Consola central administrativa
├── pacientes.php               # Módulo de lista de pacientes
├── doctores.php                # Módulo del staff médico
├── citas.php                   # Control general de citas
└── README.md                   # Documentación del repositorio