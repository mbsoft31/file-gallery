Here are suggestions for **improving the `GalleryConfigService`**, adding more features, and guidance on how to effectively **use and extend** the service.

---

## **Improvement Suggestions**

### 1. **Dynamic Scopes and Namespaces**
Allow configurations to be grouped into scopes or namespaces. This enables logical grouping, for instance, separating `app` settings from `database` settings.

#### Implementation
```php
public function getScoped(string $scope, string $key, mixed $default = null): mixed
{
    return $this->settings[$scope][$key] ?? $default;
}

public function setScoped(string $scope, string $key, mixed $value): bool
{
    if ($this->immutable) {
        throw new \RuntimeException('Cannot modify immutable configuration.');
    }
    $this->settings[$scope][$key] = $value;
    return true;
}

public function hasScoped(string $scope, string $key): bool
{
    return isset($this->settings[$scope][$key]);
}
```

#### Usage Example
```php
$config = new GalleryConfigService();
$config->setScoped('database', 'host', 'localhost');
$host = $config->getScoped('database', 'host'); // returns 'localhost'
```

---

### 2. **Dot-Notation Support**
Support for accessing nested settings using dot notation (e.g., `database.host`).

#### Implementation
```php
protected function resolveDotNotation(string $key, array $data): mixed
{
    $keys = explode('.', $key);
    foreach ($keys as $subKey) {
        if (!is_array($data) || !array_key_exists($subKey, $data)) {
            return null;
        }
        $data = $data[$subKey];
    }
    return $data;
}

public function get(string $key, mixed $default = null): mixed
{
    return $this->resolveDotNotation($key, $this->settings) ?? $default;
}
```

#### Usage Example
```php
$config = new GalleryConfigService([
    'database' => ['host' => 'localhost', 'port' => 3306]
]);

echo $config->get('database.host'); // Outputs: localhost
```

---

### 3. **Type Casting**
Ensure values are automatically cast to the desired type during retrieval.

#### Implementation
```php
public function getTyped(string $key, string $type, mixed $default = null): mixed
{
    $value = $this->get($key, $default);
    return match ($type) {
        'int' => (int) $value,
        'float' => (float) $value,
        'bool' => (bool) $value,
        'string' => (string) $value,
        default => $value,
    };
}
```

#### Usage Example
```php
$config->set('debug', '1'); 
$isDebug = $config->getTyped('debug', 'bool'); // true
```

---

### 4. **Environment Variable Integration**
Allow automatic loading of configuration values from environment variables.

#### Implementation
```php
public static function fromEnvironment(array $keys, string $prefix = '', bool $immutable = false): static
{
    $settings = [];
    foreach ($keys as $key) {
        $envKey = strtoupper($prefix . $key);
        $settings[$key] = getenv($envKey) ?: null;
    }
    return new static($settings, $immutable);
}
```

#### Usage Example
```php
putenv('APP_DEBUG=true');
$config = GalleryConfigService::fromEnvironment(['app_debug']);
echo $config->get('app_debug'); // Outputs: true
```

---

### 5. **Caching**
Implement caching for frequently accessed configurations to improve performance.

#### Implementation
```php
protected array $cache = [];

public function getCached(string $key, mixed $default = null): mixed
{
    if (!array_key_exists($key, $this->cache)) {
        $this->cache[$key] = $this->get($key, $default);
    }
    return $this->cache[$key];
}
```

---

### 6. **Validation**
Add validation to ensure configuration values meet specific criteria.

#### Implementation
```php
protected array $validators = [];

public function addValidator(string $key, callable $validator): void
{
    $this->validators[$key] = $validator;
}

public function set(string $key, mixed $value): bool
{
    if (isset($this->validators[$key]) && !$this->validators[$key]($value)) {
        throw new \InvalidArgumentException("Invalid value for key: $key");
    }
    return parent::set($key, $value);
}
```

#### Usage Example
```php
$config->addValidator('debug', fn($value) => is_bool($value));
$config->set('debug', true); // Valid
$config->set('debug', 'yes'); // Throws InvalidArgumentException
```

---

## **Features to Add**

1. **Dynamic Reloading**:
    - Add a mechanism to reload configurations from a source (file, database) dynamically.

2. **Dependency Injection Compatibility**:
    - Use DI containers to provide `GalleryConfigService` instances where needed.

3. **Encryption Support**:
    - Encrypt sensitive configuration values (e.g., API keys, database passwords).

