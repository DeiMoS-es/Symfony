# 🎬 Aplicación de Gestión de Películas - Backend

## 📌 Visión General

Este proyecto implementa la **parte de servidor** de una aplicación para la gestión de películas. Desarrollado con **PHP 8** y **Symfony 7**, permite a los usuarios:

- **Consultar** películas de cine disponibles.
- **Crear listas personalizadas** de películas que desean ver.
- **Puntuar y reseñar** las películas existentes.

Además, existe un **usuario administrador** encargado de sincronizar la base de datos interna con información obtenida desde **TMDB** u otra API que proporcione datos sobre películas nuevas.

---

## ⚙️ Tecnologías Utilizadas

- **Lenguaje:** PHP 8
- **Framework:** Symfony 7
- **Base de datos:** MySQL 
- **Autenticación:** JWT 
- **API de terceros:** TMDB API / Alternativas
- **Servidor local:** XAMPP (opcional)

---

## 🚀 Instalación y Configuración

Para instalar y configurar el proyecto correctamente, sigue estos pasos:

1️⃣ **Clonar el repositorio**  
El backend del proyecto está alojado en **GitHub**, dentro del repositorio [`DeiMoS-es/Symfony`](https://github.com/DeiMoS-es/Symfony.git), en la carpeta `003-PELICULAS-BACKEND`.  

Ejecuta el siguiente comando para clonar solo esa carpeta:
```bash
git clone --depth 1 --filter=blob:none --sparse git@github.com:DeiMoS-es/Symfony.git
cd Symfony
git sparse-checkout set 003-PELICULAS-BACKEND
cd 003-PELICULAS-BACKEND

