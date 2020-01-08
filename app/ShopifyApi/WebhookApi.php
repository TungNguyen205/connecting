<?php
namespace App\ShopifyApi;


use App\Services\SpfService;

class WebHookApi extends SpfService
{
    /**
     * @param string $address
     * @param string $topic
     *
     * @return array
     */
    public function addWebHook( string $address, string $topic ): array
    {
        return $this->postRequest( 'webhooks.json', [
            'webhook' => [
                'address' => $address,
                'topic'   => $topic,
                'format'  => 'json'
            ]
        ]);
    }

    /**
     * @return array
     */
    public function allWebHook(): array
    {
        return $this->getRequest( 'webhooks.json' );
    }

    /**
     * @param $webHookId
     *
     * @return array
     */
    public function detail( $webHookId ): array
    {
        return $this->getRequest( 'webhooks/' . $webHookId . '.json' );
    }

    /**
     * @param $webHookId
     *
     * @return array
     */
    public function delete( $webHookId ): array
    {
        return $this->deleteRequest( 'webhooks/' . $webHookId . '.json' );
    }

}