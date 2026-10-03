# TwittPay for Paid Memberships Pro

Paid Memberships Pro payment gateway

Part of the [TwittPay](https://twittpay.com) addon family.

## Quick start

1. Download the latest zip from the **Releases** page of this repository.
2. Install it on your Paid Memberships Pro following the guide below.
3. Open the TwittPay settings and enter your **Brand Key**. You can get it from [your dashboard](https://twittpay.com/user/brands).
4. Make a small test payment to confirm everything works.

Payments are always re-verified on your server before an order or invoice is marked paid.

## Detailed installation guide

```text
===========================================================================
 TwittPay - Paid Memberships Pro gateway
===========================================================================

 INSTALL
   1. WordPress admin > Plugins > Add New > Upload Plugin, choose the zip.
   2. Activate "Paid Memberships Pro - TwittPay Gateway".
      Paid Memberships Pro must be active.
   3. Memberships > Settings > Payment Gateway & SSL:
        Payment Gateway    TwittPay
        Currency           BDT
        Brand Key          from your TwittPay dashboard, under Brands
   4. Save, then make one small test payment.

 NOTES
   * TwittPay takes BDT. Set the membership currency to BDT.
   * Every payment is verified again on your server, must match the order and
     its amount, and an order is completed only once.
   * Needs PHP 7.4 or newer.
```
