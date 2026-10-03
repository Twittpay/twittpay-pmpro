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
