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

## ✨ Principios de Diseño Aplicados

### 🧱 SOLID

| Principio | Aplicación en el proyecto |
|-----------|--------------------------|
| **S**ingle Responsibility | Cada clase tiene una única responsabilidad: Controladores (HTTP), Repositorios (persistencia), Servicios (lógica de negocio), DTOs (transferencia de datos) |
| **O**pen/Closed | Las interfaces (`UserRepositoryInterface`, `GiphyServiceInterface`) permiten extender funcionalidad sin modificar el código existente |
| **L**iskov Substitution | Las implementaciones concretas (`EloquentUserRepository`) pueden sustituir a sus interfaces sin alterar el comportamiento |
| **I**nterface Segregation | Interfaces específicas y pequeñas (`UserRepositoryInterface`, `FavoriteRepositoryInterface`) en lugar de una interfaz general |
| **D**ependency Inversion | Dependemos de abstracciones en los controladores, no de implementaciones concretas (inyección de dependencias) |

### 🔁 DRY (Don't Repeat Yourself)

| Práctica | Implementación |
|----------|----------------|
| **Servicio centralizado** | `GiphyService` encapsula toda la comunicación con la API externa, reutilizado por múltiples controladores |
| **Trait de respuestas** | `ApiResponseTrait` unifica el formato de todas las respuestas JSON |
| **DTOs reutilizables** | `LoginRequestDTO`, `SearchGifRequestDTO` evitan duplicar lógica de transferencia de datos |
| **Repositorios** | La lógica de acceso a datos está encapsulada en repositorios, no repetida en controladores |

### 💬 Tell Don't Ask

| Antes (❌ Mal) | Después (✅ Bien) |
|----------------|-------------------|
| Preguntar al repositorio cómo verificar y luego decidir | Decirle al repositorio que verifique (`$repository->exists()`) |
| Consultar el estado de un objeto y tomar decisiones externas | El objeto encapsula su comportamiento y toma decisiones internas |

**Ejemplo concreto:**
```php
// ❌ Preguntamos (Ask)
$existing = Favorite::where('user_id', $userId)->where('gif_id', $gifId)->first();
if ($existing) { /* decidir */ }

// ✅ Decimos (Tell)
if ($this->favoriteRepository->exists($userId, $gifId)) {
    return $this->errorResponse('Ya existe');
}

## 🔧 Instalación

### 📦 Requisitos previos
- PHP 8.3 o superior
- Composer
- MySQL/MariaDB
- Docker (opcional)

### 🚀 Instalación local (XAMPP)

bash
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