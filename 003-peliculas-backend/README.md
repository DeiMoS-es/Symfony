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
```

# 🛠 Solución a **MalformedDsnException** en Symfony  
Si al iniciar el servidor (`symfony server:start`) aparece el error:  
MalformedDsnException - HTTP 500 Internal Server Error Malformed parameter "url".

Esto significa que Symfony no puede interpretar correctamente la variable `DATABASE_URL`. 🛑  

---

## ✅ **Causas comunes**  
1. La variable `DATABASE_URL` en `.env` está referenciando variables de entorno que no se han cargado correctamente.  
2. El archivo `.env.local` define las credenciales de la base de datos, pero **Symfony no está asignándolas correctamente a `DATABASE_URL`**.  
3. La caché de Symfony aún guarda una versión incorrecta del entorno.  

---

## 🔄 **Solución paso a paso**  

### 🔹 **Paso 1: Verificar `DATABASE_URL` en Symfony**  
Ejecuta este comando para ver cómo Symfony está interpretando `DATABASE_URL`:  
```bash
symfony console debug:dotenv | grep DATABASE_URL
```
Si ves que `DATABASE_URL` no está correctamente configurada, procede al siguiente paso.
### 🔹 **Paso 2: Verificar el archivo `.env.local`**
### 🔹 **Paso 3: Si las variables de DATABASE_URL no aparecen correctamente, ejecuta:**
```bash
export $(grep -v '^#' .env.local | xargs)
```
Luego vuelve a verificar `DATABASE_URL`:
```bash
symfony console debug:dotenv | grep DATABASE_URL
```
### 🔹 **Paso 4: Limpiar la caché de Symfony**
```bash
symfony console cache:clear
```

