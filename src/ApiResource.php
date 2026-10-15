<?php


namespace R3m\Dpae;

use ArrayAccess;
use DateTime;

/**
 * @package R3m\Dpae
 * @internal
 */
abstract class ApiResource implements ArrayAccess
{
    use ApiOperations\Request;

    protected static string $OBJECT_NAME = '';

    /**
     * @var array
     */
    protected array $values = [];

    /**
     * @var ApiResponse|null
     */
    protected ?ApiResponse $lastResponse = null;

    public function __construct(?string $id = null)
    {
        if ($id !== null) {
            $this->values['id'] = $id;
        }
    }

    public function __get(string $k): mixed
    {
        return $this->values[$k] ?? null;
    }

    public function __set(string $k, mixed $v): void
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('La mise à jour de cette ressource n\'est pas permise.');
        }
        $this->values[$k] = $v;
    }

    /**
     * @return string L'url API pour cette instance
     */
    public function instanceUrl(): string
    {
        if (!array_key_exists('id', $this->values)) {
            throw new Exception\UnexpectedValueException('Instance invalide ou non chargée.');
        }

        return static::resourceUrl($this->values['id']);
    }

    /**
     * @param string $id
     *
     * @return string L'url API pour l'instance $id
     */
    public static function resourceUrl(string $id): string
    {
        $base = static::classUrl();
        $extn = urlencode($id);

        return "$base/$extn";
    }

    /**
     * @return string L'url API pour ce type de ressources
     */
    public static function classUrl(): string
    {
        $base = static::$OBJECT_NAME;

        return "/{$base}s";
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('Cette ressource est en lecture seule');
        }
        $this->values[$offset] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('Cette ressource est en lecture seule');
        }
        unset($this->values[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    /**
     * @param array $values
     *
     * @internal
     */
    public function refreshFrom(array $values): void
    {
        foreach ($values as $k => $v) {
            $this->values[$k] = $this->convertValue($v);
        }
    }

    protected function convertValue(mixed $value): mixed
    {
        if (is_array($value)) {
            if (array_key_exists('id', $value)) {
                return static::constructFrom($value);
            }
            $a = [];
            foreach ($value as $k => $v) {
                $a[$k] = $this->convertValue($v);
            }

            return $a;
        }

        if (is_string($value) && preg_match('#\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{2}:\d{2}#', $value)) {
            return DateTime::createFromFormat(DATE_W3C, $value);
        }

        return $value;
    }

    /**
     * @param array $values
     *
     * @return static
     * @internal
     */
    public static function constructFrom(array $values): static
    {
        $obj = new static($values['id'] ?? null);
        $obj->refreshFrom($values);

        return $obj;
    }

    /**
     * Retourne la dernière réponse de l'API
     *
     * @return ApiResponse|null
     */
    public function getLastResponse(): ?ApiResponse
    {
        return $this->lastResponse;
    }

    /**
     * @param ApiResponse $response
     *
     * @internal
     */
    public function setLastResponse(ApiResponse $response): void
    {
        $this->lastResponse = $response;
    }

}
