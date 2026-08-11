-- Migracion para instalaciones existentes de sams_kiosko.
-- Agrega roles de usuario, bloqueo por intentos fallidos de login y bitacora de actividad.
-- Ejecutar una sola vez contra la base ya creada (local y en el hosting).

ALTER TABLE users
    ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'cajero' AFTER password,
    ADD COLUMN failed_attempts INT NOT NULL DEFAULT 0 AFTER role,
    ADD COLUMN locked_until DATETIME NULL AFTER failed_attempts;

UPDATE users SET role = 'admin' WHERE username = 'admin';

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL DEFAULT '',
    action VARCHAR(50) NOT NULL,
    entity VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    details VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
