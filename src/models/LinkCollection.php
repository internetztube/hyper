<?php
namespace verbb\hyper\models;

use verbb\hyper\base\ElementLink;
use verbb\hyper\base\LinkInterface;
use verbb\hyper\fields\HyperField;

use craft\base\ElementInterface;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class LinkCollection implements IteratorAggregate, Countable, ArrayAccess
{
    // Properties
    // =========================================================================

    private HyperField $_field;
    private array $_links = [];
    private ?ElementInterface $_element = null;
    private ?LinkInterface $_firstLink = null;


    // Public Methods
    // =========================================================================

    public function __construct(HyperField $field, array $links = [], ?ElementInterface $element = null)
    {
        $this->_element = $element;
        $this->_field = $field;

        // Convert serialized data to a collection of links.
        foreach ($links as $data) {
            if (!($data instanceof LinkInterface)) {
                if (is_array($data)) {
                    if ($handle = $this->_getLinkTypeHandle($data, $field)) {
                        if ($link = $field->getLinkTypeByHandle($handle)) {
                            $newLink = clone($link);

                            $newLink->setAttributes($data, false);

                            $this->_links[] = $newLink;
                        }
                    }
                }
            } else {
                $this->_links[] = $data;
            }
        }

        $this->_firstLink = $this->_links[0] ?? null;
    }

    public function __toString(): string
    {
        if ($this->_firstLink) {
            return (string)$this->_firstLink;
        }

        return '';
    }

    public function __isset($name): bool
    {
        return isset($this->_firstLink->$name);
    }

    public function __get($name)
    {
        return $this->_firstLink->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name($value);
    }

    public function __call($name, $params)
    {
        if ($this->_firstLink) {
            if (property_exists($this->_firstLink, $name)) {
                return $this->_firstLink->$name;
            }

            return call_user_func_array([$this->_firstLink, $name], $params);
        }

        return $this;
    }

    public function __debugInfo()
    {
        // For developer AX with `dd` and `dump`, keep things lean.
        if (!$this->_field->multipleLinks) {
            if ($this->_firstLink) {
                return $this->_firstLink->__debugInfo();
            }
        }

        return $this->_links;
    }

    public function __clone()
    {
        // Ensure that when we clone this collection, we deep-clone
        $this->_field = clone $this->_field;
        $this->_element = $this->_element ? clone $this->_element : null;
        $this->_firstLink = $this->_firstLink ? clone $this->_firstLink : null;

        foreach ($this->_links as $key => $link) {
            $this->_links[$key] = clone $link;
        }
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->_links);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->_links[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->_links[$offset]) ? $this->_links[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->_links[$offset] = $item;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->_links[$offset]);
    }

    public function count(): int
    {
        if (!$this->_field->multipleLinks) {
            if ($this->_firstLink) {
                return $this->_firstLink->count();
            }
        }

        return count($this->_links);
    }

    public function isEmpty(): bool
    {
        if (!$this->_field->multipleLinks) {
            if ($this->_firstLink) {
                return $this->_firstLink->isEmpty();
            }
        }
        
        return !$this->count();
    }

    public function getLinks(): array
    {
        return $this->_links;
    }

    public function setLinks(array $value): void
    {
        $this->_links = $value;
    }

    public function serializeValues(?ElementInterface $element = null): array
    {
        $values = [];

        foreach ($this->_links as $link) {
            if ($link instanceof LinkInterface) {
                $values[] = $link->getSerializedValues();
            }
        }

        return $values;
    }


    // Private Methods
    // =========================================================================

    private function _getLinkTypeHandle(array $data, HyperField $field): string
    {
        // Use either the handle of the link type, the first instance of an enabled link type
        // (of that type) or use the default link type set by the field.
        $handle = $data['handle'] ?? null;

        if (!$handle && isset($data['type'])) {
            foreach ($field->getLinkTypes() as $linkType) {
                if ($linkType::class === $data['type']) {
                    $handle = $linkType->handle;
                    break;
                }
            }
        }

        $handle = $handle ?? $field->defaultLinkType;

        return $handle;
    }
}
