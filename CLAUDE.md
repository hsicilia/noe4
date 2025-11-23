# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) al trabajar con código en este repositorio.

## Visión General del Proyecto

**noe4** es una aplicación Symfony 7.3 para la gestión de ejemplares de fauna silvestre y sus capturas. Es una versión modernizada del sistema legacy (noe2) para el seguimiento de animales en un entorno de conservación o zoológico. La aplicación usa PHP 8.2+, MariaDB y Doctrine ORM.

## Modelo de Dominio Principal

La aplicación se centra en tres entidades principales:
- **Ejemplar**: Animales individuales rastreados en el sistema, identificados por microchips, anillas u otros IDs
- **Especie**: Información de especies a las que pertenecen los ejemplares
- **Captura**: Eventos cuando los ejemplares son capturados/observados, con ubicación, fecha y fotos
- **Usuario**: Usuarios con diferentes roles y permisos

Relaciones clave:
- Ejemplar pertenece a una Especie (muchos-a-uno)
- Ejemplar tiene muchas Capturas (uno-a-muchos con borrado en cascada)
- Captura rastrea creación/modificación mediante referencias a Usuario

## Comandos de Desarrollo Comunes

### Servidor de Desarrollo
```bash
# Iniciar desarrollo local con Docker
docker compose up -d

# O usar Symfony CLI
symfony serve
```

### Gestión de Assets
```bash
# Compilar assets y limpiar caché (script personalizado de actualización)
bin/actualiza

# O manualmente:
bin/console asset-map:compile
bin/console cache:clear
```

### Base de Datos
```bash
# Ejecutar migraciones
bin/console doctrine:migrations:migrate

# Crear una nueva migración
bin/console make:migration

# La conexión a la base de datos se configura mediante DATABASE_URL en .env
```

### Calidad de Código
```bash
# Verificar estilo de código con ECS
composer ecs

# Corregir estilo de código automáticamente
composer ecs-fix

# Ejecutar Rector para refactorización automatizada (dry-run)
composer rector

# Aplicar cambios de Rector
composer rector-fix
```

### Testing
```bash
# Ejecutar todos los tests
vendor/bin/phpunit

# Ejecutar con opciones personalizadas
php vendor/bin/phpunit --filter TestName
```

### Comandos Personalizados
```bash
# Analizar estadísticas de especies
bin/console app:analisis-especies
```

## Arquitectura y Estructura

### Controladores
Ubicados en `src/Controller/`, siguiendo nomenclatura basada en recursos:
- `EjemplarController`: CRUD para ejemplares, incluye funcionalidad de búsqueda por mapa
- `CapturaController`: Anidado bajo rutas de ejemplar (`/ejemplar/{id}/captura/...`)
- `InformeController`: Genera informes PDF usando wkhtmltopdf vía KNP Snappy
- `UsuarioController`: Gestión de usuarios (solo admin)
- `SecurityController`: Login/logout

### Formularios
Ubicados en `src/Form/`, usan el componente Form de Symfony:
- Formularios separados para crear vs editar (ej., `EjemplarCrearType` vs `EjemplarEditarType`)
- `BusquedaMapaType`: Formulario complejo para búsqueda de ejemplares basada en mapa con geolocalización

### Repositorios
Métodos de consulta personalizados en `src/Repository/`:
- `EjemplarRepository`: Contiene consultas complejas para estadísticas de ejemplares, datos de capturas y filtrado
- Los repositorios usan Doctrine QueryBuilder para consultas personalizadas

### Seguridad
Jerarquía de roles (de más a menos privilegiado):
- `ROLE_ADMIN`: Acceso completo incluyendo eliminaciones
- `ROLE_OPERADOR_PROPIO`: Puede crear ejemplares
- `ROLE_OPERADOR_EXTERNO`: Puede editar ejemplares y crear capturas
- `ROLE_OPERADOR_EXTERNO_PRUEBAS`: Puede crear/editar capturas
- `ROLE_VISITANTE`: Acceso de solo lectura

Autenticación mediante form login, el proveedor de usuarios carga desde la entidad `Usuario` por el campo usuario.

### Subida de Archivos
Dos directorios de subida configurados en `config/services.yaml`:
- `ejemplares_directory`: `public/uploads/ejemplares/` - Fotos de ejemplares
- `capturas_directory`: `public/uploads/capturas/` - Fotos de capturas

Las imágenes se procesan usando LiipImagineBundle para miniaturas y redimensionamiento.

### Plantillas
Plantillas Twig en `templates/`:
- `base.html.twig`: Layout principal
- `cabecera.html.twig`: Cabecera con navegación
- Los subdirectorios específicos de recursos coinciden con la estructura de controladores

### Ciclo de Vida de Entidades
Las entidades usan `#[ORM\HasLifecycleCallbacks]` con timestamps automáticos para seguimiento de creación y modificación. La entidad `Usuario` se vincula mediante las relaciones `creadoPor` y `modificadoPor`.

## Configuración del Entorno

Variables de entorno clave en `.env`:
- `DATABASE_URL`: Cadena de conexión MariaDB
- `WKHTMLTOPDF_PATH`: Ruta al binario wkhtmltopdf para generación de PDF
- `GOOGLE_MAPS_API_KEY`: Para funcionalidad de mapa en formularios de búsqueda/captura
- `APP_ENV`: dev/prod/test
- `MAILER_DSN`: Configuración de email

## Flujo de Trabajo de Desarrollo

1. **Hacer Cambios**: Después de modificar assets o configuración, ejecutar `bin/actualiza` para compilar assets y limpiar caché
2. **Cambios en Base de Datos**: Crear/modificar entidades, luego ejecutar `bin/console make:migration` seguido de `bin/console doctrine:migrations:migrate`
3. **Estilo de Código**: Ejecutar `composer ecs` antes de hacer commit para asegurar que el código sigue PSR-12 y los estándares del proyecto
4. **Testing**: PHPUnit configurado con ajustes estrictos (falla en deprecation/notice/warning)

## Consideraciones Especiales

### Generación de PDF
- Usa KNP Snappy Bundle con wkhtmltopdf
- La ruta del PDF debe configurarse en la variable de entorno `WKHTMLTOPDF_PATH`
- InformeController genera informes desde plantillas Twig

### Paginación
- KNP Paginator Bundle usado en todas las páginas de listado
- Rango de página por defecto: 20 elementos
- Parámetro de consulta: `?p=` para número de página

### Configuración de Rector y ECS
- Ambos omiten `src/Kernel.php` del análisis
- ECS omite `DeclareStrictTypesFixer` en todo el proyecto
- Rector omite `RenameForeachValueVariableToMatchExprVariableRector`
- Configurado para PHP 8.2 con conjuntos preparados completos
