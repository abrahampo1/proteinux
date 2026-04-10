# Proteinux

Proteinux es una aplicación web construida con [Laravel](https://laravel.com) para el análisis y gestión de proteínas. Proporciona herramientas para visualizar, buscar y organizar información relacionada con proteínas de manera eficiente.

## Tecnologías

- **Framework:** Laravel (PHP)
- **Base de datos:** MySQL / PostgreSQL / SQLite
- **Frontend:** Blade / Livewire

## Requisitos previos

- PHP >= 8.2
- Composer
- Node.js y npm
- Base de datos (MySQL, PostgreSQL o SQLite)

## Instalación

1. Clonar el repositorio:

```bash
git clone https://github.com/abrahampo1/proteinux.git
cd proteinux
```

2. Instalar dependencias de PHP:

```bash
composer install
```

3. Instalar dependencias de frontend:

```bash
npm install
```

4. Configurar el entorno:

```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la base de datos en el archivo `.env` y ejecutar las migraciones:

```bash
php artisan migrate
```

## Uso

Iniciar el servidor de desarrollo:

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`.

Compilar assets del frontend en modo desarrollo:

```bash
npm run dev
```

## Tests

```bash
php artisan test
```

## Estructura del proyecto

```
proteinux/
├── app/            # Lógica de la aplicación (Models, Controllers, etc.)
├── config/         # Archivos de configuración
├── database/       # Migraciones, factories y seeders
├── public/         # Punto de entrada público
├── resources/      # Vistas, assets y archivos de idioma
├── routes/         # Definición de rutas
├── storage/        # Archivos generados (logs, cache, etc.)
└── tests/          # Tests automatizados
```

## Contribuir

Las contribuciones son bienvenidas. Por favor, abre un issue o envía un pull request.

## Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).
