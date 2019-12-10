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
        $this->selectorConverter = $selectorConverter ?? new CssSelectorConverter();
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
     * @return DomInterface
     */
    public function first(): DomInterface
    {
        return $this
            ->elements
            ->first();
    }

    /**
     * @param callable $callback
     *
     * @return void
     */
    public function each(callable $callback): void
    {
        $this
            ->elements
            ->each($callback);
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
     *
     */
    public function remove()
    {
        $this->elements->each(static function (DomInterface $element) {
            $element->remove();
        });
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
     * @return DomCollection
     */
    public function precedingSiblings(): DomCollection
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
     * @return DomCollection
     */
    public function nextSiblings(): DomCollection
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
     */
    public function wrap(DomElementInterface $wrapper)
    {
        $this->elements->each(static function (DomElementInterface $element) use ($wrapper) {
            $element->wrap($wrapper);
        });
    }

    /**
     * @param DomElementInterface $elem
     */
    public function before(DomElementInterface $elem)
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->before($elem);
        });
    }

    /**
     * @param DomElementInterface $elem
     */
    public function after(DomElementInterface $elem): void
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->after($elem);
        });
    }

    /**
     * @param DomElementInterface $elem
     */
    public function append(DomElementInterface $elem)
    {
        $this->elements->each(static function (DomElementInterface $element) use ($elem) {
            $element->append($elem);
        });
    }

    /**
     * @param DomInterface $elements
     */
    public function prepend(DomInterface $elements): void
    {
        if ($elements instanceof DomCollectionInterface) {
            $elements->each(function ($elem) {
                $this->elements->each(static function (DomElementInterface $element) use ($elem) {
                    $element->prepend($elem);
                });
            });
        } else {
            $this->elements->each(static function (DomElementInterface $element) use ($elements) {
                $element->prepend($elements);
            });
        }
    }

    /**
     *
     */
    public function empty()
    {
        $this->elements->each(static function (DomInterface $element) {
            $element->empty();
        });
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
            $collection->elements->merge($this->elements)->toArray(),
            $this->selectorConverter
        );
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
