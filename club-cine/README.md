# 🎬 CineClub App

Una aplicación web desarrollada con **Symfony 7** para digitalizar y mejorar la experiencia del cine club creado por mi pareja y sus compañeros de trabajo. Cada semana el grupo elige una película, la ve, y comparte sus valoraciones y comentarios. Esta app reemplaza el uso de hojas de Excel por una interfaz moderna, accesible y colaborativa.

---

## 🚀 Objetivo

Facilitar la participación en el cine club mediante una plataforma web que permita:

- Registrar usuarios y gestionar sesiones.
- Crear grupos de amigos para compartir películas y valoraciones.
- Puntuar películas en distintos aspectos (guion, dirección, actuación, etc.).
- Escribir comentarios personales sobre cada película.
- Visualizar estadísticas y rankings semanales.
- Fomentar el debate y la interacción entre los miembros.

---

## 🧩 Arquitectura Modular

El proyecto está organizado en módulos independientes dentro de `src/Module`, cada uno con su propio README y responsabilidades bien definidas:
- 📄 [README del módulo Auth](src/Module/Auth/README.md)
---

## 🛠️ Tecnologías utilizadas

- **Backend:** Symfony 7 (PHP 8.2)
- **Frontend:** Twig + Bootstrap 5
- **Base de datos:** MySQL
- **Autenticación:** JWT + cookies
- **Control de versiones:** Git + GitLab

---

## 📦 Estado actual del proyecto

- ✅ Registro de usuarios
- ✅ Inicio de sesión
- ✅ Creación de grupos de amigos
- 🔜 Sistema de puntuación por aspectos
- 🔜 Comentarios por película
- 🔜 Panel de administración
- 🔜 Visualización de rankings y estadísticas

---

## 📚 Instalación

```bash
git clone https://github.com/tu-usuario/cineclub-app.git
cd cineclub-app
make install  # o ./scripts/setup.sh si usas un script personalizado
