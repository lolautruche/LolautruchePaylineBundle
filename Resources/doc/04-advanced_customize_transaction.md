# Customize the transaction

The `WebTransaction` is the value object you need to create in order to generate a payment.
It holds values that will be passed to `doWebPayment` Payline service.


## Properties

Data can be set in `WebTransaction` properties using setters. Please [check PHPDoc for more information](/Payline/WebTransaction.php).

Using `WebTransaction` properties, you can override configuration values such as the contract number or the currency:

```php
use Lolautruche\PaylineBundle\Payline\WebTransaction

$transaction = new WebTransaction('order-12345', 3000, new \DateTime());
$transaction
    ->setCurrency(WebTransaction::DOLLAR)
    ->setContractNumber('1234567');
```

### Payment and Order properties
Some properties seem to be duplicate, which can be confusing:

* `$amount` / `$orderAmount`
* `$date` / `$orderDate`
* `$currency` / `$orderCurrency`

The difference between those is explained in
[Payline webservice documentation](https://support.payline.com/hc/en-us/articles/201080786-Description-of-web-service-APIs-used-by-the-Payline-payment-solution).

By default the gateway will set the same value for similar properties, unless you explicitly set different values.


## Extra options

All options for `doWebPayment` service are not reflected as properties (there is a lot!), but you can set specific options
using `WebTransaction::addExtraOption()`. First argument is a path using [PropertyAccess notation](http://symfony.com/doc/current/components/property_access/introduction.html),
the second one is the value.

Payline webservice documentation describes options with a *dot syntax* (e.g. `payment.amount`).
However, Payline SDK expects them as a hash. Using `WebTransaction::addExtraOption()`, you can use PropertyAccess notation
to easily set values to the desired options.

```php
use Lolautruche\PaylineBundle\Payline\WebTransaction

$transaction = new WebTransaction('order-12345', 3000, new \DateTime());
$transaction
    ->addExtraOption('[buyer][email]', 'customer@foo.com')
    ->addExtraOption('[order][deliveryMode]', 4);
```

> If you want to set a first level option (e.g. `languageCode`), you may omit the square brackets `[]` around the option name.


## Private data

Often you will be in the situation where you need to keep *private* information from the order and/or the customer
during the transaction process.
This is possible using **private data**, which consist in a list of simple key/value pairs.
These private data will be returned by Payline and will be accessible in `PaylineResult` object returned by the gateway.

> [Learn how to retrieve and use `PaylineResult` to control the payment, after getting back to your shop](05-advanced_control_payment_verification.md).

```php
use Lolautruche\PaylineBundle\Payline\WebTransaction

$transaction = new WebTransaction('order-12345', 3000, new \DateTime());
$transaction
    ->addPrivateData('order.type', 'service')
    ->addPrivateData('internal_id', '1234xyz');
```

> Private data key and value is limited to **50 alphanumeric characters length**.

