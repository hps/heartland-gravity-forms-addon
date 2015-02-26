<?php

class HpsGiftCardAlias extends HpsTransaction
{
    /**
     * The Hps gift card alias response.
     */

    public $giftCard = null;

    public static function fromDict($rsp, $txnType, $returnType = 'HpsGiftCardAlias')
    {
        $item = $rsp->Transaction->$txnType;

        $alias = new HpsGiftCardAlias();
        $alias->transactionId = $rsp->Header->GatewayTxnId;
        $alias->giftCard = new HpsGiftCard($item->CardData);
        $alias->responseCode = (isset($item->RspCode) ? $item->RspCode : null);
        $alias->responseText = (isset($item->RspText) ? $item->RspText : null);

        return $alias;
    }
}