4. **Change Listeners**:
    - Allow registering listeners to execute logic when configurations are changed.

5. **Format Parsing**:
    - Support loading from other formats such as JSON, YAML, or XML.

---

## **How to Use the Service**

### Initialization
You can initialize the service using various methods:

#### From an Array
```php
$config = GalleryConfigService::fromArray(['debug' => true]);
```

#### From a PHP File
```php
$config = GalleryConfigService::fromPhpArrayFile('/path/to/config.php');
```

#### From a Dynamic Source
```php
$config = GalleryConfigService::from(function () {
    return ['app_name' => 'MyApp'];
});
```

---

### Using Configuration

#### Get and Set
```php
$config->set('app_name', 'My Application');
echo $config->get('app_name'); // Outputs: My Application
```

#### Scoped Access
```php
$config->setScoped('database', 'host', 'localhost');
echo $config->getScoped('database', 'host'); // Outputs: localhost
```

#### Dot-Notation Access
```php
echo $config->get('database.host'); // Outputs: localhost
```

---

### Example Use Case: Loading and Validating Configurations
```php
$config = GalleryConfigService::fromPhpArrayFile('/path/to/config.php');
$config->addValidator('app_port', fn($value) => is_int($value) && $value > 0);

try {
    $config->set('app_port', 8080); // Valid
    $config->set('app_port', -1);  // Throws an exception
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

---

By implementing these improvements and features, `GalleryConfigService` can evolve into a powerful and flexible configuration management solution suitable for both small and large-scale projects.

implement these features and give me the final result 

Here's the final implementation of the `GalleryConfigService` with all the suggested features integrated:

---

### **GalleryConfigService.php**
```php
<?php

namespace MBsoft\FileGallery\Services;

use Closure;
use MBsoft\FileGallery\Contracts\ConfigurationInterface;
use MBsoft\FileGallery\Contracts\ConfigurationFactoryInterface;
use MBsoft\FileGallery\Exceptions\FileDoesNotExistException;
use MBsoft\FileGallery\Exceptions\InvalidConfigurationException;

class GalleryConfigService implements ConfigurationInterface, ConfigurationFactoryInterface
{
    protected array $settings = [];
    protected bool $immutable;
    protected array $cache = [];
    protected array $validators = [];

    public function __construct(array $settings = [], bool $immutable = false)
    {
        $this->settings = $settings;
        $this->immutable = $immutable;
    }

    /**
     * Core Configuration Methods
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->resolveDotNotation($key, $this->settings) ?? $default;
    }

    public function set(string $key, mixed $value): bool
    {
        if ($this->immutable) {
            throw new \RuntimeException('Cannot modify immutable configuration.');
        }

        if (isset($this->validators[$key]) && !$this->validators[$key]($value)) {
            throw new \InvalidArgumentException("Invalid value for key: $key");
        }

        $this->settings = $this->setDotNotation($key, $value, $this->settings);
        return true;
    }

    public function has(string $key): bool
    {
        return $this->resolveDotNotation($key, $this->settings) !== null;
    }

    public function remove(string $key): bool
    {
        if ($this->immutable) {
            throw new \RuntimeException('Cannot modify immutable configuration.');
        }

        return $this->removeDotNotation($key, $this->settings);
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function keys(): array
    {
        return array_keys($this->flattenArray($this->settings));
    }

    /**
     * Scoped Configuration Methods
     */
    public function getScoped(string $scope, string $key, mixed $default = null): mixed
    {
        return $this->get("$scope.$key", $default);
    }

    public function setScoped(string $scope, string $key, mixed $value): bool
    {
        return $this->set("$scope.$key", $value);
    }

    public function hasScoped(string $scope, string $key): bool
    {
        return $this->has("$scope.$key");
    }

