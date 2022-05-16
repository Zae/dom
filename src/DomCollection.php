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
 * @implements \ArrayAccess<array-key, DomInterface>
 * @implements \IteratorAggregate<array-key, DomInterface>
 */
class DomCollection implements DomCollectionInterface, ArrayAccess, IteratorAggregate, Countable
{
    use CollectionArrayAccess;
    use CollectionIteratorAggregate;
    use CollectionCountable;
    use Stringable;

    /**
     * @var Collection<array-key, DomElementInterface|DomCollectionInterface>
     */
    private $elements;

    /**
     * @var CssSelectorConverter|null
     */
    private $selectorConverter;

    /**
     * DomCollection constructor.
     *
     * @param DomElementInterface[]|DomCollectionInterface[] $elements
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
        return new self(
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
     * @param DomElementInterface $element
     * @return self
     */
    public function wrap(DomElementInterface $element): self
    {
        $this->elements->each(static function (DomElementInterface $elem) use ($element) {
            $elem->wrap($element);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $element
     * @return self
     */
    public function before(DomElementInterface $element): self
    {
        $this->elements->each(static function (DomElementInterface $elem) use ($element) {
            $elem->before($element);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $element
     *
     * @return self
     */
    public function after(DomElementInterface $element): self
    {
        $this->elements->each(static function (DomElementInterface $elem) use ($element) {
            $elem->after($element);
        });

        return $this;
    }

    /**
     * @param DomElementInterface $element
     * @return self
     */
    public function append(DomElementInterface $element): self
    {
        $this->elements->each(static function (DomElementInterface $elem) use ($element) {
            $elem->append($element);
        });

        return $this;
    }

    /**
     * @param DomElementInterface|DomCollectionInterface $element
     *
     * @return self
     */
    public function prepend($element): self
    {
        if ($element instanceof DomCollectionInterface) {
            $element->each(function (DomElementInterface $elem) {
                $this->elements->each(static function (DomElementInterface $element) use ($elem) {
                    $element->prepend($elem);
                });
            });
        } else {
            $this->elements->each(static function (DomElementInterface $elem) use ($element) {
                $elem->prepend($element);
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
        return new self(
            $collection->elements()->merge($this->elements)->toArray(),
            $this->selectorConverter
        );
    }

    /**
     * @return Collection<array-key, DomElementInterface|DomCollectionInterface>
     */
    public function elements(): Collection
    {
        return $this->elements;
    }

    /**
     * @param string $name
     * @param ?mixed $value
     *
     * @return DomInterface|null|string
     * @psalm-suppress ImplementedReturnTypeMismatch
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
