# Enumerable Type

**Strongly typed** implementation of enumerable type in PHP which helps to write a safer more readable code.

## Why it was created at all?

 Currently there are many implementations of enum, but they are missing a core feature - **strong typing support**.  
 If you'll look at your code you'll see that most business logic operations will depend on types which has predefined options.     
 Will it be order status, flight status, purchase status, ticket type, company type. 
 Many time business logic would be like so if your ticket type is "priority" and purchase status is "Completed" do some stuff. 

 Using this library your business logic in PHP code will look like this:
 ```php
    if (($ticket->getType() === TicketType::Priority()) && 
        ($purchase->getStatus() === PurchaseStatus::Completed()) {
        // do your logic here...
    }
        
 ``` 
 
 Other example is that your logic almost every time depends on a specific enumerable type and you almost 100% want  
 to be sure that somebody will give you a correct value of correct enumerable type. How many times you've debugged why 
 it's not working because of given option was the same as your expected for enum, but got a completely DIFFERENT enum 
 which contained same option (not same option conceptually, but the same literal string or integer)? 
 
 ```php
 public function someMethod(CompanyType $companyType) {
  // ...
 }
 
 // usage:
 someMethod(CompanyType::Private());
 someMethod(CompanyType::fromId('private'));
 ```

 There is impossible to give any other object, but just CompanyType (e.g. not PersonType or RepositoryType
 which would have also the same 'private', 'public' options).
 
 These enum options is truly **objects** of specific type and as many times you'll call `CompanyType::Private()` you'll
 get the same object which express "Private" option of enumerable type `CompanyType` so you can safely compare these
 objects by it's reference. e.g. `CompanyType::Private() === CompanyType::Private()` always is true.
 
 This was designed in a way that enum would look like it's a default PHP language construct, easy to create and easy to use.


## What's benefits?

* No hardcoded literals in your code
* Allows to type hint any argument of method to accept only a specific type of enumerable
* All getters and return types can be hinted to return a specific enumerable
* Single point of how these enum objects could be created. e.g. only by CompanyType::fromId($companyTypeId)
* Team in project can think in terms of enumerable types instead of strings/ints
* Full IDE support for Find Usages, Refactoring, etc.
* Easy enumeration of available enumerable types (e.g. CompanyType::enum() returns an array of available CompanyType objects)


## Is it production ready?

Yes, this code was written more than a year ago and it's still running in production.


## Easy to use

Just create a class which you want to be enumerable and extend `EnumerableType`.
```php
    class CompanyType extends EnumerableType {
       final public static Unknown() { return static::get(null); }       
       final public static Private() { return static::get('private'); }       
       final public static Public() { return static::get('public'); }
    }
    
    class PaymentMethod extends EnumerableType {
       final public static Unknown() { return static::get(null); }       
       final public static Cash() { return static::get('cash'); }       
       final public static CreditCard() { return static::get('credit_card'); }
    }
    
    class DeliveryStatus extends EnumerableType {
       final public static Delivered() { return static::get(1, 'delivered'); }       
       final public static NotDelivered() { return static::get(0, 'not_delivered'); }       
    }
```
That`s it! No more constants or primitives just valid **objects** which supports **strong typing**.


## API documentation

To create a new enumerable type you need to extend `EnumerableType` class and add as much methods as you need options for that type.  
Methods must be in format:  
`final public static YourOption() { return static::get('your_option_id', 'your_option_name'); }`.

`Your_option_name` argument is optional, if it's not specified then option `name` will be equal to `id`.


Your created classes will contain only two additional static methods:
 
* CompanyType::fromId($id) - Method returns an **option object** which represents a specific option of CompanyType
* CompanyType::enum()      - Method will return an array of all options(as **option objects**) available

Also you should use your added `final public static` methods to retrieve an **option objects**.

Any  **option object** contains two methods:

* id() - returns an option id ('your_option_id') e.g. `CompanyType::Private()->id()` 
* name() - returns an option name ('your_option_name') e.g. `CompanyType::Private()->name()`

P.S. option id and option name is not restricted just to `string` types, just pass to your static::get(...) what's needed.


## Explanation of workings

**Option objects** will be instances of a given enumerable type .e.g. CompanyType::Private() will return object of CompanyType which represents "Private" option.  
Notice: Option objects are created only once per each option and will be `1:1` representation of an option. It means that there impossible to have two objects in a system which represents the same enumerable option. This library uses objects as identities of specific options.

By holding that identity rule we can see these **option objects** as a **subtypes** of type of **CompanyType** (analog to: subclass of class, but no need to have a real extended classes).  
Confused? Ok, lets try again, here is an alternative implementation of `EnumerableType` using just classes:
```php
	class CompanyEnum { }
    class CompanyEnum_Private extends CompanyEnum {}
    class CompanyEnum_Public extends CompanyEnum {}
    class CompanyEnum_Unknown extends CompanyEnum {}
```
By having this structure the same results can be achieved:
```php
    function fromId($id) {
    	switch ($id) {
        	case 'private': return new CompanyEnum_Private();
            case 'public': return new CompanyEnum_Public();
            case null: return new CompanyEnum_Unknown();
            default: throw new \RuntimeException("unhandled {$id}");
        }
    }
    
	function doSomething(CompanyEnum $companyEnum) {
         if ($companyEnum instanceof CompanyEnum_Private) {
           // ...
         }
    }