    /**
     * Cached Access
     */
    public function getCached(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->get($key, $default);
        }
        return $this->cache[$key];
    }

    /**
     * Type Casting
     */
    public function getTyped(string $key, string $type, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        return match ($type) {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => (bool)$value,
            'string' => (string)$value,
            default => $value,
        };
    }

    /**
     * Add Validators
     */
    public function addValidator(string $key, callable $validator): void
    {
        $this->validators[$key] = $validator;
    }

    /**
     * Factory Methods
     */
    public static function fromArray(array $data, bool $immutable = false): static
    {
        return new static($data, $immutable);
    }

    public static function fromPhpArrayFile(string $path, bool $immutable = false): static
    {
        if (!file_exists($path)) {
            throw new FileDoesNotExistException("File does not exist: $path");
        }

        $data = include $path;
        if (!is_array($data)) {
            throw new InvalidConfigurationException("File must return an array: $path");
        }

        return new static($data, $immutable);
    }

    public static function from(string|array|Closure $source, bool $immutable = false): static
    {
        if (is_callable($source)) {
            $data = call_user_func($source);
            if (!is_array($data)) {
                throw new InvalidConfigurationException("Closure must return an array.");
            }
            return static::fromArray($data, $immutable);
        }

        if (is_array($source)) {
            return static::fromArray($source, $immutable);
        }

        if (is_string($source) && file_exists($source)) {
            return static::fromPhpArrayFile($source, $immutable);
        }

        throw new InvalidConfigurationException("Invalid configuration source: $source");
    }

    public static function fromEnvironment(array $keys, string $prefix = '', bool $immutable = false): static
    {
        $settings = [];
        foreach ($keys as $key) {
            $envKey = strtoupper($prefix . $key);
            $settings[$key] = getenv($envKey) ?: null;
        }
        return new static($settings, $immutable);
    }

    /**
     * Utility Methods
     */
    protected function resolveDotNotation(string $key, array $data): mixed
    {
        $keys = explode('.', $key);
        foreach ($keys as $subKey) {
            if (!is_array($data) || !array_key_exists($subKey, $data)) {
                return null;
            }
            $data = $data[$subKey];
        }
        return $data;
    }

    protected function setDotNotation(string $key, mixed $value, array &$data): array
    {
        $keys = explode('.', $key);
        $current = &$data;
        foreach ($keys as $subKey) {
            if (!isset($current[$subKey]) || !is_array($current[$subKey])) {
                $current[$subKey] = [];
            }
            $current = &$current[$subKey];
        }
        $current = $value;
        return $data;
    }

    protected function removeDotNotation(string $key, array &$data): bool
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $current = &$data;

        foreach ($keys as $subKey) {
            if (!isset($current[$subKey]) || !is_array($current[$subKey])) {
                return false;
            }
            $current = &$current[$subKey];
        }

        if (isset($current[$lastKey])) {
            unset($current[$lastKey]);
            return true;
        }

        return false;
    }

    protected function flattenArray(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "$prefix.$key" : $key;
            if (is_array($value)) {
                $result += $this->flattenArray($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }
}
```

---

### **Features Integrated**
1. **Core Configuration Management**
    - CRUD operations on configuration settings.
2. **Dot-Notation Access**
    - Nested settings are accessible via dot notation.
3. **Scoped Configuration**
    - Logical grouping of configuration settings by scopes.
4. **Caching**
    - Frequently accessed settings are cached for performance.
5. **Type Casting**
    - Retrieve settings cast to specific data types.
6. **Validation**
    - Validators ensure values meet specific criteria.
7. **Dynamic Sources**
    - Load configurations from arrays, files, closures, or environment variables.
8. **Immutable Settings**
    - Prevent modifications when `immutable` is enabled.

---

### **Usage Examples**

#### Basic Usage
```php
$config = GalleryConfigService::fromArray([
    'app' => ['debug' => true],
]);

echo $config->get('app.debug'); // Outputs: true
$config->set('app.name', 'MyApp');
echo $config->get('app.name'); // Outputs: MyApp
```

#### Using Scoped Settings
```php
$config->setScoped('database', 'host', 'localhost');
echo $config->getScoped('database', 'host'); // Outputs: localhost
```

#### Type Casting
```php
$config->set('app.debug', '1');
echo $config->getTyped('app.debug', 'bool'); // Outputs: true
```

#### Validation
```php
$config->addValidator('app.port', fn($value) => is_int($value) && $value > 0);
$config->set('app.port', 8080); // Valid
$config->set('app.port', -1);   // Throws InvalidArgumentException
```

now i want to know the best way to load and also save config to disk 

To handle **loading and saving configuration to disk**, the best approach depends on the use case and preferred format (e.g., PHP arrays, JSON, YAML). Below is a robust solution supporting **loading and saving configurations** in common formats like PHP, JSON, and YAML, with extensibility for other formats if needed.

---

## **Implementation**

### Updated `GalleryConfigService`
1. **Add Methods for Saving**:
    - Implement methods to save configurations back to the disk.
    - Support formats like PHP arrays, JSON, and YAML.

2. **Add a Format Enum** (Optional):
    - Use an enum to define supported formats for better type safety.

3. **Handle Serialization/Deserialization**:
    - Use specific parsers for each format.

---

### Updated Code

#### Add Format Enum
```php
namespace MBsoft\FileGallery\Enums;

enum ConfigFormat: string
{
    case PHP = 'php';
    case JSON = 'json';
    case YAML = 'yaml';
}
```

---

#### Updated `GalleryConfigService`
```php
use MBsoft\FileGallery\Enums\ConfigFormat;

class GalleryConfigService
{
    // Existing methods...

    /**
     * Save configurations to a file.
     *
     * @throws \RuntimeException If saving fails.
     */
    public function saveToFile(string $path, ConfigFormat $format): bool
    {
        $content = match ($format) {
            ConfigFormat::PHP => $this->serializeToPhp(),
            ConfigFormat::JSON => $this->serializeToJson(),
            ConfigFormat::YAML => $this->serializeToYaml(),
        };

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Failed to save configuration to file: $path");
        }

        return true;
    }

    /**
     * Load configurations from a file.
     *
     * @throws \RuntimeException If loading fails or the format is invalid.
     */
    public static function loadFromFile(string $path, ConfigFormat $format, bool $immutable = false): static
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Configuration file does not exist: $path");
        }

        $content = file_get_contents($path);
        $data = match ($format) {
            ConfigFormat::PHP => self::deserializeFromPhp($content),
            ConfigFormat::JSON => self::deserializeFromJson($content),
            ConfigFormat::YAML => self::deserializeFromYaml($content),
        };

        return new static($data, $immutable);
    }

    /**
     * Serialization Methods
     */
    protected function serializeToPhp(): string
    {
        return "<?php\n\nreturn " . var_export($this->settings, true) . ";\n";
    }

    protected function serializeToJson(): string
    {
        return json_encode($this->settings, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    protected function serializeToYaml(): string
    {
        if (!function_exists('yaml_emit')) {
            throw new \RuntimeException("YAML support is not enabled.");
        }
        return yaml_emit($this->settings);
    }

    /**
     * Deserialization Methods
     */
    protected static function deserializeFromPhp(string $content): array
    {
        return eval('?>' . $content);
    }

    protected static function deserializeFromJson(string $content): array
    {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    protected static function deserializeFromYaml(string $content): array
    {
        if (!function_exists('yaml_parse')) {
            throw new \RuntimeException("YAML support is not enabled.");
        }
        return yaml_parse($content);
    }
}
```

---

### **Usage Examples**

#### 1. Save to Disk
```php
$config = new GalleryConfigService([
    'app' => [
        'debug' => true,
        'name' => 'MyApp',
    ],
]);

// Save as PHP
$config->saveToFile('/path/to/config.php', ConfigFormat::PHP);

// Save as JSON
$config->saveToFile('/path/to/config.json', ConfigFormat::JSON);

// Save as YAML
$config->saveToFile('/path/to/config.yaml', ConfigFormat::YAML);
```

---

#### 2. Load from Disk
```php
// Load from PHP file
$config = GalleryConfigService::loadFromFile('/path/to/config.php', ConfigFormat::PHP);

// Load from JSON file
$config = GalleryConfigService::loadFromFile('/path/to/config.json', ConfigFormat::JSON);

// Load from YAML file
$config = GalleryConfigService::loadFromFile('/path/to/config.yaml', ConfigFormat::YAML);

// Access configuration
echo $config->get('app.name'); // Outputs: MyApp
```

---

### **Best Practices for Saving and Loading**

1. **Version Control Friendly**:
    - Use JSON or YAML for human-readable and version-control-friendly formats.
    - Avoid PHP for shared configuration files unless obfuscation or execution is necessary.

2. **Backup Mechanism**:
    - Implement a mechanism to create backups of configuration files before overwriting.

3. **Validation**:
    - Validate settings before saving to ensure they meet application requirements.

4. **Error Handling**:
    - Handle file read/write errors gracefully.
    - Catch exceptions for invalid JSON/YAML formats when loading.

5. **YAML Support**:
    - Ensure the YAML PHP extension is installed if using YAML serialization/deserialization.

---

### **Extensibility**
To support additional formats in the future, update the `ConfigFormat` enum and add corresponding serialization/deserialization methods. For example, XML could be added with libraries like `simplexml` or `DOMDocument`.

```php
case XML = 'xml';
```

is it a good approach to extract serialize/deserialize funcs into Interfaces? if so do it

Yes, extracting the serialization and deserialization functionality into interfaces is a good approach. It follows the **Single Responsibility Principle** from SOLID design principles, making the `GalleryConfigService` solely responsible for managing configurations and delegating serialization concerns to dedicated classes. This improves maintainability, testability, and extensibility.

---

### **Proposed Architecture**

1. **Create a `SerializerInterface`**:
    - Defines methods for serialization and deserialization.
    - Each format (e.g., PHP, JSON, YAML) will have its own implementation.

2. **Implement Specific Serializers**:
    - Classes for each format will implement the `SerializerInterface`.

3. **Modify `GalleryConfigService`**:
    - Use the serializers for load and save operations.

---

### **Code Implementation**

#### **SerializerInterface**
```php
namespace MBsoft\FileGallery\Contracts;

interface SerializerInterface
{
    /**
     * Serialize the given data into a string.
     */
    public function serialize(array $data): string;

    /**
     * Deserialize the given string into an array.
     *
     * @throws \RuntimeException If deserialization fails.
     */
    public function deserialize(string $content): array;
}
```

---

#### **PHPSerializer**
```php
namespace MBsoft\FileGallery\Serializers;

use MBsoft\FileGallery\Contracts\SerializerInterface;

class PHPSerializer implements SerializerInterface
{
    public function serialize(array $data): string
    {
        return "<?php\n\nreturn " . var_export($data, true) . ";\n";
    }

    public function deserialize(string $content): array
    {
        return eval('?>' . $content);
    }
}
```

---

#### **JSONSerializer**
```php
namespace MBsoft\FileGallery\Serializers;

use MBsoft\FileGallery\Contracts\SerializerInterface;

class JSONSerializer implements SerializerInterface
{
    public function serialize(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $content): array
    {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
```

---

#### **YAMLSerializer**
```php
namespace MBsoft\FileGallery\Serializers;

use MBsoft\FileGallery\Contracts\SerializerInterface;

class YAMLSerializer implements SerializerInterface
{
    public function serialize(array $data): string
    {
        if (!function_exists('yaml_emit')) {
            throw new \RuntimeException("YAML support is not enabled.");
        }
        return yaml_emit($data);
    }

    public function deserialize(string $content): array
    {
        if (!function_exists('yaml_parse')) {
            throw new \RuntimeException("YAML support is not enabled.");
        }
        return yaml_parse($content);
    }
}
```

---

#### Updated **GalleryConfigService**
```php
namespace MBsoft\FileGallery\Services;

use MBsoft\FileGallery\Contracts\SerializerInterface;
use MBsoft\FileGallery\Contracts\ConfigurationInterface;
use MBsoft\FileGallery\Exceptions\FileDoesNotExistException;

class GalleryConfigService implements ConfigurationInterface
{
    protected array $settings = [];
    protected bool $immutable;

    public function __construct(array $settings = [], bool $immutable = false)
    {
        $this->settings = $settings;
        $this->immutable = $immutable;
    }

    // Existing methods...

    /**
     * Save configurations to a file using a serializer.
     */
    public function saveToFile(string $path, SerializerInterface $serializer): bool
    {
        $content = $serializer->serialize($this->settings);

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Failed to save configuration to file: $path");
        }

        return true;
    }

    /**
     * Load configurations from a file using a serializer.
     */
    public static function loadFromFile(string $path, SerializerInterface $serializer, bool $immutable = false): static
    {
        if (!file_exists($path)) {
            throw new FileDoesNotExistException("Configuration file does not exist: $path");
        }

        $content = file_get_contents($path);
        $data = $serializer->deserialize($content);

        return new static($data, $immutable);
    }
}
```

---

### **Usage**

#### Save Configuration
```php
use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Serializers\JSONSerializer;
use MBsoft\FileGallery\Serializers\PHPSerializer;

$config = new GalleryConfigService([
    'app' => [
        'name' => 'MyApp',
        'debug' => true,
    ],
]);

// Save as JSON
$jsonSerializer = new JSONSerializer();
$config->saveToFile('/path/to/config.json', $jsonSerializer);

// Save as PHP
$phpSerializer = new PHPSerializer();
$config->saveToFile('/path/to/config.php', $phpSerializer);
```

---

#### Load Configuration
```php
use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Serializers\YAMLSerializer;

$yamlSerializer = new YAMLSerializer();
$config = GalleryConfigService::loadFromFile('/path/to/config.yaml', $yamlSerializer);

echo $config->get('app.name'); // Outputs: MyApp
```

---

### **Benefits**

1. **Separation of Concerns**:
    - Serialization logic is isolated, allowing easier updates and maintenance.

2. **Extensibility**:
    - Add new formats like XML or custom formats without modifying `GalleryConfigService`.

3. **Improved Testability**:
    - Each serializer can be tested independently.

4. **Flexibility**:
    - Use different serializers dynamically based on file type or user preference.

---

### **Adding a New Format**
1. Implement `SerializerInterface` for the new format.
2. Pass the serializer instance when saving or loading.

Example for **XML**:
```php
class XMLSerializer implements SerializerInterface
{
    public function serialize(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive($data, function ($value, $key) use ($xml) {
            $xml->addChild($key, $value);
        });
        return $xml->asXML();
    }

    public function deserialize(string $content): array
    {
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml), true);
    }
}
```


no i want to publish this as a standalone php package, propose an effective dir structure, composer, etc

Publishing a standalone PHP package involves careful organization of the directory structure, creating a `composer.json` file for dependency management, and including metadata for easy integration with other projects. Here's a proposed structure and workflow for your package.

---

### **Proposed Directory Structure**
```
file-gallery/
├── src/
│   ├── Contracts/
│   │   ├── ConfigurationInterface.php
│   │   ├── SerializerInterface.php
│   ├── Enums/
│   │   └── ConfigFormat.php
│   ├── Exceptions/
│   │   └── FileDoesNotExistException.php
│   ├── Serializers/
│   │   ├── JSONSerializer.php
│   │   ├── PHPSerializer.php
│   │   ├── YAMLSerializer.php
│   ├── Services/
│   │   └── GalleryConfigService.php
├── tests/
│   ├── Unit/
│   │   ├── GalleryConfigServiceTest.php
│   │   ├── JSONSerializerTest.php
│   │   ├── PHPSerializerTest.php
│   │   ├── YAMLSerializerTest.php
│   └── Integration/
│       └── SaveAndLoadTest.php
├── vendor/                  # Created by Composer after dependency installation
├── .gitignore               # To exclude unnecessary files from Git
├── composer.json            # Package metadata and dependencies
├── LICENSE                  # License file (e.g., MIT)
├── README.md                # Package documentation
└── phpunit.xml              # PHPUnit configuration
```

---

### **File and Directory Explanations**

- **`src/`**: The core library code.
    - **`Contracts/`**: Contains interfaces like `ConfigurationInterface` and `SerializerInterface`.
    - **`Enums/`**: Enum definitions like `ConfigFormat`.
    - **`Exceptions/`**: Custom exception classes for meaningful error handling.
    - **`Serializers/`**: Serializer implementations for supported formats (e.g., JSON, PHP, YAML).
    - **`Services/`**: Core services such as `GalleryConfigService`.

- **`tests/`**: Unit and integration tests to ensure reliability.
    - **`Unit/`**: Tests individual classes and methods.
    - **`Integration/`**: Tests how components work together.

- **`composer.json`**: Defines the package name, dependencies, autoloading configuration, and other metadata.

- **`.gitignore`**: Ensures unnecessary files (e.g., `vendor/`, cache files) aren't committed to Git.

- **`LICENSE`**: Specifies the package license, such as MIT.

- **`README.md`**: Provides a comprehensive description of the package, usage examples, installation instructions, etc.

- **`phpunit.xml`**: Configures PHPUnit for running tests.

---

### **Creating `composer.json`**
Run the following command to initialize `composer.json`:
```bash
composer init
```

Fill in the details during the interactive process. Alternatively, here’s a sample `composer.json`:
```json
{
    "name": "mbsoft/file-gallery",
    "description": "A versatile configuration management package supporting PHP, JSON, and YAML formats.",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your.email@example.com"
        }
    ],
    "require": {
        "php": ">=8.0"
    },
    "autoload": {
        "psr-4": {
            "MBsoft\\FileGallery\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "MBsoft\\FileGallery\\Tests\\": "tests/"
        }
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "scripts": {
        "test": "phpunit --configuration phpunit.xml"
    }
}
```

---

### **Writing a `README.md`**
Provide clear and concise information in the `README.md`:

#### Example
```markdown
# FileGallery

