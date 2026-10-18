<?php
// db_config.php - Conexión a SQLite y actualización automática de columnas
try {
    $db = new PDO('sqlite:' . __DIR__ . '/citas_medicas.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tabla de Usuarios
    $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        nombre TEXT NOT NULL,
        rol TEXT DEFAULT 'paciente',
        direccion TEXT,
        alergias TEXT,
        tipo_sangre TEXT
    )");

    // Asegurar columnas de la tabla usuarios
    $colsUser = array_column($db->query("PRAGMA table_info(usuarios)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('direccion', $colsUser)) $db->exec("ALTER TABLE usuarios ADD COLUMN direccion TEXT");
    if (!in_array('alergias', $colsUser)) $db->exec("ALTER TABLE usuarios ADD COLUMN alergias TEXT");
    if (!in_array('tipo_sangre', $colsUser)) $db->exec("ALTER TABLE usuarios ADD COLUMN tipo_sangre TEXT");

    // Tabla de Citas Médicas
    $db->exec("CREATE TABLE IF NOT EXISTS citas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        paciente_nombre TEXT NOT NULL,
        fecha TEXT NOT NULL,
        hora TEXT NOT NULL,
        especialidad TEXT NOT NULL,
        doctor TEXT DEFAULT 'Doctor de Turno',
        estado TEXT DEFAULT 'Pendiente',
        usuario_id INTEGER,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");

    // Asegurar columnas de la tabla citas si fue creada previamente
    $colsCitas = array_column($db->query("PRAGMA table_info(citas)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('doctor', $colsCitas)) {
        $db->exec("ALTER TABLE citas ADD COLUMN doctor TEXT DEFAULT 'Doctor de Turno'");
    }
    if (!in_array('estado', $colsCitas)) {
        $db->exec("ALTER TABLE citas ADD COLUMN estado TEXT DEFAULT 'Pendiente'");
    }
    if (!in_array('usuario_id', $colsCitas)) {
        $db->exec("ALTER TABLE citas ADD COLUMN usuario_id INTEGER");
    }

    // Crear usuario admin por defecto si no existe (Usuario: admin | Clave: admin123)
    $checkAdmin = $db->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin'")->fetchColumn();
    if ($checkAdmin == 0) {
        $stmt = $db->prepare("INSERT INTO usuarios (usuario, password, nombre, rol) VALUES ('admin', :pass, 'Administrador General', 'admin')");
        $stmt->bindValue(':pass', password_hash('admin123', PASSWORD_DEFAULT));
        $stmt->execute();
    }

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>