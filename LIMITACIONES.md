# Limitaciones Iniciales

## Alcance actual

- No hay autentificacion robusta.
- No hay integracion real con base de datos.
- No hay validaciones avanzadas de formularios.
- Endpoints API en modo placeholder.
- Exportacion admin sin logica de negocio.

## Restricciones de seguridad (pendientes)

- Implementar variables de entorno para secretos.
- Usar hash seguro para credenciales (password_hash).
- Agregar control de sesiones y expiracion.
- Aplicar CSRF tokens en formularios.
- Agregar rate limiting para endpoints API.

## Restricciones operativas

- Sitio pensado como base de trabajo, no produccion final.
- Requiere completar configuracion de SSL/tunel segun entorno.
- Requiere pruebas funcionales antes de publicar.
