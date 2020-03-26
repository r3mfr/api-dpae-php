<?php


namespace R3m\Dpae;

/**
 * @package R3m\Dpae
 * @internal
 */
abstract class ApiResource implements \ArrayAccess
{
    use ApiOperations\Request;

    protected static $OBJECT_NAME = '';

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var ApiResponse
     */
    protected $lastResponse = null;

    public function __construct(string $id = null)
    {
        if ($id !== null) {
            $this->values['id'] = $id;
        }
    }

    public function __get($k)
    {
        return $this->values[$k] ?? null;
    }

    public function __set($k, $v)
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
        return "/${base}s";
    }

    public function offsetSet($k, $v)
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('Cette ressource est en lecture seule');
        }
        $this->values[$k] = $v;
    }

    public function offsetExists($k)
    {
        return array_key_exists($this->values, $k);
    }

    public function offsetUnset($k)
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('Cette ressource est en lecture seule');
        }
        unset($this->values[$k]);
    }

    public function offsetGet($k)
    {
        return $this->values[$k] ?? null;
    }

    /**
     * @param array $values
     * @internal
     */
    public function refreshFrom(array $values)
    {
        foreach ($values as $k => $v) {
            $this->values[$k] = $this->convertValue($v);
        }
    }

    protected function convertValue($value)
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

        if (preg_match('#\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{2}:\d{2}#', $value)) {
            return \DateTime::createFromFormat(DATE_W3C, $value);
        }

        return $value;
    }

    /**
     * @param array $values
     * @return static
     * @internal
     */
    public static function constructFrom(array $values)
    {
        $obj = new static($values['id'] ?? null);
        $obj->refreshFrom($values);

        return $obj;
    }

    /**
     * Retourne la dernière réponse de l'API
     *
     * @return ApiResponse
     */
    public function getLastResponse(): ApiResponse
    {
        return $this->lastResponse;
    }

    /**
     * @param ApiResponse $response
     * @internal
     */
    public function setLastResponse(ApiResponse $response)
    {
        $this->lastResponse = $response;
    }

}