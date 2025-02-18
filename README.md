# WsSymfony

## Pasos para crear el proyecto
- Primero necesitamos tener instalado Xamp.
- Segundo instalar composer desde la página: https://getcomposer.org/doc/00-intro.md

- Una vez instalado composer, podemos insatalar symfony cli, para ello:
### Instalar SymfonyCli
- Si no tenemos instalado Scoop, necesitamos ejecutar el comando "Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser", después necesitamos lanzar el comando "Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression". Scoop se instala en la ruta en mi caso: "C:\Users\Usuario\scoop"
- Para instalar SymfonyCli lanzamos el comando "scoop install symfony-cli"
- Una vez instalado podemos comprobar con symfony -v. En el caso que no nos reconozca el comando symfony hay que añadirlo a las variables del sistema Path. la ruta en mi caso es "C:\Users\Usuario\scoop\apps\symfony-cli\current".
- Para crear el proyecto nuevo podemos ejecutar el comando: symfony new --webapp my_project el cual crea un proyecto skeleton, o lanzar symfony new my_project el cual crea un proyecto con lo básico.
- para lanzar el proyecto ejecutamos el comando "symfony serve"
### Si no queremos instalar SymfonyCli
- Una vez instalado composer, vamos a la carpeta de nuestro servidor apache, en mi caso uso Xamp, así que en la carpeta htcdocs, abrimos una terminal y escribimos "composer create-project symfony/website-skeleton nombreCarpetaProyecto".
- Una vez instalado todo, necesitamos instalar el componente del servidor apache para poder ejecutar el proyecto, asi que usamos el comando dentro de la carpeta del proyecto "composer require symfony/apache-pack".
- Para comprobar que está todo correcto entramos en mi caso: "http://localhost/WsSymfony/curso-symfony/public/"  y deberíamos de ver la versión de symfony y algunos datos más.

## Crear un proyecto:
### Primeros - Pasos:
- Si estás construyendo un aplicación web tradicional: 
    ```sh
    symfony new --webapp my_project
    ```
- Si estás construyendo un microservicio, aplicación de consola o API: 
    ```sh
    symfony new my_project
    ```
- Si hemos clonado un proyecto existente, antes de levantar el servidor conviene asegurarnos de tener todas las dependencias instaladas con el comando: 
    ```sh
    composer install
    ```
- Una vez creado el proyecto levantamos el servidor con el comando:
    ```sh
     symfony server:start
    ```
- Una vez levantado accedemos en el navegador a la url: http://127.0.0.1:8000/