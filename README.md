# LolautruchePaylineBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4d54fe75-e1ee-4c67-94b6-7db9bbbda418/big.png)](https://insight.sensiolabs.com/projects/4d54fe75-e1ee-4c67-94b6-7db9bbbda418)
[![Build Status](https://travis-ci.org/lolautruche/LolautruchePaylineBundle.svg?branch=master)](https://travis-ci.org/lolautruche/LolautruchePaylineBundle)

Integrates [Payline payment solution](http://www.payline.com/) with Symfony.


## Features

* Service integration and simple semantic configuration
* Simplified API for web payments
* Automatically validates web payments
* Extensibility using events


## Requirements

### Payline account
You will of course need a valid Payline account.

Mandatory elements from you Payline account are:
* **Merchant ID**
* **Access key**, which you can generate in Payline admin
* **Contract number**, related to the means of payment you configured in Payline admin

### PHP
* PHP >=7.2.5
* [PHP SOAP extension](http://php.net/soap) for Payline SDK

### Symfony
Symfony 4.4 / 5.x

> For support of earlier versions of Symfony, refer to:
> - [2.0](https://github.com/lolautruche/LolautruchePaylineBundle/tree/2.0) (Symfony 3.4 / 4.x) with Symfony Flex support
> - [1.1](https://github.com/lolautruche/LolautruchePaylineBundle/tree/1.1) (Symfony 2.7 / 3.x)


## Installation

This bundle is installable with [Symfony Flex](https://flex.symfony.com).
You first need to allow contrib recipes before requiring the package:

```
composer config extra.symfony.allow-contrib true
composer req lolautruche/payline-bundle
```

Everything will be pre-configured for you; however, ensure to **Encrypt sensitive environment variables**,
e.g. `PAYLINE_MERCHANT_ID` and `PAYLINE_ACCESS_KEY` with [`secrets:set` command](https://symfony.com/blog/new-in-symfony-4-4-encrypted-secrets-management):

````bash
php bin/console secrets:set PAYLINE_MERCHANT_ID
php bin/console secrets:set PAYLINE_ACCESS_KEY
````


## Documentation

See [Resources/doc/](Resources/doc/00-index.md)
