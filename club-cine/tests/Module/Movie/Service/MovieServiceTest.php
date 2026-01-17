<?php
namespace App\Tests\Module\Movie\Service;

use App\Module\Movie\Service\MovieService;
use App\Module\Movie\DTO\MovieCatalogItemDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MovieServiceTest extends KernelTestCase
{
    private MovieService $movieService;

    protected function setUp(): void
    {
        // Levanta el kernel de Symfony para acceder a los servicios
        self::bootKernel();
        $this->movieService = static::getContainer()->get(MovieService::class);
    }

    public function testSearchMoviesReturnsCorrectDtoStructure(): void
    {
       // 1. Ejecutar la acción (que aún no existe)
        $searchTerm = 'Matrix';
        $results = $this->movieService->getSearchCatalog($searchTerm);

        // 2. Verificaciones (Assertions)
        $this->assertArrayHasKey('items', $results, 'La respuesta debe contener una clave "items"');
        $this->assertIsArray($results['items']);
        
        if (count($results['items']) > 0) {
            $this->assertInstanceOf(
                MovieCatalogItemDTO::class, 
                $results['items'][0], 
                'Cada item debe ser una instancia de MovieCatalogItemDTO'
            );
            // Comprobar que el título contiene el término buscado (insensible a mayúsculas)
            $this->assertStringContainsStringIgnoringCase(
                $searchTerm, 
                $results['items'][0]->title
            );
        }
    }
}

?>