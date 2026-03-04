# 🎯 GIPHY Challenge API

API REST desarrollada en Laravel 11 que consume la API de GIPHY, permitiendo búsqueda de GIFs, gestión de favoritos y autenticación de usuarios. Implementa **arquitectura hexagonal**, **principios SOLID** y **patrón Repository**.

## 📋 Requisitos cumplidos

| Requerimiento | Implementación |
|---------------|----------------|
| ✅ PHP 8.3+ | PHP 8.3.29 |
| ✅ Laravel 11+ | Laravel 11.48.0 |
| ✅ MySQL/MariaDB | MySQL 8.0 (XAMPP) |
| ✅ UML | Diagramas en `/docs/diagrams` |
| ✅ Docker | Archivos Docker incluidos |
| ✅ Autenticación | OAuth2 (token con expiración 30 min) |
| ✅ Logging | Registro de todas las peticiones |

## 🏗️ Arquitectura Hexagonal
app/
├── Contracts/ # Puertos (interfaces)
│ ├── Repositories/ # - UserRepositoryInterface
│ │ # - FavoriteRepositoryInterface
│ └── Services/ # - GiphyServiceInterface
├── DTOs/ # Data Transfer Objects
├── Http/
│ ├── Controllers/ # Adaptadores HTTP
│ └── Middleware/ # - AuthenticateWithToken
├── Models/ # Modelos Eloquent
├── Repositories/ # Implementaciones concretas
├── Services/ # Casos de uso
└── Traits/ # - ApiResponseTrait

text

## 🔧 Instalación

### 📦 Requisitos previos
- PHP 8.3 o superior
- Composer
- MySQL/MariaDB
- Docker (opcional)

### 🚀 Instalación local (XAMPP)

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/giphy-challenge.git
cd giphy-challenge

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus datos de BD

# 4. Generar key
php artisan key:generate

# 5. Ejecutar migraciones
php artisan migrate

# 6. Iniciar servidor
php artisan serve