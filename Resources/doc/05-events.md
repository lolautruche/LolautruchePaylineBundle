# Using events

LolautruchePaylineBundle exposes several events to give you the opportunity to interact during the payment process.
These events are the following

* `Lolautruche\PaylineBundle\Event\PaylineEvents::PRE_WEB_TRANSACTION_INITIATE`
* `Lolautruche\PaylineBundle\Event\PaylineEvents::POST_WEB_TRANSACTION_INITIATE`
* `Lolautruche\PaylineBundle\Event\PaylineEvents::WEB_TRANSACTION_VERIFY`
* `Lolautruche\PaylineBundle\Event\PaylineEvents::ON_BACK_TO_SHOP`
* `Lolautruche\PaylineBundle\Event\PaylineEvents::ON_NOTIFICATION`


## `PaylineEvents::PRE_WEB_TRANSACTION_INITIATE`

This event is fired just **before** `doWebPayment` webservice is called by the Payline gateway.
Interacting with this event gives you access to the `WebTransaction` object and lets customize it and/or add extra options
to it before calling the service.

Listeners will receive a `Lolautruche\PaylineBundle\Event\WebTransactionEvent` object.


## `PaylineEvents::POST_WEB_TRANSACTION_INITIATE`

This event is fired **after** `doWebPayment` webservice has been called by the Payline gateway.
It lets you check the result returned by Payline and interact with it.

Listeners will receive a `Lolautruche\PaylineBundle\Event\ResultEvent` object.


## `PaylineEvents::WEB_TRANSACTION_VERIFY`

This is event is fired just after `getWebPaymentDetails` webservice was called by the Payline gateway, in order to
verify the payment. It is usually triggered either when getting back to the shop, or when getting an automatic notification
from Payline.

It lets you check the result returned by Payline and interact with it. It is especially useful whe you want to update the
order made by the customer for instance.

Listeners will receive a `Lolautruche\PaylineBundle\Event\ResultEvent` object.


## `PaylineEvents::ON_BACK_TO_SHOP`

This event is fired when getting back from Payline website, after the customer has done his payment,
**and after the transaction has been verified by the gateway**.

It gives you access to the result returned by `getWebPaymentDetails` and **gives you the opportunity to customize the response**
returned by `PaylineController`.

Listeners will receive a `Lolautruche\PaylineBundle\Event\PaymentNotificationEvent` object.

### Example for customizing the response

```php
namespace Acme\TestBundle\EventListener;

use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\PaymentNotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class PaymentListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(RouterInterface $router, SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            PaylineEvents::ON_BACK_TO_SHOP => 'onBackToShop',
        ];
    }

    public function onBackToShop(PaymentNotificationEvent $event)
    {
        $paylineResult = $event->getPaylineResult();

        // Let's assume we have different order types and we want to redirect the user depending on that
        $orderType = $paylineResult->getPrivateData('internal_order_type');

        // Error with payment (cacelation, duplicate, refusal...).
        // Redirect the customer depending on the order type.
        if (!$event->isPaymentSuccessful()) {
            if ($event->isPaymentCanceledByUser()) {
                $this->session->getFlashBag()->add('error', 'Payment was canceled as requested.');
                $event->setResponse(
                    new RedirectResponse(
                        $this->router->generate(
                            $orderType === 'invoice' ? 'invoice_home_route' : 'some_other_route'
                        )
                    )
                );

                return;
            } elseif ($event->isPaymentDuplicate()) {
                $this->session->getFlashBag()->add('error', 'Your payment was canceled as you already paid.');
                $event->setResponse(
                    new RedirectResponse(
                        $this->router->generate(
                            $orderType === 'invoice' ? 'invoice_home_route' : 'some_other_route'
                        )
                    )
                );

                return;
            }

            // If refused, just return and let PaylineController do the work.
            return;
        }

        // Payment success, redirect the user depending on order type.
        $transactionId = $paylineResult->getItem('[transaction][id]');
        $this->session->set('transactionId', $transactionId);
        switch ($typeCommande) {
            case 'invoice':
                $event->setResponse(new RedirectResponse($this->router->generate('invoice_payment_success')));
                break;

            case 'foobar':
                $event->setResponse(new RedirectResponse($this->router->generate('some_other_success_route')));
                break;

            default:
                return;
        }
    }
}
```


## `PaylineEvents::ON_NOTIFICATION`

This event is fired when getting a payment notification from Payline, when using *automatic notification*,
**and after the transaction has been verified by the gateway**. Check your Payline customer admin for this to set up auto notification.

Listeners will receive a `Lolautruche\PaylineBundle\Event\PaymentNotificationEvent` object.
