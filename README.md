# QoboLtd PHRETS
PHP client library for interacting with a RETS server to pull real estate listings,
photos and other data made available from an MLS system.

PHRETS handles the following aspects for you:

* Response parsing (XML, HTTP multipart, etc.)
* Simple variables, arrays and objects returned to the developer
* RETS communication (over HTTP)
* HTTP Header management
* Authentication
* Session/Cookie management

## Permanent Fork
This library is a fork of [troydavisson/PHRETS](https://github.com/troydavisson/PHRETS) which
was forked to:

1. support modern PHP versions
2. Allow the library to be used without too many dependencies.
3. Improve type safety and static analysis.
4. Gather improvements/fixes from other forks into the same codebase.

Includes changes from the following forks:
- [maxlipsky-ca/PHRETS-PHP8](https://github.com/maxlipsky-ca/PHRETS-PHP8)
- [ocusellinc/PHRETS](https://github.com/ocusellinc/PHRETS)
- [okua/PHRETS](https://github.com/okua/PHRETS)

Due to the nature and quantity of the changes we do not expect this fork to be merged back.
We welcome any contributions as long as they are generally useful (ie. not hacks)

### Compatibility
It should be a drop-in replacement for troydavisson/PHRETS with the following exceptions:

1. No support for PHP versions before 8.2.
2. RETS version is now an enum and can only be set in the constructor of the Configuration.
3. The client, cookie jar and logger can only be set in the constructor of the Session.

## Installation

The easiest way to get started is using [Composer](http://getcomposer.org) to install

```js
{
    "repositories": [
        {
          "type": "git",
          "url": "https://github.com/QoboLtd/PHRETS.git"
        }
    ],
    "require": {
        "qoboltd/phrets": "^3.0"
    }
}
```

## Get Help
Please use the GitHub's issue tracker for bugs/suggestions.

## Disclaimer
In many cases, the capabilities provided by this library are dependent on these features being properly implemented by the RETS server you're accessing.  The RETS specification defines how clients and servers communicate, and if a server is doing something unexpected, this library may not work without tweaking some options.

## Documentation

### Quick Start
```php
<?php
require_once("vendor/autoload.php");

use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;
use PHRETS\Session;

date_default_timezone_set('America/New_York');

$config = new Configuration(version: RETSVersion::VERSION_1_7_2);
$config->setLoginUrl('rets login url here')
        ->setUsername('rets username here')
        ->setPassword('rets password here');

$rets = new Session($config);

// If you're using Monolog or any other PSR-3 logger, you can pass it to PHRETS for some additional
// insight into what PHRETS is doing.
//
// $log = new \Monolog\Logger('PHRETS');
// $log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));
// $rets = new Session($config, logger: $log);

$connect = $rets->Login();

$system = $rets->GetSystemMetadata();
var_dump($system);

$resources = $system->getResources();
$classes = $resources->first()->getClasses();
var_dump($classes);

$classes = $rets->GetClassesMetadata('Property');
var_dump($classes->first());

$objects = $rets->GetObject('Property', 'Photo', '00-1669', '*', 1);
var_dump($objects);

$fields = $rets->GetTableMetadata('Property', 'A');
var_dump($fields[0]);

$results = $rets->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => 'LIST_1,LIST_105,LIST_15,LIST_22,LIST_87,LIST_133,LIST_134']);
foreach ($results as $r) {
    var_dump($r);
}
```

#### Configuration

The first step with getting connected to a RETS server is to configure the connection.

```php
$config = new \PHRETS\Configuration(version: \PHRETS\Enums\RETSVersion::VERSION_1_7_2);
$config->setLoginUrl($rets_login_url);
$config->setUsername($rets_username);
$config->setPassword($rets_password);

// optional.  value shown below are the defaults used when not overridden
$config->setUserAgent('PHRETS/2.0');
$config->setUserAgentPassword($rets_user_agent_password); // string password, if given
$config->setHttpAuthenticationMethod('digest'); // or 'basic' if required
$config->setOption('use_post_method', false); // boolean
$config->setOption('disable_follow_location', false); // boolean
```

Available options are:

* `use_post_method` - Always use HTTP POST instead of HTTP GET.  Default is `false` (uses GET)
* `disable_follow_location` - Disable the ability to automatically handle redirects sent by the server.  Default is `false` (follow redirects)

As an alternative, you can also load configuration options from an array:

```php
$config = Configuration::load([
    'login_url' => 'http://loginurlhere',
    'username' => 'rets_username',
    'password' => 'rets_password',
    'user_agent' => 'UserAgent/1.0',
    'user_agent_password' => 'user_agent_password_here',
    'rets_version' => '1.8',
    'http_authentication' => 'basic',
]);
```

#### Connecting

Once the configuration has been setup, a RETS session can be started:

```php
$config = Configuration::load([
    see above for what to give here
]);

$rets = new \PHRETS\Session($config);
```

#### Login

```php
$bulletin = $rets->Login();
```

This will make the first request to the RETS server.  In addition to general authentication, this step is required to finalize the session's configuration.  Within the Login response, RETS servers provide back information needed for all other requests, so this has to be done first.

#### Grabbing Records

> Note: In order to grab records from a RETS server, you need to first know the types of information you're allowed to get and see.  This information is provided through the metadata calls supported by a RETS server, but using a RETS metadata viewer service such as [RETSMD.com](http://retsmd.com/) can make this process much faster unless you have a specific need for having parseable metadata.

With a known RETS Resource, Class and DMQL query, you can issue requests for records:

```php
$results = $rets->Search($resource, $class, $query);

// or with the additional options (with defaults shown)

$results = $rets->Search(
    $resource,
    $class,
    $query,
    [
        'QueryType' => 'DMQL2',
        'Count' => 1, // count and records
        'Format' => 'COMPACT-DECODED',
        'Limit' => 99999999,
        'StandardNames' => 0, // give system names
    ]
);
```

#### Processing Results

The result of a `$rets->Search()` request will return a `\PHRETS\Models\Search\Results` object which can be used, in many ways, like a regular array.  Each item in that array is a `\PHRETS\Models\Search\Record` object representing a single record returned.

```php
$results = $rets->Search( see above for what to give here );

foreach ($results as $record) {
    echo $record['Address'] . "\n";
    // is the same as:
    echo $record->get('Address') . "\n";
}
```

$results can be used in a foreach loop like above, but some additional helper methods exist:

```php
// return an array of the field names to expect with each record
$results->getHeaders();

// return the total number of results found (reported by the RETS server)
$results->getTotalResultsCount();

// return the count of results actually retrieved by PHRETS
$results->getReturnedResultsCount(); // same as: count($results)

// make a RETS GetMetadata call for the metadata for this RETS Resource and Class
$results->getMetadata();

// return whether or not the RETS server has more results to give
$results->isMaxRowsReached();

// return the string of characters to expect as the value of a field the RETS server blocked
$results->getRestrictedIndicator();

// return the first record in the set
$results->first();

// return the last record in the set
$results->last();

// returns an array representing the collected values from the identified field
$all_ids = $results->lists('ListingID');

// export the results in JSON format
json_encode($results);

// export the results in a simple array format
$results->toArray();
```

Because each $record is an object, some helper methods exist:

```php
$record->isRestricted('Address'); // determine if the RETS server blocked this value
$record->getFields(); // return an array of the field names associated with this record
$record->toArray(); // returns a true PHP array of the given record
json_encode($record); // returns a JSON encoded string representing the record
$record->getResource(); // returns the RETS Resource responsible for this record
$record->getClass(); // returns the RETS Class responsible for this record
```

#### Downloading Media (Photos, Images, Documents, etc.)

The returned value from a `$rets->GetObject()` call is an array.
The library provides some helper methods for convenience.

```php
$objects = $rets->GetObject($rets_resource, $object_type, $object_keys);

// grab the first object of the set
\PHRETS\Arr::first($objects);

// grab the last object of the set
\PHRETS\Arr::last($objects);

// throw out everything but the first 10 objects
$objects = array_slice($objects, 0, 10);
```

Each object within the array is a `\PHRETS\Models\BaseObject` object with it's own set of helper methods:

```php
$objects = $rets->GetObject( see above documentation );
foreach ($objects as $object) {
    // does this represent some kind of error
    $object->isError();
    $object->getError(); // returns a \PHRETS\Models\RETSError

    // get the record ID associated with this object
    $object->getContentId();

    // get the sequence number of this object relative to the others with the same ContentId
    $object->getObjectId();

    // get the object's Content-Type value
    $object->getContentType();

    // get the description of the object
    $object->getContentDescription();

    // get the sub-description of the object
    $object->getContentSubDescription();

    // get the object's binary data
    $object->getContent();

    // get the size of the object's data
    $object->getSize();

    // does this object represent the primary object in the set
    $object->isPreferred();

    // when requesting URLs, access the URL given back
    $object->getLocation();

    // use the given URL and make it look like the RETS server gave the object directly
    $object->setContent(file_get_contents($object->getLocation()));
}
```
