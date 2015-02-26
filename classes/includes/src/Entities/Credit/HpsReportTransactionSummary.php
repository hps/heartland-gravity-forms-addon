<?php

class HpsReportTransactionSummary extends HpsTransaction
{
    public $amount                = null;
    public $settlementAmount      = null;
    public $originalTransactionId = null;
    public $maskedCardNumber      = null;
    public $transactionType       = null;
    public $transactionUTCDate    = null;
    public $exceptions            = null;

    public static function fromDict($rsp, $txnType, $filterBy = null, $returnType = 'HpsReportTransactionSummary')
    {
        $transactions = array();

        if ($rsp->Transaction->ReportActivity->Header->TxnCnt == "0") {
            return $transactions;
        }

        if ($filterBy != null && is_string($filterBy)) {
            $filterBy = HpsTransaction::serviceNameToTransactionType($filterBy);
        }

        $summary = null;
        $serviceName = (isset($filterBy) ? HpsTransaction::transactionTypeToServiceName($filterBy) : null);

        foreach ($rsp->Transaction->ReportActivity->Details as $charge) {
            if (isset($serviceName) && $serviceName != $charge->ServiceName) {
                continue;
            }

            $summary = new HpsReportTransactionSummary();

            // Hydrate the header
            $summary->_header = new HpsTransactionHeader();
            $summary->_header->gatewayResponseCode = $charge->GatewayRspCode;
            $summary->_header->gatewayResponseMessage = $charge->GatewayRspMsg;

            $summary->transactionId = $charge->GatewayTxnId;

            $summary->originalTransactionId = (isset($charge->OriginalGatewayTxnId) ? $charge->OriginalGatewayTxnId : null);
            $summary->maskedCardNumber = (isset($charge->MaskedCardNbr) ? $charge->MaskedCardNbr : null);
            $summary->responseCode = (isset($charge->IssuerRspCode) ? $charge->IssuerRspCode : null);
            $summary->responseText = (isset($charge->IssuerRspText) ? $charge->IssuerRspText : null);
            $summary->amount = (isset($charge->Amt) ? $charge->Amt : null);
            $summary->settlementAmount = (isset($charge->SettlementAmt) ? $charge->SettlementAmt : null);
            $summary->transactionType = (isset($charge->ServiceName) ? HpsTransaction::serviceNameToTransactionType($charge->ServiceName) : null);
            $summary->transactionUTCDate = (isset($charge->TxnUtcDT) ? $charge->TxnUtcDT : null);

            $gwResponseCode = (isset($charge->GatewayRspCode) ? $charge->GatewayRspCode : null);
            $issuerResponseCode  = (isset($charge->IssuerRspCode) ? $charge->IssuerRspCode : null);

            if ($gwResponseCode != "0" || $issuerResponseCode != "00") {
                $exceptions = new HpsChargeExceptions();
                if ($gwResponseCode != "0") {
                    $message = $charge->GatewayRspMsg;
                    $exceptions->hpsException = HpsGatewayResponseValidation::getException($charge->GatewayTxnId, $gwResponseCode, $message);
                }
                if ($issuerResponseCode != "00") {
                    $message = $charge->IssuerRspText;
                    $exceptions->cardException = HpsIssuerResponseValidation::getException($charge->GatewayTxnId, $issuerResponseCode, $message);
                }
                $summary->exceptions = $exceptions;
            }

            $transactions[] = $summary;
        }
        return $transactions;
    }
}
