<?php
/*
|--------------------------------------------------------------------------
| PHP Data Structures - API Draft - v2.0
|
| Target PHP Version: 7.3.1
|
| https://pecl.php.net/package/ds
|
|--------------------------------------------------------------------------
|
| This file is will be used for planning the API of the next major version of
| the data structures extension. Please feel free to suggest modifications.
*/
namespace Ds;

/**
 * @todo How do we handle comparison? Proposal: == for objects, === otherwise.
 */


/******************************************************************************/
/*                                EXCEPTIONS                                  */
/******************************************************************************/

/**
 * Should be thrown when a structure is accessed unexpectedly, or in a way that
 * is undefined, or to avoid ambiguous results. This is a marker interface to
 * support catching all access exceptions.
 */
interface AccessException {}

/**
 * Should be thrown when an index or key is not within the given access bounds
 * of a structure, such as a negative index on a list or
 */
class IndexOutOfBoundsException extends \OutOfBoundsException implements AccessException  {}

/**
 * Should be thrown when an empty container is accessed a clear, obvious result.
 */
class EmptyContainerException extends \RuntimeException implements AccessException {}

/**
 * Should be thrown when an undefined zval is accessed. I do not except any user
 * classes to throw this, but it might be useful to catch it.
 */
class UndefinedValueException extends \RuntimeException implements AccessException {}

/**
 * Should be thrown when a key is not supported, eg. when an attempt is made to
 * associate a value with NULL in Dictionary, causing ambiguity in `find`.
 */
class InvalidKeyException extends \RuntimeException implements AccessException {}

/**
 * Should be thrown when a value could not be found in a context where it was
 * expected. This should only be used if an operation is undefined if not found.
 */
class ValueNotFoundException extends \RuntimeException implements AccessException {}


/******************************************************************************/
/*                                INTERFACES                                  */
/******************************************************************************/

/**
 * Indicates that a structure contains elements and is aware of the number of
 * items contained at any point in time. How a container must determine the
 * count is implementation-specific.
 *
 * This interface does not imply that a structure is traversable.
 */
interface Container extends \Countable
{
    /**
     * @return bool whether the container is empty, which can be TRUE even when
     *              the number of elements is non-zero.
     */
    function isEmpty(): bool;
}

/**
 * Indicates that the elements of a structure can be represented by an array
 * without altering the structure of the data, without any warnings or errors.
 */
interface Arrayable
{
    /**
     * This method should not fail. There is an implicit expectation that any
     * structure converted to an array must be able to be converted back to
     * the structure in such a way that it is considered equal to the initial.
     */
    function toArray(): array;
}

/**
 * Indicates that a structure can be cleared.
 */
interface Clearable
{
    function clear();
}

/**
 * Indicates that the order of the elements of a structure can be reversed.
 */
interface Reversable
{
    /**
     * @return static A reversed copy or this structure reversed in-place.
     */
    function reverse();
}

/**
 * Indicates that a structure can be sorted by value.
 *
 * This interface does not guarantee that the sorting algorithm will be stable.
 * Implementations should use a domain-specific tiebreaker when required.
 */
interface Sortable
{
    /**
     * @return static A sorted copy, or $this structure sorted in-place.
     */
    function sort(callable $comparator = null);
}

/**
 * Indicates that a structure can be sorted by key.
 *
 * This interface does not guarantee that the sorting algorithm will be stable.
 * Implementations should use a domain-specific tiebreaker when required.
 */
interface SortableKeys
{
    /**
     * @return static A sorted copy, or $this structure sorted in-place.
     */
    function ksort(callable $comparator = null);
}

/**
 * Indicates that an object is designed to be used in hash-based structures.
 */
interface Hashable
{
    /**
     * @return mixed A scalar value (or hashable delegate) that will be hashed.
     */
    function getHashSource();
}

/**
 * Indicates that a structure can be accessed using a zero-based integer index
 * indicating the position of an element from the beginning of the structure.
 *
 * We extend Countable because it should always be possible to determine bounds.
 *
 * @todo Naming - OrdinalAccess, LinearAccess, RandomAccess...
 */