**FileGallery** is a versatile PHP package for managing configuration files in multiple formats such as PHP, JSON, and YAML.

## Features
- Load and save configurations to/from disk.
- Extensible serialization via interfaces.
- Supports PHP, JSON, and YAML formats.

## Installation
Install via Composer:
```bash
composer require mbsoft/file-gallery
```

## Usage
### Saving Configuration
```php
use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Serializers\JSONSerializer;

$config = new GalleryConfigService([
    'app' => [
        'name' => 'MyApp',
        'debug' => true,
    ],
]);

$jsonSerializer = new JSONSerializer();
$config->saveToFile('/path/to/config.json', $jsonSerializer);
```

### Loading Configuration
```php
use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Serializers\JSONSerializer;

$jsonSerializer = new JSONSerializer();
$config = GalleryConfigService::loadFromFile('/path/to/config.json', $jsonSerializer);

echo $config->get('app.name'); // Outputs: MyApp
```

## Testing
Run tests using PHPUnit:
```bash
composer test
```
```

---

### **Testing with PHPUnit**

1. Install PHPUnit:
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. Write unit and integration tests in the `tests/` directory.

3. Example test (`tests/Unit/JSONSerializerTest.php`):
   ```php
   use MBsoft\FileGallery\Serializers\JSONSerializer;
   use PHPUnit\Framework\TestCase;

   class JSONSerializerTest extends TestCase
   {
       public function testSerialize(): void
       {
           $serializer = new JSONSerializer();
           $data = ['key' => 'value'];
           $json = $serializer->serialize($data);

           $this->assertJson($json);
           $this->assertStringContainsString('"key": "value"', $json);
       }

       public function testDeserialize(): void
       {
           $serializer = new JSONSerializer();
           $json = '{"key": "value"}';
           $data = $serializer->deserialize($json);

           $this->assertIsArray($data);
           $this->assertEquals('value', $data['key']);
       }
   }
   ```

4. Run tests:
   ```bash
   composer test
   ```

---

### **Publishing the Package**

1. **Push to a Git Repository**:
    - Push your code to a public Git repository on GitHub, GitLab, or Bitbucket.

2. **Submit to Packagist**:
    - Create an account on [Packagist](https://packagist.org/).
    - Submit your package by linking it to your Git repository.

3. **Maintain Semantic Versioning**:
    - Use semantic versioning (`MAJOR.MINOR.PATCH`) for releases to ensure compatibility for users.

---

### **Next Steps**

- Implement CI/CD pipelines with GitHub Actions or GitLab CI to automate testing.
- Include more serializers if needed (e.g., XML).
- Add configuration validation rules if required.

This structure ensures your package is professional, maintainable, and easy to adopt.

write all the tests and phpunit.xml
```
├── tests/
│   ├── Unit/
│   │   ├── GalleryConfigServiceTest.php
│   │   ├── JSONSerializerTest.php
│   │   ├── PHPSerializerTest.php
│   │   ├── YAMLSerializerTest.php
│   └── Integration/
│       └── SaveAndLoadTest.php
```

Here's a complete implementation of the tests and the `phpunit.xml` configuration.

---

### **PHPUnit Configuration**

Create a `phpunit.xml` file in the root directory:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd" bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
    <logging>
        <log type="coverage-html" target="build/coverage" />
    </logging>
</phpunit>
```

