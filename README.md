# Raspberry Web Infra - Base Estructural

Self-hosted, low-cost, and energy-efficient web infrastructure built on Raspberry Pi using Apache and Cloudflare Tunnel. Ideal for startups and personal projects seeking privacy, stability, and full control without relying on third-party servers.

## Objetivo

- Tener una estructura clara para sitio web + panel admin + API.
- Mantener archivos basicos, sin datos de negocio.
- Dejar limitaciones y reglas definidas desde el inicio.

## Estructura Inicial

- `index.php` router principal minimo.
- `pages/` vistas publicas base.
- `includes/` layout y helpers.
- `admin/` acceso y panel base.
- `api/appointments/` endpoints placeholder.
- `config/` configuraciones y SQL inicial.
- `assets/css/` y `js/` recursos del frontend.

## Flujo Basico

1. Editar vistas en `pages/`.
2. Agregar logica comun en `includes/functions.php`.
3. Definir conexion real en `config/database.php`.
4. Implementar endpoints reales en `api/appointments/`.

## Estado

- Estructura creada.
- Archivos base creados.
- Sin informacion de negocio cargada.
- Ramas `develop` y `production` listas para flujo seguro.

## Deploy Automatizado

- Webhook listo en `deploy/webhook.php`.
- Script seguro en `deploy/deploy.sh` con backup y rollback.
- Guia de configuracion en `deploy/README.md`.

### Configuracion en GitHub

1. Ir al repositorio: `https://github.com/devcepeda/raspberry_webinfra`.
2. Entrar a `Settings -> Webhooks -> Add webhook`.
3. Configurar:
	- Payload URL: `https://TU_DOMINIO/raspberry_webinfra/deploy/webhook.php`
	- Content type: `application/json`
	- Secret: el mismo `DEPLOY_WEBHOOK_SECRET` de `deploy/.env`
	- Eventos: `Just the push event`
4. Guardar y probar un push a `production`.

### Flujo recomendado de trabajo

1. Crear cambios en `develop`.
2. Validar funcionalidad.
3. Merge de `develop` a `production`.
4. Push a `production` para desplegar automaticamente.

### Estrategia de ramas

- `production`: rama estable para despliegue.
- `develop`: rama de integracion y pruebas.
- Features desde `develop` y merge a `production` al validar.

## Comandos Git Rapidos

```bash
# Trabajar en desarrollo
git checkout develop

# Subir cambios de desarrollo
git add .
git commit -m "feat: cambios en desarrollo"
git push origin develop

# Pasar a produccion
git checkout production
git merge --no-ff develop
git push origin production
```