interface OffsetAccess extends \Countable
{
    /**
     * @return mixed The value at the
     *
     * @throws IndexOutOfBoundsException if the index is not within [0, size).
     *
     * @todo Potential for a better name, like "skip" or "atIndex" or "at"?
     */
    function offset(int $index);
}

/**
 * Indicates that a structure can be appended to, ie. add or remove values at
 * the end of the structure.
 */
interface Pushable
{
    /**
     * Appends the given value to the end of the structure.
     */
    function push($value);

    /**
     * Removes and returns the value at the end of the structure.
     *
     * @return mixed The value that was removed from the end of the structure.
     *
     * @throws EmptyContainerException if the structure is empty.
     *
     * @todo should we leave it up to the impl to throw or return NULL? I think
     *       we should aim for consistency by requiring a throw when empty. This
     *       should be consistent with OffsetAccess' first and last.
     */
    function pop();
}

/**
 * Indicates that a structure can be prepended to, ie. add or remove values at
 * the start of the structure.
 */
interface Shiftable
{
    /**
     * Prepends the given value to the front of the structure.
     */
    function unshift($value);

    /**
     * Removes and returns the first value of the structure.
     *
     * @return mixed The value that was removed from the front of the structure.
     *
     * @throws EmptyContainerException if the structure is empty.
     */
    function shift();
}

/**
 * Indicates that a structure has a defined "next" value, and that it can be
 * produces without modifying the contents of the structure.
 */
interface Peekable
{
    /**
     * Returns what is considered the "next" value in a structure.
     *
     * @throws EmptyContainerException
     */
    function peek();
}

/**
 * Indicates that a structure contains a series of contiguous elements.
 */
interface Sequence
{
    /**
     * Returns the value at the given position.
     *
     * @throws IndexOutOfBoundsException if the index is not within [0, size)
     */
    function get(int $index);

    /**
     * @return mixed The first value in $this sequence.
     */
    function first();

    /**
     * @return mixed The last value in $this sequence.
     */
    function last();
}

/**
 * A sequence which can be modified, either in-place or as a copy.
 */
interface MutableSequence extends Sequence
{
    /**
     * Sets the value at the given position.
     *
     * @throws IndexOutOfBoundsException if the index is not within [0, size)
     */
    function set(int $index, $value);

    /**
     * Removes the value at the given position, moving all successive
     * values one position towards the front.
     *
     * @todo Naming - remove, pull?
     *
     * @throws IndexOutOfBoundsException if the index is not within [0, size)
     */
    function unset(int $index);

    /**
     * Moves all values between the given index and the end of the sequence one
     * position towards the back, then inserts the given value into the gap.
     *
     * @throws IndexOutOfBoundsException if the index is out of range [0, size]
     */
    function insert(int $index, $value);
}

/**
 * Indicates that a structure is designed to quickly determine whether a given
 * value is already contained by it.
 *
 * Note: The intention will be that all implementations of Set will use a
 *       do_operation handler to override |, ^, -, &.
 */
interface Set
{
    /**
     * @return bool TRUE if the value is in $this set, FALSE otherwise.
     *
     * @todo Naming - contains?
     */
    function has($value): bool;

    /**
     * Operator: |
     *
     * @todo what about the + operator?
     *
     * @return static A set containing values in both $this and $other.
     *
     * @todo Naming - or, union, merge
     */
    function or(Set $other): Set;

    /**
     * Operator: ^
     *
     * @return static A set containing values in either $this or $other,
     *                but not in both.
     */
    function xor(Set $other): Set;

    /**
     * Operator: -
     *
     * @return static A set containing values in $this but not in $other.
     *
     * @todo Naming - not, diff, without
     */
    function not(Set $other): Set;

    /**
     * Operator: &
     *
     * @return static A set containing values in $this that are also in $other.
     *
     * @todo Naming - and, intersect
     */
    function and(Set $other): Set;
}

/**
 * A set which can be modified, either in-place or as a copy.
 */
interface MutableSet extends Set
{
    /**
     * Adds the given value to $this set if it is not already in $this set.
     */
    function add($value);

    /**
     * @todo what happens when the value could not be found? Silent no-op?
     */
    function remove($value);
}

/**
 * A structure that associates one value with another and provides the ability
 * to query or adjust associations efficiently.
 */