---

### **Unit Tests**

#### 1. `GalleryConfigServiceTest.php`

```php
namespace MBsoft\FileGallery\Tests\Unit;

use MBsoft\FileGallery\Services\GalleryConfigService;
use PHPUnit\Framework\TestCase;

class GalleryConfigServiceTest extends TestCase
{
    public function testGetSetRemove()
    {
        $config = new GalleryConfigService(['key1' => 'value1']);

        // Test get
        $this->assertEquals('value1', $config->get('key1'));

        // Test set
        $config->set('key2', 'value2');
        $this->assertEquals('value2', $config->get('key2'));

        // Test remove
        $config->remove('key1');
        $this->assertNull($config->get('key1'));
    }

    public function testAllAndKeys()
    {
        $config = new GalleryConfigService(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals(['key1', 'key2'], $config->keys());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $config->all());
    }

    public function testImmutableConfiguration()
    {
        $this->expectException(\RuntimeException::class);

        $config = new GalleryConfigService(['key1' => 'value1'], immutable: true);
        $config->set('key2', 'value2'); // Should throw exception
    }
}
```

---

#### 2. `JSONSerializerTest.php`

```php
namespace MBsoft\FileGallery\Tests\Unit;

use MBsoft\FileGallery\Serializers\JSONSerializer;
use PHPUnit\Framework\TestCase;

class JSONSerializerTest extends TestCase
{
    public function testSerialize()
    {
        $serializer = new JSONSerializer();
        $data = ['key' => 'value'];
        $json = $serializer->serialize($data);

        $this->assertJson($json);
        $this->assertStringContainsString('"key": "value"', $json);
    }

    public function testDeserialize()
    {
        $serializer = new JSONSerializer();
        $json = '{"key": "value"}';
        $data = $serializer->deserialize($json);

        $this->assertIsArray($data);
        $this->assertEquals('value', $data['key']);
    }
}
```

