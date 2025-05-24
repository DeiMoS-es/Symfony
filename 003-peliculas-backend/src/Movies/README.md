# 🎬 Módulo Movies - Backend

## 📌 Objetivo  
Este módulo es responsable de gestionar las películas dentro de la aplicación. Incluye **entidades, repositorios, servicios y controladores** que permiten **consultar, puntuar y gestionar** películas.

---

## 🏗 Estructura del Módulo  
📂 `src/Movies/`  
│── 📁 `Controller/` *(Gestión de solicitudes HTTP - MovieController.php)*  
│── 📁 `Entity/` *(Modelo de datos de película - Movie.php)*  
│── 📁 `Repository/` *(Consultas a la base de datos - MovieRepository.php)*  
│── 📁 `DTO/` *(Data Transfer Objects - MovieDTO.php)*  
│── 📁 `Service/` *(Lógica de negocio - MovieService.php)*  

---

## 🚀 Pasos para Completar el Módulo Movies  

1️⃣ **Crear la entidad `Movie`**  
   ```bash
   symfony console make:entity Movie
   ```
   Una vez creada, se realiza una migración para actualizar la base de datos:
   ```bash
   symfony console make:migration
   symfony console doctrine:migrations:migrate
   ```