interface Dictionary
{
    /**
     * @todo if NULL keys are not allowed, should we throw if $key is NULL?
     *
     * @return mixed The value associated with the $key, or $default if the key
     *               could not be found and a $default was provided.
     *
     * @throws AccessException if the $key was not found with default given.
     */
    function get($key, $default = null);

    /**
     * @return bool TRUE if $this map has an association for the given $key,
     *              FALSE otherwise.
     *
     * @todo "This is ambiguous! 'has' ...what? has key or has value?"
     *       What about `$map->keys()->has(x)` or `$map->values()->has(x)` if we
     *       can achieve that without intermediaries? That would make the `has`
     *       here a bit redundant, where we would not get much value for the
     *       cost of the ambiguity. The other alternative is hasKey + hasValue,
     *       but that feels more specific than it needs to be.
     */
    function has($key): bool;

    /**
     * @return Set All keys from the map added in traversal order.
     *
     * @todo We have to come up with a clever way to make this lazy, so we can
     *       expose a view on the structure's keys rather than an eager copy.
     */
    function keys(): Set;

    /**
     * @return Set All keys from the map added in traversal order.
     *
     * @todo We have to come up with a clever way to make this lazy, so we can
     *       expose a view on the structure's values rather than an eager copy.
     */
    function values(): Sequence;
}

/**
 * A map which can be modified, either in-place or as a copy.
 */
interface MutableDictionary extends Dictionary
{
    /**
     * Associates the $key with the $value, overriding any previous association.
     *
     * @throws InvalidKeyException if the map implementation does not support
     *                             the given key, eg. NULL
     *
     * @todo Naming - set/put (set/unset symmetry, add/remove, put/pull?)
     */
    function set($key, $value);

    /**
     * Removes the given $key from the map, and does nothing if they key could
     * not be found.
     *
     * @todo Should we return the removed value? (No, ambiguous and two-in-one)
     *
     * @todo Key not found: should we throw, do nothing, or not enforce either?
     */
    function unset($key);
}

/**
 * Indicates that a structure supports tree traversals.
 */
interface TreeTraversal
{
    /**
     * Root, Left, Right
     */
    function preorder(): Traversable;

    /**
     * Left, Root, Right
     */
    function inorder(): Traversable;

    /**
     * Left, Right, Root
     */
    function postorder(): Traversable;

    /**
     * Right, Root, Left
     */
    function outorder(): Traversable;
}


/******************************************************************************/
/*                                 CLASSES                                    */
/******************************************************************************/

/**
 * A low-level memory allocation abstraction which could be useful to build
 * structures in userland that do not want to use arrays or other structures
 * as a buffer.
 *
 * @todo how should we handle elements that are undefined, ie. a new allocation
 *       will have undefined values. Internally these will be IS_UNDEF, but we
 *       should not expose that to userland. Attempting to access an undefined
 *       value will throw an exception.
 *
 * @todo What should clear do? I think that capacity should be preserved, but
 *       all values should be set the their initial state, ie. undefined
 */
final class Allocation implements
    ArrayAccess,
    Clearable
    {
        /**
         * Creates a new allocation using a given capacity.
         */
        public function __construct(int $capacity) {}

        /**
         * @return int The capacity of $this allocation.
         */
        public function capacity(): int {}

        /**
         * Adjusts the capacity of $this allocation, regardless of current capacity.
         *
         * @param int $capacity Resulting capacity, > 0
         */
        public function realloc(int $capacity): void {}
    }

/**
 * List structure, dynamic size, contiguous, no ops at the front.
 */
