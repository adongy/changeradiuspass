RADIUS Password changer
=======================

About
=====

This is a quick PHP application to change passwords in a RADIUS database.
It only allows storing as NT-Password, the only non-encrypted type of password that will work with EAP/MSCHAPv2.

One can log in using the current password stored in the RADIUS database, or using CAS.

Supported login password types:
  - Cleartext-Password
  - NT-Password

Requirements
============

  - php >= 5.3.0
  - php-(mysql|postgres|...) (relies on PDO)

The application bundles [Jasig phpCAS][cas] version 1.3.2. If it is installed on your environment it will prefer the environment installed version.

Installing
==========

Copy config.sample.php to config.php, edit as desired.

When using CAS, remember to download the target CAS server certificate.
You can do so by executing:

```bash  
echo | openssl s_client -connect SERVER_URL:443 2>/dev/null | openssl x509 > cachain.pem
```

Warning
=======

This application does *not* work well when using CAS authentication and reverse proxies that change `REQUEST_URI`. This is because `phpCAS` uses `REQUEST_URI` to determine the login page url, but it is modified by the reverse proxy, causing a wrong redirection.

[cas]: https://wiki.jasig.org/display/CASC/phpCAS