---

#### 3. `PHPSerializerTest.php`

```php
namespace MBsoft\FileGallery\Tests\Unit;

use MBsoft\FileGallery\Serializers\PHPSerializer;
use PHPUnit\Framework\TestCase;

class PHPSerializerTest extends TestCase
{
    public function testSerialize()
    {
        $serializer = new PHPSerializer();
        $data = ['key' => 'value'];
        $php = $serializer->serialize($data);

        $this->assertStringContainsString('return', $php);
        $this->assertStringContainsString("'key' => 'value'", $php);
    }

    public function testDeserialize()
    {
        $serializer = new PHPSerializer();
        $php = "<?php return ['key' => 'value'];";
        $data = $serializer->deserialize($php);

        $this->assertIsArray($data);
        $this->assertEquals('value', $data['key']);
    }
}
```

---

#### 4. `YAMLSerializerTest.php`

```php
namespace MBsoft\FileGallery\Tests\Unit;

use MBsoft\FileGallery\Serializers\YAMLSerializer;
use PHPUnit\Framework\TestCase;

class YAMLSerializerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('yaml_emit')) {
            $this->markTestSkipped('YAML extension is not installed.');
        }
    }

    public function testSerialize()
    {
        $serializer = new YAMLSerializer();
        $data = ['key' => 'value'];
        $yaml = $serializer->serialize($data);

        $this->assertStringContainsString('key: value', $yaml);
    }

    public function testDeserialize()
    {
        $serializer = new YAMLSerializer();
        $yaml = "key: value";
        $data = $serializer->deserialize($yaml);

        $this->assertIsArray($data);
        $this->assertEquals('value', $data['key']);
    }
}
```