final class Vector implements
    ArrayAccess,
    Arrayable,
    Container,
    Clearable,
    Sortable,
    Reversable,
    Pushable,
    OffsetAccess,
    MutableSequence
    {
        /**
         * Creates a new vector using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * Double-ended-queue, supports prepend and append, but nothing in-between.
 */
final class Deque implements
    ArrayAccess,
    Arrayable,
    Container,
    Clearable,
    Sortable,
    Reversable,
    Pushable,
    Shiftable,
    OffsetAccess,
    Sequence
    {
        /**
         * Creates a new deque using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * The standard hashtable, implemented as Map in ext-ds currently. It is based
 * on the PHP array but is not identical. We can implement OffsetAccess because
 * insertion order is preserved.
 *
 * @todo Should we implement Arrayable here? We would have to throw if an object
 *       is used as a key, and there will also be inconsistencies eg. "1" and 1.
 */
final class HashMap implements
    ArrayAccess,
    Container,
    Clearable,
    Sortable,
    SortableKeys,
    Reversable,
    OffsetAccess,
    MutableDictionary
    {
        /**
         * Creates a new map using keys and values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * The Set equivalent of HashMap, which might not use a HashMap internally, but
 * will always preserve insertion order.
 */
final class HashSet implements
    ArrayAccess,
    Arrayable,
    Container,
    Clearable,
    Sortable,
    Reversable,
    OffsetAccess,
    Sequence,
    MutableSet
    {
        /**
         * Creates a new set using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * A structure that is always sorted and does not allow duplicate elements.
 *
 * This would be useful for building sorted datasets, or sets without hashing.
 *
 * See: https://github.com/php-ds/ext-ds/issues/121
 */
final class BinarySearchTree implements
    Arrayable,
    Container,
    Clearable,
    OffsetAccess,
    TreeTraversal,
    MutableSet,
    {
        /**
         * Creates a new bst using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * A first-in-last-out structure.
 */
final class Stack implements
    Container,
    Clearable,
    Pushable,
    Peekable
    {
        /**
         * Creates a new stack using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * A first-in-first-out structure.
 */
final class Queue implements
    Container,
    Clearable,
    Pushable,
    Peekable
    {
        /**
         * Creates a new queue using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}
    }

/**
 * Heap-based queue with a mixed-type priority value associated with the key.
 *
 * @todo Should we support duplicate values?
 */
final class PriorityQueue implements
    Container,
    Clearable,
    Pushable,
    Peekable
    {
        /**
         * Creates a new priority queue using values from $iter, if any.
         */
        public function __construct(iterable $iter = []) {}

        /**
         * Adds a value to the queue, using a given priority.
         */
        public function push($value, $priority = null) {}

        /**
         * Updates the priority of the first encountered instance of $value.
         *
         * @throws ValueNotFoundException
         */
        public function update($value, $priority) {}
    }

/**
 * Stable heap with an optional comparator, defaulting to minimum. We could
 * potentially remove the nullable which clears up the min/max ambiguity.
 */
final class Heap implements
    Arrayable,
    Container,
    Clearable,
    Pushable,
    Peekable
    {
        /**
         * Creates a new heap using values from $iter, if any.
         */
        public function __construct(iterable $iter = [], callable $comparator = null) {}
    }


/******************************************************************************/
/*                                 FUNCTIONS                                  */
/******************************************************************************/

/**
 * Shortcut to create a new allocation.
 */
function alloc(int $capacity): Allocation
{
    return new Allocation($capacity);
}

/**
 * Shortcut to create a new sequence.
 */
function seq(...$v): MutableSequence
{
    return new Vector($v);
}

/**
 * Shortcut to create a new deque.
 */
function deque(...$v): Deque
{
    return new Deque($v);
}

/**
 * Shortcut to create a new set.
 */
function set(...$v): MutableSet
{
    return new HashSet($v);
}

/**
 * Shortcut to create a new dictionary.
 */
function dict(...$v): MutableDictionary
{
    return new HashMap($v);
}

/**
 * Shortcut to create a new binary search tree.
 */
function bst(...$v): BinarySearchTree
{
    return new BinarySearchTree($v);
}

/**
 * Shortcut to create a new stack.
 */
function stack(...$v): Stack
{
    return new Stack($v);
}

/**
 * Shortcut to create a new queue.
 */
function queue(...$v): Queue
{
    return new Queue($v);
}

/**
 * Shortcut to create a new priority queue.
 */
function pqueue(...$v): PriorityQueue
{
    return new PriorityQueue($v);
}

/**
 * Shortcut to create a new heap.
 *
 * @todo We can't support variadic values before the comparator, so either we
 *       do not provide this shortcut, or we do not provide a way to initialize.
 */
function heap(callable $comparator = null): Heap
{
    return new Heap([], $comparator);
}
