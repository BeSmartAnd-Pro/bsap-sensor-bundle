# BeSmartAnd.Pro Sensor Bundle

Integracja `besmartand-pro/bsap-sensor-client` z Symfony 7.4 i 8.x, dla PHP 8.4+. Bundle dodaje konfigurację, transport Symfony HttpClient i autowiring. Obsługa API i modele danych znajdują się w osobnej bibliotece klienta.

## Instalacja

```bash
composer require besmartand-pro/bsap-sensor-bundle
```

Zarejestruj bundle w `config/bundles.php`:

```php
return [
    BeSmartAndPro\BsapSensorBundle\BsapSensorBundle::class => ['all' => true],
];
```

Dodaj `config/packages/bsap_sensor.yaml`:

```yaml
bsap_sensor:
    api_token: '%env(SENSOR_API_TOKEN)%'
    base_uri: 'https://sensor.besmartand.pro'
    timeout: 10.0
    http_client: 'http_client'
```

Tylko `api_token` jest wymagany. Pozostałe wartości powyżej są domyślne. Token umieść w zmiennej środowiskowej lub Symfony Secrets; nie zapisuj go w repozytorium.

`http_client` pozwala wskazać istniejący scoped HTTP client aplikacji. Bundle tworzy własną pochodną tego serwisu, ustawia timeout i maksymalny czas żądania, a także wyłącza przekierowania. Aplikacja powinna mieć włączony HttpClient FrameworkBundle, np. `framework: { http_client: ~ }`.

## Użycie przez autowiring

```php
use BeSmartAndPro\BsapSensorClient\SensorClientInterface;

final readonly class SensorHistoryService
{
    public function __construct(private SensorClientInterface $sensorClient)
    {
    }

    public function read(string $sensorId): array
    {
        return $this->sensorClient->getSensorHistory($sensorId);
    }
}
```

Można również wstrzyknąć konkretną klasę `SensorClient`. Interfejs i klasa wskazują ten sam serwis. Opis modeli, wyjątków i ograniczeń endpointu znajduje się w README biblioteki `bsap-sensor-client`.

Licencja MIT.
