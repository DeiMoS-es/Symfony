# Configuraciones / Instalaciones

## Instalación de Dependencias

- **Doctrine como ORM**: Para interactuar con bases de datos utilizando el siguiente comando:
    ```sh
    composer require symfony/orm-pack
    ```

- **Herramienta Maker**: Recomendada para generar entidades y otros archivos de forma automática:
    ```sh
    composer require symfony/maker-bundle --dev
    ```

- **HttpClient**: Para realizar peticiones HTTP de manera sencilla:
    ```sh
    composer require symfony/http-client
    ```

## Configuración de la Base de Datos

- Configuramos la conexión a nuestra base de datos en el archivo `.env`:
    ```dotenv
    DATABASE_URL="mysql://root:root@127.0.0.1:3306/movie_rating?serverVersion=8.0"
    ```

- Creamos la base de datos con el siguiente comando:
    ```sh
    php bin/console doctrine:database:create
    ```

## Estructura del Proyecto

El proyecto está organizado en varios paquetes para mantener una estructura modular y bien definida:

```plaintext
src/
│
├── Tmdb/
│   ├── Controller/TmdbController.php
│   ├── Service/TmdbService.php
│   ├── DTO/MovieDTO.php
│
├── Movie/
│   ├── Controller/MovieController.php
│   ├── Entity/Movie.php
│   ├── Repository/MovieRepository.php
│   ├── Service/MovieService.php
│
├── User/
│   ├── Controller/UserController.php
│   ├── Entity/User.php
│   ├── Repository/UserRepository.php
│   ├── Service/AuthService.php
│   ├── Security/UserAuthenticator.php
│
├── Security/
│   ├── Voter/MovieVoter.php
