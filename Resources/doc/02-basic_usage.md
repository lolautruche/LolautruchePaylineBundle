# Basic Usage

## Initiating the payment

Creating a payment is pretty easy and only needs a few steps:

* Create a `WebTransaction` object with all needed information (amount, order reference, order date).
* *(Optional)* Customize the transaction and/or add extra options (e.g. private data, buyer information...).
* Initiate the transaction via the Payline gateway (`payline` service). The gateway will seamlessly call `doWebPayment` service.
* If successful, redirect to the URL provided by Payline gateway.

The user will then fill in his card number to pay on Payline website.

### Example
```php
namespace Acme\TestBundle\Controller;

use Lolautruche\PaylineBundle\Payline\WebTransaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function doPaymentAction($orderId)
    {
        // Create the transaction
        $transaction = new WebTransaction(
            3000, // Payment amount, in the smallest currency unit. Here 30.00€ (or whatever your currency is).
            $orderId, // Reference to the order. Must be unique as it used for duplicate control.
            new DateTime() // Order date
        );
        // Add extra options to pass to doWebPayment webservice. Use options as described in Payline documentation.
        $transaction
            ->addExtraOption('buyer', [
                'email' => 'customer@foo.com',
                'customerId' => 'customer_id',
                'firstName' => 'customer_first_name',
                'lastName' => 'customer_last_name',
            ])
            ->addPrivateData('someInternalVariable', '123xyz456') // This private data will be returned as is by Payline after payment.
        ;

        // Initiate the transaction
        $payline = $this->get('payline');
        /** @var \Lolautruche\PaylineBundle\Payline\PaylineResult $result */
        $result = $payline->initiateWebTransaction($transaction);
        if (!$result->isSuccessful()) {
            throw new \RuntimeException('Payline error: $result->getLongMessage()', $result->getCode());
        }

        // And redirect to Payline platform
        return $this->redirect($result->getItem('redirectURL'));
    }
}
```

You may also [customize the transaction](04-advanced_customize_transaction), e.g. to add private data, change the currency,
add customer information...


## Controlling the payment
Once customer's payment has been validated on Payline platform, the following workflow is triggered:

* Payline redirects the user to your application via a route (`lolautruche_payline_back_to_shop`)
  and a controller (`PaylineController::backToShopAction()`) provided by `LolautruchePaylineBundle`.
* `PaylineController` verifies the transaction, using the gateway (`Payline::verifyWebTransaction()`).
  The gateway seamlessly calls `getWebPaymentDetails` service.

After the gateway verified the transaction, it lets you the ability to trigger custom actions, like updating the order
for instance. To let you do that, `PaylineEvents::WEB_TRANSACTION_VERIFY` event is fired just after the gateway has called
the verification service (`getPaymentDetails`). All you have to do is to implement a event listener or subscriber for this
event.

Each listener will receive a `\Lolautruche\PaylineBundle\Event\ResultEvent` object, which contains the transaction verification
result contained in a `\Lolautruche\PaylineBundle\Payline\PaylineResult` instance. The result object contains the whole
result hash, and let you check if it is successful, canceled or if the transaction was a duplicate
(same order reference with same amount than in a previous transaction).

You can access to elements in the hash using `\Lolautruche\PaylineBundle\Payline\PaylineResult::getItem()`, passing it a
path using PropertyAccess notation.

> You can find all available items in
> [Payline `getWebPaymentDetails` service documentation](https://support.payline.com/hc/en-us/articles/201080786-Description-of-web-service-APIs-used-by-the-Payline-payment-solution).

[Private data that you may have set](04-advanced_customize_transaction#private-data) are also accessible using
`\Lolautruche\PaylineBundle\Payline\PaylineResult::getPrivateData()`.


```php
<?php

namespace Acme\TestBundle\EventListener;

use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\ResultEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentListener implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            PaylineEvents::WEB_TRANSACTION_VERIFY => 'onTransactionVerify',
        ];
    }

    public function onTransactionVerify(ResultEvent $event)
    {
        // You can access to the result object from the transaction verification.
        /** @var \Lolautruche\PaylineBundle\Payline\PaylineResult $paylineResult */
        $paylineResult = $event->getResult();
        $transactionId = $paylineResult->getItem('[transaction][id]');

        if (!$paylineResult->isSuccessful()) {
            if ($paylineResult->isCanceled()){
                $this->logger->info("Transaction #$transactionId was canceled by user", ['paylineResult' => $paylineResult->getResultHash()]);
            }
            elseif ($paylineResult->isDuplicate()){
                $this->logger->warning("Transaction #$transactionId is a duplicate", ['paylineResult' => $paylineResult->getResultHash()]);
            }
            else {
                $this->logger->error("Transaction #$transactionId was refused by bank.", ['paylineResult' => $paylineResult->getResultHash()]);
            }

            return;
        }

        // Transaction was validated, do whatever you need to update your order
        // ...

        // Assuming you have set a private data with "internal_id" key when initiating the transaction.
        $internalId = $paylineResult->getPrivateData('internal_id');
        $this->logger->info("Transaction #$transactionId is valid. Internal ID is $internalId");
    }
}
```
