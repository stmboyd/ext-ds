#ifndef DS_DEQUE_H
#define DS_DEQUE_H

#include "../common.h"

#define DS_DEQUE_MIN_CAPACITY 8 // Must be a power of 2

#define DS_DEQUE_SIZE(d)     ((d)->size)
#define DS_DEQUE_IS_EMPTY(d) ((d)->size == 0)

#define DS_DEQUE_FOREACH(_d, _v)                            \
do {                                                        \
    ds_deque_t  *_deque   = _d;                             \
    zval        *_buffer  = _deque->buffer;                 \
    zend_long    _tail    = _deque->tail;                   \
    zend_long    _mask    = _deque->capacity - 1;           \
    zend_long    _head    = _deque->head;                   \
                                                            \
    for (; _head != _tail; _head = (_head + 1) & _mask) {   \
        _v = _buffer + _head;

#define DS_DEQUE_FOREACH_END() \
    } \
} while (0)

typedef struct _ds_deque_t {
    zval      *buffer;
    zend_long  capacity;
    zend_long  head;
    zend_long  tail;
    zend_long  size;
} ds_deque_t;

ds_deque_t *ds_deque();
ds_deque_t *ds_deque_ex(zend_long capacity);
ds_deque_t *ds_deque_clone(ds_deque_t *src);
ds_deque_t *ds_deque_from_buffer(zval *buffer, zend_long size);

void ds_deque_clear(ds_deque_t *deque);
void ds_deque_destroy(ds_deque_t *deque);
void ds_deque_allocate(ds_deque_t *deque, zend_long capacity);

void ds_deque_push(ds_deque_t *deque, zval *value);
void ds_deque_push_va(ds_deque_t *deque, VA_PARAMS);
void ds_deque_push_all(ds_deque_t *deque, zval *values);

void ds_deque_set(ds_deque_t *deque, zend_long index, zval *value);
void ds_deque_pop(ds_deque_t *deque, zval *return_value);
void ds_deque_shift(ds_deque_t *deque, zval *return_value);
void ds_deque_find(ds_deque_t *deque, zval *value, zval *return_value);
void ds_deque_remove(ds_deque_t *deque, zend_long index, zval *return_value);
void ds_deque_insert_va(ds_deque_t *deque, zend_long index, VA_PARAMS);
void ds_deque_unshift_va(ds_deque_t *deque, VA_PARAMS);

zval *ds_deque_get(ds_deque_t *deque, zend_long index);
zval *ds_deque_get_last(ds_deque_t *deque);
zval *ds_deque_get_first(ds_deque_t *deque);

bool ds_deque_contains_va(ds_deque_t *deque, VA_PARAMS);
bool ds_deque_isset(ds_deque_t *deque, zend_long index, int check_empty);

ds_deque_t *ds_deque_map(ds_deque_t *deque, FCI_PARAMS);
ds_deque_t *ds_deque_filter(ds_deque_t *deque);
ds_deque_t *ds_deque_filter_callback(ds_deque_t *deque, FCI_PARAMS);
ds_deque_t *ds_deque_slice(ds_deque_t *deque, zend_long index, zend_long length);
ds_deque_t *ds_deque_merge(ds_deque_t *deque, zval *values);
ds_deque_t *ds_deque_reversed(ds_deque_t *deque);

void ds_deque_join(ds_deque_t *deque, char *str, size_t len, zval *return_value);
void ds_deque_reduce(ds_deque_t *deque, zval *initial, zval *return_value, FCI_PARAMS);
void ds_deque_rotate(ds_deque_t *deque, zend_long rotations);
void ds_deque_sort_callback(ds_deque_t *deque);
void ds_deque_sort(ds_deque_t *deque);
void ds_deque_reverse(ds_deque_t *deque);
void ds_deque_to_array(ds_deque_t *deque, zval *return_value);
void ds_deque_apply(ds_deque_t *deque, FCI_PARAMS);
void ds_deque_sum(ds_deque_t *deque, zval *return_value);

#endif