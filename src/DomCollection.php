<?php
declare(strict_types=1);

namespace Zae\DOM;

use ArrayAccess;
use Closure;
use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Zae\DOM\Contracts\DomCollectionInterface;
use Zae\DOM\Contracts\DomElementInterface;
use Zae\DOM\Contracts\DomInterface;
use Zae\DOM\Traits\CollectionArrayAccess;
use Zae\DOM\Traits\CollectionCountable;
use Zae\DOM\Traits\CollectionIteratorAggregate;
use Zae\DOM\Traits\Stringable;

/**
 * Class DomCollection
 *
 * @package Zae\DOM
 */
class DomCollection implements DomCollectionInterface, ArrayAccess, IteratorAggregate, Countable
{
    use CollectionArrayAccess;
    use CollectionIteratorAggregate;
    use CollectionCountable;
    use Stringable;

    /**
     * @var Collection
     */
    private $elements;

    /**
     * @var CssSelectorConverter|null
     */
    private $selectorConverter;

    /**
     * DomCollection constructor.
     *
     * @param array|DomInterface[]  $elements
     * @param CssSelectorConverter|null $selectorConverter
     */
    public function __construct(array $elements = [], ?CssSelectorConverter $selectorConverter = null)
    {
        $this->elements = collect($elements);
        $this->selectorConverter = $selectorConverter ?? new CssSelectorConverter(true);
    }

    /**
     * @param callable $callback
     *
     * @return DomCollection
     */
    public function map(callable $callback): DomCollection
    {
        return new static(
            $this
                ->elements
                ->map($callback)
                ->toArray(),
            $this->selectorConverter
        );
    }

    /**
     * @return ?DomInterface
     */
    public function first(): ?DomInterface
    {
        return $this
            ->elements
            ->first();
    }

    /**
     * @param callable $callback
     *
     * @return self
     */
    public function each(callable $callback): self
    {
        $this
            ->elements
            ->each($callback);

        return $this;
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->elements->map(static function (DomInterface $element) {
            return $element->text();
        })->implode(' ');
    }

    /**
     * @return self
     */
    public function remove(): self
    {
        $this->elements->each(static function (DomInterface $element) {
            $element->remove();
        });

        return $this;
    }

    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function find(string $selector): DomCollection
    {
        return $this->elements->map(static function (DomInterface $element) use ($selector) {
            return $element->find($selector);
        })
        ->reduce(
            Closure::fromCallable([__CLASS__, 'reduceCollection']),
            new DomCollection([], $this->selectorConverter)
        );
    }

    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function findxPath(string $selector): DomCollection
    {
        return $this->elements->map(static function (DomInterface $element) use ($selector) {
            return $element->findxPath($selector);
        })
        ->reduce(
            Closure::fromCallable([__CLASS__, 'reduceCollection']),
            new DomCollection([], $this->selectorConverter)
        );
    }

    /**
     * @return self
     */
    public function precedingSiblings(): self
    {
        return $this->elements->map(static function (DomInterface $element) {
            return $element->precedingSiblings();
        })
        ->reduce(
            Closure::fromCallable([__CLASS__, 'reduceCollection']),
            new DomCollection([], $this->selectorConverter)
        );
    }

    /**
     * @return self
     */
    public function nextSiblings(): self
    {
        return $this->elements->map(static function (DomInterface $element) {
            return $element->nextSiblings();
        })
        ->reduce(
            Closure::fromCallable([__CLASS__, 'reduceCollection']),
            new DomCollection([], $this->selectorConverter)
        );
    }

    /**
     * @param DomElementInterface $wrapper
     * @return self
     */
    public function wrap(DomElementInterface $wrapper): self
    {
        $this->elements->each(static function (DomElementInterface $element) use ($wrapper) {
            $element->wrap($wrapper);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $elem
     * @return self
     */
    public function before(DomElementInterface $elem): self
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->before($elem);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $elem
     *
     * @return self
     */
    public function after(DomElementInterface $elem): self
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->after($elem);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $elem
     * @return self
     */
    public function append(DomElementInterface $elem): self
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->append($elem);
        });

        return $this;
    }

    /**
     * @param DomElementInterface|DomCollectionInterface $elements
     *
     * @return self
     */
    public function prepend($elements): self
    {
        if ($elements instanceof DomCollectionInterface) {
            $elements->each(function (DomElementInterface $elem) {
                $this->elements->each(static function (DomElementInterface $element) use ($elem) {
                    $element->prepend($elem);
                });
            });
        } else {
            $this->elements->each(static function (DomElementInterface $element) use ($elements) {
                $element->prepend($elements);
            });
        }

        return $this;
    }

    /**
     * @return self
     */
    public function empty(): self
    {
        $this->elements->each(static function (DomInterface $element) {
            $element->empty();
        });

        return $this;
    }

    /**
     * @return string
     */
    public function html(): string
    {
        return $this->elements->map(static function (DomInterface $element) {
            return $element->html();
        })
        ->implode('');
    }

    /**
     * @param DomCollectionInterface $collection
     *
     * @return DomCollection
     */
    public function merge(DomCollectionInterface $collection): DomCollection
    {
        return new static(
            $collection->elements()->merge($this->elements)->toArray(),
            $this->selectorConverter
        );
    }

    /**
     * @return Collection
     */
    public function elements(): Collection
    {
        return $this->elements;
    }

    /**
     * @param string $name
     * @param null   $value
     *
     * @return $this|string|null
     */
    public function attr(string $name, $value = null)
    {
        if ($value === null) {
            $first = $this->first();

            if ($first) {
                return $first->attr($name);
            }

            return null;
        }

        foreach ($this->elements as $element) {
            $element->attr($name, $value);
        }

        return $this;
    }
    /**
     * @param DomCollection $collection
     * @param DomCollection $initial
     *
     * @return DomCollection
     */
    private static function reduceCollection(DomCollection $collection, DomCollection $initial): DomCollection
    {
        return $initial->merge($collection);
    }
}
