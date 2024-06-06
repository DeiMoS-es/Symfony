## Proyecto empezado
- Si nos pasan un proyecto, o tenemos que clonar un proyecto, debemos ejecutar el comando "composer install" ya que este comando nos instalará todas las dependencias o bundles que necesita el proyecto para funcionar en el estado en el que se encuentra (se regenera la carpete vendor).

# Crear BBDD
- Configurar el archivo .env con el nombre que queremos darle a la BBDD el usuario y la contraseña.
- Para crear una BBDD podemos ejecutar el comando "php/bin/console" y nos dará un listado de los comandos admitidos, si los exploramos podemos ver en la parte de Doctrine que existe uno que es "doctrine:database:create", eso lo que hará es ir al archivo .env y ver en la parte de BBDD cual es el nombre que le hemos dado previamente, el nombre de usuario y la contraseña y nos creará la BBDD

# Crear entidades
- Hay una herramienta que se llama maker bundle, que nos permite crear entidades, controladores...con el comando "php bin/console make:user"
- Si queremos editar una entidad porque necesitamos añadir más atributos usamos el comando "php bin/console make:entity", introducimos en primer lugar el nombre de la entidad existente y añadimos los nuevos atributos.