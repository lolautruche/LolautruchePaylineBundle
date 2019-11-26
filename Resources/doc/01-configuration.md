# Configuration

## Full config overview

```yaml
lolautruche_payline:
    merchant_id:          ~ # Required. Must be surrounded by quotes as this is a string.
    access_key:           ~ # Required. Must be surrounded by quotes as this is a string.
    contract_number:      ~ # Required. Must be surrounded by quotes as this is a string.
    default_currency:     ~ # One of "EUR"; "DOLLAR"; "CHF"; "POUND"; "CAD", Required
    default_confirmation_route:  ~ # Required
    default_error_route:  ~ # Required
    environment:          HOMO # One of "HOMO"; "PROD"; "INT"; "DEV", Required
    log_level:            warning # One of "debug"; "info"; "notice"; "warning"; "error"; "critical"; "alert"; "emergency"
    # Proxy to use for calling Payline SOAP API.
    proxy:
        host:                 null
        port:                 null
        login:                null
        password:             null
```

## Configuration details

> **Note about sensitive settings:**
> You may want to encrypt sensitive settings such as `merchant_id`, `access_key` or `contract_number`.
>
> To do so, use `bin/console secrets:set` command.
>
> Read more about [Encrypted Secrets Management](https://symfony.com/blog/new-in-symfony-4-4-encrypted-secrets-management).

### `merchant_id`
Your Payline merchant ID

> **MUST** be surrounded by quotes as this must be a string

### `access_key`
The access key you generated in Payline admin

> **MUST** be surrounded by quotes as this must be a string

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
