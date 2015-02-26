<?php

class HpsReportTransactionDetails extends HpsAuthorization
{
    public $issuerTransactionId   = null;
    public $issuerValidationCode  = null;
    public $originalTransactionId = null;
    public $maskedCardNumber      = null;
    public $settlementAmount      = null;
    public $transactionType       = null;
    public $transactionUTCDate    = null;
    public $exceptions            = null;
    public $memo                  = null;
    public $invoiceNumber         = null;
    public $customerId            = null;

    public static function fromDict($rsp, $txnType, $returnType = 'HpsReportTransactionDetails')
    {
        $reportResponse = $rsp->Transaction->$txnType;

        $details = parent::fromDict($rsp, $txnType, $returnType);
        $details->originalTransactionId = (isset($reportResponse->OriginalGatewayTxnId) ? $reportResponse->OriginalGatewayTxnId : null);
        $details->authorizedAmount = (isset($reportResponse->Data->AuthAmt) ? $reportResponse->Data->AuthAmt : null);
        $details->maskedCardNumber = (isset($reportResponse->Data->MaskedCardNbr) ? $reportResponse->Data->MaskedCardNbr : null);
        $details->authorizationCode = (isset($reportResponse->Data->AuthCode) ? $reportResponse->Data->AuthCode : null);
        $details->avsResultCode = (isset($reportResponse->Data->AVSRsltCode) ? $reportResponse->Data->AVSRsltCode : null);
        $details->avsResultText = (isset($reportResponse->Data->AVSRsltText) ? $reportResponse->Data->AVSRsltText : null);
        $details->cardType = (isset($reportResponse->Data->CardType) ? $reportResponse->Data->CardType : null);
        $details->descriptor = (isset($reportResponse->Data->TxnDescriptor) ? $reportResponse->Data->TxnDescriptor : null);
        $details->transactionType = (isset($reportResponse->ServiceName) ? HpsTransaction::serviceNameToTransactionType($reportResponse->ServiceName) : null);
        $details->transactionUTCDate = (isset($reportResponse->RspUtcDT) ? $reportResponse->RspUtcDT : null);
        $details->cpcIndicator = (isset($reportResponse->Data->CPCInd) ? $reportResponse->Data->CPCInd : null);
        $details->cvvResultCode = (isset($reportResponse->Data->CVVRsltCode) ? $reportResponse->Data->CVVRsltCode : null);
        $details->cvvResultText = (isset($reportResponse->Data->CVVRsltText) ? $reportResponse->Data->CVVRsltText : null);
        $details->referenceNumber = (isset($reportResponse->Data->RefNbr) ? $reportResponse->Data->RefNbr : null);
        $details->responseCode = (isset($reportResponse->Data->RspCode) ? $reportResponse->Data->RspCode : null);
        $details->responseText = (isset($reportResponse->Data->RspText) ? $reportResponse->Data->RspText : null);

        if (isset($reportResponse->Data->TokenizationMsg)) {
            $details->tokenData = new HpsTokenData();
            $details->tokenData->responseMessage = $reportResponse->Data->TokenizationMsg;
        }

        if (isset($reportResponse->Data->AdditionalTxnFields)) {
            $additionalTxnFields = $reportResponse->Data->additionalTxnFields;
            $details->memo = (isset($additionalTxnFields->Description) ? $additionalTxnFields->Description : null);
            $details->invoiceNumber = (isset($additionalTxnFields->InvoiceNbr) ? $additionalTxnFields->InvoiceNbr : null);
            $details->customerId = (isset($additionalTxnFields->CustomerId) ? $additionalTxnFields->CustomerId : null);
        }

        if ($reportResponse->Data->RspCode != '00') {
            if ($details->exceptions == null) {
                $details->exceptions = new HpsChargeExceptions();
            }

            $details->exceptions->issuerException = HpsIssuerResponseValidation::getException(
                $rsp->Header->GatewayTxnId,
                $reportResponse->Data->RspCode,
                $reportResponse->Data->RspText
            );
        }

        return $details;
    }
}
