<?php

namespace App\Helpers;


class ProductHelper
{
    public static function convertProductModel(array $product, array $meta = [])
    {
        $data['id'] = $product['id'];
        $data['title'] = $product['title'];
        $data['handle'] = $product['handle'];
        $data['image'] = $product['image']['src'];
        $data['shop_id'] = $meta['shop_id'];

        $minPrice = $product['variants'][0]['price'];
        $maxPrice = $product['variants'][0]['price'];
        foreach($product['variants'] as $variant) {
            if( $variant['price'] < $minPrice ) {
                $minPrice = $variant['price'];
            }
            if ($variant['price'] > $maxPrice ) {
                $maxPrice = $variant['price'];
            }
        }

        $data['price'] = [
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ];

        return $data;
    }
}