<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ShopRepository;
use App\ShopifyApi\WebHookApi;
use App\Jobs\AddWebhookJob;
use App\Jobs\HandleProductWebhookJob;
class SpfWebhookController extends Controller
{
    function viewWebhook($shopName)
    {
        $domain = $shopName.'.myshopify.com';
        $shop_repository = app(ShopRepository::class);
        $shop = $shop_repository->getShopAttributes(['myshopify_domain' => $domain]);
        if($shop) {
            $webhookApi = new WebHookApi();
            $webhookApi->setParameter($shop['myshopify_domain'], $shop['access_token']);
            $webhook = $webhookApi->allWebHook();
            return response()->json($webhook);
        }
        return response()->json(['status' => false, 'message' => 'shop invalid']);
    }

    function addWebhook($shopName)
    {
        $domain = $shopName.'.myshopify.com';
        $shop_repository = app(ShopRepository::class);
        $shop = $shop_repository->getShopAttributes(['myshopify_domain' => $domain]);
        if($shop) {
            AddWebhookJob::dispatch($shop['myshopify_domain'], $shop['access_token']);
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false, 'message' => 'cannot add webhook']);
    }

    public function uninstallApp(Request $request)
    {
        $res = $request->all();
        $shopRepo = new ShopRepository();
        $shopRepo->createOrUpdate($res['id'],
            [
                'status' => config( 'common.status.unpublish' ),
                'access_token' => '',
            ]);
    }

    public function createdProduct(Request $request)
    {
        $res = $request->all();
        $shopRepo = new ShopRepository();
        $shop_name = $request->server('HTTP_X_SHOPIFY_SHOP_DOMAIN');
        $shop = $shopRepo->getShopAttributes(['myshopify_domain' => $shop_name]);
        if(!$shop )
            return response()->json(['status' => false]);

        HandleProductWebhookJob::dispatch($shop, json_decode(json_encode($res), true));

        return response()->json(['status' => true]);
    }
}
