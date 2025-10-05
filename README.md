# Sistema de Gestión de Tareas

Una aplicación web moderna para la gestión eficiente de tareas, construida con Laravel y Vue.js. Permite a los usuarios crear, asignar, organizar y rastrear tareas utilizando un sistema de etiquetado con keywords.

## ¿Qué hace el proyecto?

Este proyecto implementa un **Sistema de Gestión de Tareas (Task Management System)** que proporciona las siguientes funcionalidades principales:

- **Autenticación de usuarios**: Registro, login y logout con soporte para autenticación de dos factores.
- **Gestión de tareas**: Crear, editar, eliminar y listar tareas con atributos como título, descripción, estado (pendiente/completada), fecha límite y prioridad.
- **Asignación de tareas**: Asignar tareas a múltiples usuarios para facilitar el trabajo en equipo.
- **Sistema de keywords**: Etiquetar tareas con palabras clave para una mejor organización y búsqueda.
- **Interfaz web moderna**: Aplicación de página única (SPA) con Vue.js para una experiencia de usuario fluida.
- **API RESTful**: Endpoints completos para integración con otras aplicaciones o clientes móviles.

### Funcionalidades destacadas

- **Dashboard de tareas**: Vista principal con lista de tareas y filtros por estado, asignado, etc.
- **Gestión de keywords**: CRUD completo para keywords con asociación/desasociación a tareas.
- **Toggle de estado**: Cambiar rápidamente el estado de completado de una tarea.
- **Relaciones many-to-many**: Soporte para asignar múltiples usuarios a una tarea y múltiples keywords a una tarea.

## ¿Cómo se hace?

El proyecto utiliza una **arquitectura moderna full-stack** con separación clara entre backend y frontend:

### Backend (Laravel)
- **Framework**: Laravel 12 con PHP 8.2+
- **Autenticación**: Laravel Fortify para gestión de usuarios y Laravel Sanctum para tokens API
- **Base de datos**: Eloquent ORM con soporte para SQLite/MySQL/PostgreSQL
- **API**: Endpoints RESTful protegidos con middleware de autenticación
- **Relaciones**: Modelos con relaciones many-to-many (tareas-usuarios, tareas-keywords)

### Frontend (Vue.js)
- **Framework**: Vue 3 con Composition API
- **Enrutamiento**: Inertia.js para navegación SPA sin recargas de página
- **Estilos**: Tailwind CSS para diseño responsivo y moderno
- **Componentes**: Sistema de componentes reutilizables con shadcn-vue
- **Build**: Vite para desarrollo rápido y optimización de producción

### Arquitectura general
```
Cliente (Browser) <-> Inertia.js <-> Laravel (Backend) <-> Base de datos
                      |
                      v
                Vue.js (Frontend)
```

## ¿Por qué se hace?

Este proyecto se desarrolla para abordar la necesidad de herramientas eficientes de gestión de tareas en entornos colaborativos. Los objetivos principales son:

- **Mejorar la productividad**: Proporcionar una interfaz intuitiva para organizar y priorizar tareas.
- **Facilitar la colaboración**: Permitir asignar tareas a miembros del equipo y rastrear el progreso.
- **Organización flexible**: El sistema de keywords permite categorizar tareas de manera personalizable.
- **Accesibilidad**: API RESTful permite integración con otras herramientas o desarrollo de apps móviles.
- **Experiencia moderna**: SPA con Vue.js ofrece una experiencia de usuario fluida y responsiva.

## Inicio Rápido

Sigue estos pasos para configurar y ejecutar el proyecto en tu entorno local:

### 1. Clonar el repositorio
```bash
git clone https://github.com/miusarname2/pruebaTheCloud
cd pruebaTheCloud
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Instalar dependencias de JavaScript
```bash
npm install
```

### 4. Configurar el entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar la base de datos
Por defecto, el proyecto usa SQLite. El archivo se crea automáticamente, pero puedes configurar otras bases de datos en `.env`.

```bash
# Ejecutar migraciones
php artisan migrate
```

### 6. Ejecutar la aplicación
```bash
# Opción 1: Ejecutar todo con un comando (recomendado para desarrollo)
composer run dev

# Opción 2: Ejecutar componentes por separado
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Servidor de desarrollo de Vue
npm run dev