```
But in this implementation, identity of specific option is based on **class name** rather than an objects itself.  
And if an object can represent exactly one option in the same way as in this example subclass represents one option then we can apply the same semantics of "instanceof" without any subclasses, but just by using strict equality (`===`) and avoiding any subclassing at all.


## Installation

    `composer require happy-types/enumerable-type`

## Code convention: UPPERCASE vs PascalCase

Probably you've just seen an "awkward"(not familiar in PHP, but default in C#) method naming in this library examples, but that was chosen intentionally.

There was a need in a team to somehow mark this "new" style of pattern in a way that any PHP developer could clearly identify where enumerable type logics is playing in code.

There was a couple of options, one was UPPERCASE_NOTATION. But UPPERCASE variables already has a meaning - it's constants.  
Also writing method in all uppercase is really strange e.g. `final public static function DO_NOT_NEED_INSPECTION(){ }`.  
It also reminds me an old `C/C++` times (underscored variables and macros in uppercase...).

What's left?

Regular method naming - camelCased.  
Regular method naming is an option, but that doesn't express a fact that our methods is somewhat special - every method returns only one unique object - **option object**.

So to express the fact that these methods returns **option objects** we've chosen to use camelCase with first case uppered also known as - PascalCase.

Therefore, PascalCase it's just recommended coding style which we are using in our team internally. Important is to choose one and be consistent with it.
    
        
## Extras

Here are some other best practices which we've developed in using `EnumerableType`:

### Don't use default php serialize/unserialize for EnumerableType

Proper serialization using default PHP facility is currently impossible as we cannot force object uniqueness. 
Each time then PHP deserializes an object it firstly will create an object and then sets a values and we must 
somehow ensure that for a given option value would be only unique object in system. Currently it's impossible in PHP. 
Use custom serialization in your classes for a variables which is a type of `EnumerableType`.

 
### All class getters/setters must use EnumerableType instead of string/int

Many PHP ORM supports integer or string fields to be easily persisted in database, but not a custom class objects.
So there is a need to wrap an `EnumerableType` objects in your class's getter/setter.

```php
class Company {
   /**
   * @var int
   */
   private $companyTypeId;
   
   public function setCompanyType(CompanyType $value)
   {
      $this->companyTypeId = $value->id();
   }
   
   /**
   * @return CompanyType
   */
   public function getCompanyType()
   {
      return CompanyType::fromId($this->companyTypeId);
   }
}
```

### All logic where is EnumerableType used must handle all available situations (all available options)

Best way to do it is using `switch` statement plus `default` case condition.
```php
   switch ($companyType) {
      case CompanyType::Private():
         // ... do logic..
         break;
      case CompanyType::Public():
         // ... do logic..
         break;
      default:
         throw new \RuntimeException("unhandled case:" . $companyType->name());
   }
```
That style of code will help you in the future then new type option comes to the scene.


### Don't use id or name in your code directly. Instead compare your objects directly using strict equality

Library is written in a way that you'll get an unique *object* per each option. And objects will be the same 
for the same option, so you can compare it directly.

```php
// BAD CODE:
   if ($companyType->id() === CompanyType::Private()->id()) { /*...*/ }
// AWFUL CODE:
   if ($companyType->id() === 'private') { /*...*/ }
```

```php
// GOOD CODE:
   if ($companyType === CompanyType::Private()) { /*...*/ }
```

### You can easily get all available options in your EnumerableType. Don't enumerate manually

There are situations where is a need to list all available options of your enum.

```php
// BAD CODE:
   $companyTypes = [CompanyType::Private(), CompanyType::Public()];
```

```php
// GOOD CODE:
   $companyTypes = CompanyType::enum();
   foreach ($companyTypes as $companyType) {
        echo $companyType->name();
   }
```

### There is no need to create manual factories of EnumerableType, just use "fromId".
 
```php
// BAD CODE:
   switch ($companyTypeId) {
       case 'private': return CompanyType::Private();
       case 'public': return CompanyType::Public();
       default: return null;
   }
```

```php
// GOOD CODE:
    $companyType = CompanyType::fromId($companyTypeId);
```
 
### Actually, it's better to create "Unknown" option in your enumerable type than use "null" value.

On your classes getters/setters which uses enumerable type there is no point to return nulls at all.
Better create "Unknown" option in your enum. That way you can write nicer code in future.

```php
// BAD CODE:
    class PaymentMethod extends EnumerableType {
       final public static Cash() { return static::get('cash'); }       
       final public static CreditCard() { return static::get('credit_card'); }
    }
    function getPaymentMethod() {
       return $id ? PaymentMethod::fromId($id) : null;
    }
```

```php
// GOOD CODE:
    class PaymentMethod extends EnumerableType {
       final public static Unknown() { return static::get(null); }       
       final public static Cash() { return static::get('cash'); }       
       final public static CreditCard() { return static::get('credit_card'); }
    }
    function getPaymentMethod() {
       return PaymentMethod::fromId($id);
    }
```

## Ending

By this contribution I'm expecting that one day the usage of enumerable types in PHP will be standardized.

Happy Types!
And happy coding!


Antanas A. (antanas.arvasevicius@gmail.com)
