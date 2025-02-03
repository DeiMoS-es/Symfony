# Configuraciones / instalaciones
- Hemos comenzado instalando:
  - Doctrine como ORM (Object-Relational Mapper), para interactuar con bases de datos con el siguiente comando:
  
    ```sh
    composer require symfony/orm-pack
    ```

  - También es recomendable instalar la herramienta maker para generar entidades y otros archivos de forma automática con el siguiente comando:
  
    ```sh
    composer require symfony/maker-bundle --dev
    ```

- Una vez realizados los pasos anteriores, configuramos la conexión a nuestra bbdd en el archivo `.env`:
  
    ```dotenv
    DATABASE_URL="mysql://root:root@127.0.0.1:3306/movie_rating?serverVersion=8.0"
    ```

- Creamos la bbdd con el siguiente comando:
  
    ```sh
    php bin/console doctrine:database:create
    ```
