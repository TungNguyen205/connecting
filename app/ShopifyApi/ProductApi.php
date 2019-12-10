<?php
/**
 * Created by PhpStorm.
 * User: buicongdang
 * Date: 7/24/19
 * Time: 9:54 AM
 */

namespace App\ShopifyApi;

use App\Services\SpfService;
use Exception;
class ProductApi extends SpfService
{
    function list(string $pageInfo, array $field, array $filters, int $limit): array
    {
        $field = implode(',', $field);
        $filters['fields'] = $field;
        $filters['limit'] = $limit;
        if(strlen($pageInfo) > 0) {
            $filters['page_info'] = $pageInfo;
        }

        $products = $this->getRequest('products.json', $filters);
        return $products;
    }

}