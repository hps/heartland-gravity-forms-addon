<?php

class HpsGiftCardActivate extends HpsTransaction
{
    /**
     * The HPS gift card activate response
     */

    public $authorizationCode   = null;
    public $balanceAmount       = null;
    public $pointsBalanceAmount = null;

    /**
     * The rewards (dollars or points) added to the account as
     * a result of the transaction.
     *
     * @var null
     */
    public $rewards             = null;

    /**
     * Notes contain reward messages to be displayed on a receipt,
     * mobile app, or web page to inform an account holder about
     * special rewards or promotions available on the account.
     *
     * @var string
     */
    public $notes               = null;

    public static function fromDict($rsp, $txnType, $returnType = 'HpsGiftCardActivate')
    {
        $activationRsp = $rsp->Transaction->$txnType;

        $activation = new $returnType();

        $activation->transactionId = $rsp->Header->GatewayTxnId;
        $activation->authorizationCode = (isset($activationRsp->AuthCode) ? $activationRsp->AuthCode : null);
        $activation->balanceAmount = (isset($activationRsp->BalanceAmt) ? $activationRsp->BalanceAmt : null);
        $activation->pointsBalanceAmount = (isset($activationRsp->PointsBalanceAmount) ? $activationRsp->PointsBalanceAmount : null);
        $activation->rewards = (isset($activationRsp->Rewards) ? $activationRsp->Rewards : null);
        $activation->notes = (isset($activationRsp->Notes) ? $activationRsp->Notes : null);
        $activation->responseCode = (isset($activationRsp->RspCode) ? $activationRsp->RspCode : null);
        $activation->responseText = (isset($activationRsp->RspText) ? $activationRsp->RspText : null);

        return $activation;
    }
}
