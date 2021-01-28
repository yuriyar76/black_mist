<?php


/**
 * Interface InvoiceInterface
 */
interface InvoiceInterface
{

    public function getBaseInv();

    public function makeReturnInvoice();

    public function makeInvoicePDF();

    public function callingCourier();

    public function sendMailCallCourier();
}