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

## Si queremos modificar la entidad `Movies/`
1️⃣ **Añadimos el nuevo campo**
   - En este caso se añade el campo $status, los correspondientes getters y setters, y se actualiza la migración.
   ```php
   #[ORM\Column(type: 'boolean')]
    private ?bool $status = true;
   ```
   ```bash
   symfony console make:migration
   symfony console doctrine:migrations:migrate
   ```

---

## 🖥️ Controladores en Symfony

### 📌 ¿Qué es un controlador?
En Symfony, un **controlador** es una clase que gestiona las solicitudes HTTP y devuelve respuestas. Es el puente entre las peticiones del usuario y la lógica de la aplicación.

### ⚡ ¿Cómo funciona?
1. **Recibe una solicitud HTTP**  
   Cuando un usuario accede a una URL, Symfony busca el controlador asociado y ejecuta el método correspondiente.

2. **Procesa la lógica de negocio**  
   Puede llamar a servicios, consultar la base de datos, realizar validaciones, entre otras acciones.

3. **Devuelve una respuesta**  
   El controlador genera una respuesta en formato HTML, JSON, o cualquier otro tipo de contenido.

### 🏗 Ejemplo de un controlador `MovieController`
```php
namespace App\Movies\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route('/', name: 'app_movie_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('movie/index.html.twig');
    }
}
```

## 🎯 Repositorio vs Servicio en Symfony

En nuestra arquitectura, es importante distinguir bien los **responsables** de cada capa:

| Capa             | Qué hace                                                                 | Ejemplos de métodos                   |
|------------------|--------------------------------------------------------------------------|---------------------------------------|
| **Repositorio**  | Accede directamente a la base de datos mediante Doctrine.                | `findByTitle()`, `findMostPopular()`  |
| **Servicio**     | Aplica lógica de negocio, transforma datos o coordina múltiples tareas.  | `searchByTitle()`, `getActiveMovies()`|


### 🧩 Ejemplo de método en el Repositorio

El repositorio se encarga de construir y ejecutar consultas a la base de datos:

```php
// src/Movies/Repository/MovieRepository.php

public function findByTitle(string $title): array
{
    return $this->createQueryBuilder('m')
        ->where('m.title_movie LIKE :title')
        ->setParameter('title', '%' . $title . '%')
        ->orderBy('m.release_date', 'DESC')
        ->getQuery()
        ->getResult();
}
```
### 🧩 Ejemplo de método en el Repositorio
```php
// src/Movies/Service/MovieService.php
public function searchByTitle(string $title): array
{
    return $this->movieRepository->findByTitle($title);
}
```
### 🧩 Resumen
- **Repositorio**: Interactúa directamente con la base de datos, ejecuta consultas y devuelve resultados.“¿Cómo obtengo los datos?”
- **Servicio**: Contiene la lógica de negocio, utiliza repositorios para obtener datos y puede realizar transformaciones o cálculos adicionales.“¿Qué hago con los datos?”
