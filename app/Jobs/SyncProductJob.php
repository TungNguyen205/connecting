<?php

namespace App\Jobs;

use App\Repository\ShopRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repository\ProductRepository;
use App\Helpers\ProductHelper;
use App\ShopifyApi\ProductApi;
class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $_myshopifyDomain;

    private $_accessToken;

    private $_shop;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($myshopifyDomain, $accessToken)
    {
        $this->_myshopifyDomain = $myshopifyDomain;

        $this->_accessToken = $accessToken;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pageInfo = '';
        do {
            $products = $this->getProducts($pageInfo);
            if($products['status']) {
                $pageInfo = $products['page_info'];
                $this->saveProducts($products['products'], $products['shop']);
            }

        } while($products['status'] && strlen($pageInfo) >0);
    }

    /**
     * @param $pageInfo
     * @return mixed
     */
    public function getProducts($pageInfo ='')
    {
        $field = [
            'id',
            'title',
            'handle',
            'image',
            'images',
            'variants'
        ];
        $limit = 250;
        $shopRepository = new ShopRepository();
        $productApi = new ProductApi();

        $shop = $shopRepository->getShopAttributes(['myshopify_domain' => $this->_myshopifyDomain]);

        if( ! $shop)
            return ['status' => false];

        $productApi->setParameter($shop['myshopify_domain'], $shop['access_token']);
        $products = $productApi->list($pageInfo, $field, [], $limit);

        if(! $products['status']) {
            return ['status' => false];
        }

        return ['status' => true, 'page_info' => $products['page_info'], 'products' => $products['data']['products'], 'shop' => $shop];

    }

    private function saveProducts($products, $shop)
    {
        $productRepo = new ProductRepository();
        try{
            DB::beginTransaction();
            foreach($products as $product) {
                $meta = [
                    'shop_id'   => $shop['id']
                ];
                $product = ProductHelper::convertProductModel($product, $meta);
                $productRepo->createOrUpdate($product, $meta);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }


}
