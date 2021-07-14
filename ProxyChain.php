<?php

namespace App\Collections;

use ArrayAccess;
use ArrayObject;
use phpDocumentor\Reflection\Types\Void_;

class ProxyChain implements ArrayAccess
{

 /**
  * The underlying object.
  *
  * @var mixed
  */
 protected $value;

 /**
  * The underlying object.
  *
  * @var Collection
  */
 protected Collection $chain;

 /**
  * Create a new optional instance.
  *
  * @param  mixed  $value
  * @return void
  */
 public function __construct($value)
 {
  $this->value = $value;
  $this->chain = collect();
 }

 public function __resolve()
 {
  try {
   foreach ($this->chain as $link) {
    [$operation, $params] = $link;
    switch ($operation) {
     case ('get'):
      $this->value = $this->value->{$params[0]} ?? null;
      break;
     case ('call'):
      $this->value = $this->value->{$params[0]}(...$params[1]);
      break;
     case ('offsetGet'):
      $this->value = $this->value[$params[0]];
      break;
    }
   }
   return $this->value;
  } catch (\Throwable $e) {
   // throw $e;
   return;
  }
 }

 /**
  * Dynamically access a property on the underlying object.
  *
  * @param  string  $key
  * @return self
  */
 public function __get($key)
 {
  $this->chain->push(['get', func_get_args()]);
  return $this;
 }

 /**
  * Dynamically pass a method to the underlying object.
  *
  * @param  string  $method
  * @param  array  $parameters
  * @return self
  */
 public function __call($method, $parameters)
 {
  $this->chain->push(['call', func_get_args()]);
  return $this;
 }

 /**
  * Get an item at a given offset.
  *
  * @param  mixed  $key
  * @return self
  */
 public function offsetGet($key)
 {
  $this->chain->push(['offsetGet', func_get_args()]);
  return $this;
 }

 /**
  * @inheritDoc
  */
 public function offsetExists($key)
 {
  // Only here for offsetGet to work
 }

 /**
  * @inheritDoc
  */
 public function offsetSet($key, $value)
 {
  // Only here for offsetGet to work
 }

 /**
  * @inheritDoc
  */
 public function offsetUnset($key)
 {
  // Only here for offsetGet to work
 }
}
