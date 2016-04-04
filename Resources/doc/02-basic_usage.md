## Basic Usage

Creating a payment is pretty easy and only needs a few steps:

* Create a `WebTransaction` object with all needed information (amount, order reference, order date).
* *(Optional)* Customize the transaction and/or add extra options (e.g. private data, buyer information...).
* Initiate the transaction via the Payline gateway (`payline` service). The gateway will seamlessly call `doWebPayment` service.
* If successful, redirect to the URL provided by Payline gateway.

The user will then fill in his card number to pay on Payline website.

Once validated:
* Payline redirects the user to your application via a route (`lolautruche_payline_back_to_shop`)
  and a controller (`PaylineController::backToShopAction()`) provided by `LolautruchePaylineBundle`.
* `PaylineController` verifies the transaction, using the gateway (`Payline::verifyWebTransaction()`).
  The gateway seamlessly calls `getWebPaymentDetails` service.


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
