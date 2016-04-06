# Using Payline SDK

LolautruchePaylineBundle uses [PaylineSDK](https://packagist.org/packages/monext/payline-sdk) in the background,
and defines it as a service.
Using the SDK to consume specific Payline services is as easy as getting the gateway :smile:

```php
// Inside a controller

/** @var \Lolautruche\PaylineBundle\Payline\Payline $paylineGateway */
$paylineGateway = $this->get('payline');

/** @var \Payline\PaylineSDK $paylineSDK */
$paylineSDK = $paylineGateway->getPaylineSDK();
$payineSDK->createWebWallet([/* */]);
```