# Terminal 3: (opcional) Queue worker para trabajos en segundo plano
php artisan queue:listen
```

### 7. Acceder a la aplicación
- **Aplicación web**: http://localhost:8000
- **API**: http://localhost:8000/api

### Ejemplo de uso básico
1. Regístrate en la aplicación web
2. Ve al dashboard y crea tu primera tarea
3. Asigna keywords para organizar mejor
4. Marca tareas como completadas usando el toggle

## Estructura del Proyecto

```
pruebaTheCloud/
├── app/                          # Código de aplicación Laravel
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── api/             # Controladores API
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── KeywordController.php
│   │   │   │   └── TaskController.php
│   │   │   └── Settings/        # Controladores de configuración
│   │   ├── Middleware/          # Middleware personalizado
│   │   └── Requests/            # Validación de requests
│   ├── Models/                  # Modelos Eloquent
│   │   ├── Keywords.php
│   │   ├── Task.php
│   │   └── User.php
│   └── Providers/               # Service providers
├── bootstrap/                   # Inicialización de Laravel
├── config/                      # Archivos de configuración
├── database/                    # Migraciones y seeders
├── public/                      # Assets públicos
├── resources/                   # Recursos de frontend
│   ├── css/
│   │   └── app.css             # Estilos Tailwind
│   ├── js/                     # Código Vue.js
│   │   ├── components/         # Componentes Vue
│   │   │   ├── ui/            # Componentes de UI reutilizables
│   │   │   └── ...            # Componentes específicos
│   │   ├── composables/       # Composables Vue
│   │   ├── layouts/           # Layouts de página
│   │   ├── lib/               # Utilidades
│   │   ├── pages/             # Páginas Vue
│   │   └── types/             # Definiciones TypeScript
│   └── views/                  # Vistas Blade (mínimas)
├── routes/                      # Definición de rutas
│   ├── api.php                 # Rutas API
│   ├── auth.php                # Rutas de autenticación
│   ├── settings.php            # Rutas de configuración
│   └── web.php                 # Rutas web
├── storage/                     # Archivos temporales y logs
├── tests/                       # Tests automatizados
├── .env.example                 # Plantilla de configuración
├── artisan                      # CLI de Laravel
├── composer.json                # Dependencias PHP
├── package.json                 # Dependencias JavaScript
├── vite.config.js               # Configuración de Vite
└── README.md                    # Esta documentación
```

## Requerimientos del Sistema

### Requerimientos mínimos
- **PHP**: >= 8.3
- **Composer**: Última versión estable
- **Node.js**: >= 23.0
- **npm**: >= 9.0

### Base de datos
- **SQLite** (por defecto, no requiere configuración adicional)
- **MySQL** >= 5.7
- **PostgreSQL** >= 9.6

### Navegadores soportados
- Chrome >= 90
- Firefox >= 88
- Safari >= 14
- Edge >= 90

### Dependencias principales
- **Laravel Framework** 12.x
- **Vue.js** 3.x
- **Inertia.js** 2.x
- **Tailwind CSS** 4.x
- **Laravel Sanctum** 4.x
- **Laravel Fortify** 1.x

## API Reference

La aplicación proporciona una API RESTful completa. Todos los endpoints requieren autenticación con token Bearer.

### Autenticación
```http
POST /api/login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "password",
  "device_name": "Mi Dispositivo"
}
```

### Tareas
```http
# Listar tareas
GET /api/tasks

# Crear tarea
POST /api/tasks
{
  "title": "Nueva tarea",
  "description": "Descripción de la tarea",
  "due_date": "2024-12-31",
  "priority": "alta",
  "assignees": [1, 2],
  "keywords": [1, 3]
}

# Actualizar tarea
PUT /api/tasks/{id}

# Eliminar tarea
DELETE /api/tasks/{id}

# Toggle estado
PATCH /api/tasks/{id}/toggle
```

### Keywords
```http
# Listar keywords
GET /api/keywords

# Crear keyword
POST /api/keywords
{
  "name": "urgente"
}

# Asociar keywords a tarea
POST /api/tasks/{taskId}/keywords
{
  "keyword_ids": [1, 2],
  "names": ["nuevo", "importante"]
}
```

## Desarrollo

### Comandos útiles
```bash
# Ejecutar tests
composer run test

# Formatear código
npm run format

# Linting
npm run lint

# Build para producción
npm run build
```

### Contribución
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para soporte técnico o preguntas:
- Abre un issue en el repositorio
- Revisa la documentación de Laravel e Inertia.js
- Consulta los logs de la aplicación en `storage/logs/`

---

¡Gracias por usar nuestro Sistema de Gestión de Tareas! Esperamos que te ayude a organizar mejor tu trabajo y el de tu equipo.
