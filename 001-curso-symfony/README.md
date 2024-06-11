## Proyecto empezado
- Si nos pasan un proyecto, o tenemos que clonar un proyecto, debemos ejecutar el comando "composer install" ya que este comando nos instalará todas las dependencias o bundles que necesita el proyecto para funcionar en el estado en el que se encuentra (se regenera la carpete vendor).

# Crear BBDD
- Configurar el archivo .env con el nombre que queremos darle a la BBDD el usuario y la contraseña.
- Para crear una BBDD podemos ejecutar el comando "php/bin/console" y nos dará un listado de los comandos admitidos, si los exploramos podemos ver en la parte de Doctrine que existe uno que es "doctrine:database:create", eso lo que hará es ir al archivo .env y ver en la parte de BBDD cual es el nombre que le hemos dado previamente, el nombre de usuario y la contraseña y nos creará la BBDD

# Crear entidades
- Hay una herramienta que se llama maker bundle, que nos permite crear entidades, controladores...con el comando "php bin/console make:entity".
- Para crear la entidad usuario lanzamos el comando "php bin/console make:user"
- Si queremos editar una entidad porque necesitamos añadir más atributos usamos el comando "php bin/console make:entity", introducimos en primer lugar el nombre de la entidad existente y añadimos los nuevos atributos.

## Relaciones entre entidades
- Primeramente editamos la entidad por ejemplo User con el comando "php bin/console make:user", le añadimos un nuevo campo, por ejemplo post, le decimos que va a ser de tipo OneToMany y que estará relacionada con la entidad Post.
- El siguiente paso, pregunta que como quieres que se llame la relación dentro e Post y le decimos user y si puede ser nulo.
- Por último nos pregunta que hago cuando no exista el usuario (se elimine) si elimina o no también los post.
- Para pasar las entidades a la BBDD lo hacemos con el comando "php bin/console doctrine:schema:update --force".

## Para crear un Controlador
- Lanzamos el comando "php bin/console make:controller" y además del controlador, se creará en templates una plantilla (vista) para ese controlador.

## Métodos mágicos de Symfony
- Symfony posee unos métodos que se denominan mágicos, para consultas a una base de datos por ejemplo.
- Para hacer uso de ellos podemos directamente en el controlador: creando una variable privada con nombre por ejemplo $em (de Entity Manager ya que será la clase a usar) habría que importar dicha clase de 'use Doctrine\ORM\EntityManagerInterface;' y creando un constructor:
- Ej: 'public function __construc(EntityManagerInterface $em){
            $this->em = $em;
        }'
- Y por último en un método para buscar un post podemos hacer: $post = $this->em->getRepository(Post::class)->find($id); Para realizar la búsqueda de un Post.

## Repositorios
- Si no deseamos usar los métodos mágicos, podemos usar el repositorio para crear nuestros métodos personalizados usando Doctrine Query Languaje.
- Nuestro método para buscar un Post por su id sería por ejemplo:  
- public function findPost($id){
        return $this->getEntityManager()
            ->createQuery(
                'SELECT post.id, post.tittle, post.type
                FROM App\Entity\Post post
                WHERE post.id = :id'
            )
            ->setParameter('id', $id)
            // ->getResult();
            ->getSingleResult();
    }

## Formularios
- Para crear un formulario lanzamos desde la terminal el comando 'php bin/console make:form'
- A la hora de crear el formulario nos preguntará si quieremos relacionar dicho formulario con alguna entidad.
- Podemos personalizar los campos del formulario siguiente la documentación (https://symfony.com/doc/current/reference/forms/types.html)