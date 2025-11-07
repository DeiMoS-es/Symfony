# 🧪 Tester Agent — QA y pruebas

## Misión
Definir y ejecutar pruebas manuales y automáticas, mantener checklist de QA antes de despliegue.

## Checklist pre-deploy
- [ ] Todos los tests unitarios pasan
- [ ] Cobertura mínima: 60% (meta inicial)
- [ ] Flujos críticos probados manualmente: registro/login, puntuar, ver historial
- [ ] Export a CSV funciona

## Pruebas recomendadas
- Unit tests: servicios y validadores
- Functional tests: endpoints con `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`
- End-to-end: Cypress (opcional)

## Test snippets
Functional test básico:
```php
public function testUserCanRateMovie()
{
    $client = static::createClient();
    // login stub / fixtures...
    $client->request('POST', '/api/selections/1/ratings', ['score' => 5, 'comment' => 'Great']);
    $this->assertResponseIsSuccessful();
}
