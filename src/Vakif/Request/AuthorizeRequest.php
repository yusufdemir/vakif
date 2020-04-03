<?php

namespace Payconn\Vakif\Request;

use Payconn\Common\AbstractRequest;
use Payconn\Common\HttpClient;
use Payconn\Common\ResponseInterface;
use Payconn\Vakif\Model\Authorize;
use Payconn\Vakif\Response\AuthorizeResponse;
use Payconn\Vakif\Token;

class AuthorizeRequest extends AbstractRequest
{
    public function send(): ResponseInterface
    {
        /** @var Authorize $model */
        $model = $this->getModel();
        /** @var Token $token */
        $token = $this->getToken();

        /** @var HttpClient $httpClient */
        $httpClient = $this->getHttpClient();
        $params = [
          'MerchantId' => $token->getMerchantId(),
          'MerchantPassword' => $token->getPassword(),
          'VerifyEnrollmentRequestId' => $model->getOrderId(),
          'Pan' => $model->getCreditCard()->getNumber(),
          'ExpiryDate' => $model->getCreditCard()->getExpireYear().$model->getCreditCard()->getExpireMonth(),
          'PurchaseAmount' => $model->getAmount(),
          'Currency' => $model->getCurrency(),
          'BrandName' => $model->getCardBrand(),
          'SuccessUrl' => $model->getSuccessfulUrl(),
          'FailureUrl' => $model->getFailureUrl(),
          'SessionInfo' => $model->getAmount()
        ];
        if($model->getInstallment()>1){
          $params['InstallmentCount'] = $model->getInstallment();
        }
        $response = $httpClient->request('POST', $model->getBaseUrl(), [
            'form_params' => $params,
        ]);

        return new AuthorizeResponse($model, (array) @simplexml_load_string($response->getBody()->getContents()));
    }
}
