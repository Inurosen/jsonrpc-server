<?php


namespace Inurosen\JsonRPCServer;


class JsonRPCResponse implements \JsonSerializable, \ArrayAccess, \IteratorAggregate, \Countable
{
    private $items = [];
    private $isBatch = false;

    public function __construct($results, $isBatch)
    {
        $this->items = $results;
        $this->isBatch = $isBatch;
    }

    /**
     * Get all results as raw
     *
     * @return array
     */
    public function getResults()
    {
        return $this->items;
    }

    /**
     * Determine if this is a batch result
     *
     * @return bool
     */
    public function isBatch()
    {
        return $this->isBatch;
    }

    public function jsonSerialize()
    {
        if ($this->count() === 0) {
            return [];
        }

        return $this->isBatch() ? $this->items : $this->items[0];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Convert result to array recursively.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }

    /**
     * Convert result to JSON.
     *
     * @return string
     */
    public function toJson()
    {
        if ($this->items === null) {
            return '';
        }

        return json_encode($this->jsonSerialize());
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Convert result to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert result to its string representation.
     *
     * @return string
     */
    public function toString()
    {
        return $this->toJson();
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return $this->items !== null ? count($this->items) : 0;
    }
}
