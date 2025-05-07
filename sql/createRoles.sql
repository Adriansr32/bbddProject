-- ------------------------------
-- CREACIÓ DE ROLES
-- ------------------------------


-- Solo lectura
CREATE USER 'lector'@'localhost' IDENTIFIED BY 'lector123';
GRANT SELECT ON digital_library.* TO 'lector'@'localhost';

-- Control total
CREATE USER 'gestor'@'localhost' IDENTIFIED BY 'gestor123';
GRANT ALL PRIVILEGES ON digital_library.* TO 'gestor'@'localhost';
