# Configuration

## Full config overview

```yaml
lolautruche_payline:
    merchant_id:          ~ # Required
    access_key:           ~ # Required
    contract_number:      ~ # Required
    default_currency:     ~ # One of "EUR"; "DOLLAR"; "CHF"; "POUND"; "CAD", Required
    default_confirmation_route:  ~ # Required
    default_error_route:  ~ # Required
    environment:          HOMO # One of "HOMO"; "PROD"; "INT"; "DEV", Required
    log_level:            warning # One of "debug"; "info"; "notice"; "warning"; "error"; "critical"; "alert"; "emergency"
    proxy:
        host:                 null
        port:                 null
        login:                null
        password:             null
```

## Configuration details

### `merchant_id`
Your Payline merchant ID

### `access_key`
The access key you generated in Payline admin

### `contract_number`
The default contract number to use.
You may override it when creating a transaction.

**Note that it MUST be a string, so use quotes**:
```yaml
lolautruche_payline:
    # ...
    contract_number: "1234567"
```

### `default_currency`
The default currency for transactions.
You may override it when creating a transaction.

Accepted values are:
* `EUR` or `€`
* `DOLLAR` or `$`
* `CHF`
* `POUND` or `£`
* `CAD`

### `default_confirmation_route`
Route name to redirect to when a transaction is successful.

### `default_error_route`
Route name to redirect to when a transaction has failed.

### `environment`
Payline *environment* to use.

Accepted values are:
* `HOMO`, standing for *Homologation*.
  This is the environment you'll most likely use during development. No transaction will be debited.
* `PROD`, standing for *Production*.
  Use it for production only. All transactions will be debited.
* `INT`, standing for *Integration*.
* `DEV`, standing for *Development*.

### `log_level`
Log verbosity level for PaylineSDK.
Logs are located in Symfony logs directory.

### `proxy`
Proxy to use in order to reach Payline SOAP webservices.
`host` / `port` / `login` / `password` will be directly passed to `SOAPClient`.