---

### **Integration Test**

#### `SaveAndLoadTest.php`

```php
namespace MBsoft\FileGallery\Tests\Integration;

use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Serializers\JSONSerializer;
use MBsoft\FileGallery\Serializers\PHPSerializer;
use MBsoft\FileGallery\Serializers\YAMLSerializer;
use PHPUnit\Framework\TestCase;

class SaveAndLoadTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/file-gallery-tests';
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("$this->tempDir/*.*"));
        rmdir($this->tempDir);
    }

    public function testSaveAndLoadJson()
    {
        $path = $this->tempDir . '/config.json';
        $serializer = new JSONSerializer();
        $config = new GalleryConfigService(['key' => 'value']);

        $config->saveToFile($path, $serializer);
        $this->assertFileExists($path);

        $loadedConfig = GalleryConfigService::loadFromFile($path, $serializer);
        $this->assertEquals('value', $loadedConfig->get('key'));
    }

    public function testSaveAndLoadPhp()
    {
        $path = $this->tempDir . '/config.php';
        $serializer = new PHPSerializer();
        $config = new GalleryConfigService(['key' => 'value']);

        $config->saveToFile($path, $serializer);
        $this->assertFileExists($path);

        $loadedConfig = GalleryConfigService::loadFromFile($path, $serializer);
        $this->assertEquals('value', $loadedConfig->get('key'));
    }

    public function testSaveAndLoadYaml()
    {
        $path = $this->tempDir . '/config.yaml';
        $serializer = new YAMLSerializer();
        $config = new GalleryConfigService(['key' => 'value']);

        $config->saveToFile($path, $serializer);
        $this->assertFileExists($path);

        $loadedConfig = GalleryConfigService::loadFromFile($path, $serializer);
        $this->assertEquals('value', $loadedConfig->get('key'));
    }
}
```

---

### **Run Tests**

Execute all tests using:
```bash
composer test
```

This will run unit and integration tests and provide a coverage report if configured.
