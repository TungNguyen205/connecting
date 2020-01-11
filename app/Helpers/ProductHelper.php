<?php

namespace App\Helpers;


class ProductHelper
{
    public static function convertProductModel(array $product, array $meta = [])
    {
        $data['platform_id'] = $product['id'];
        $data['title'] = $product['title'];
        $data['handle'] = $product['handle'];
        $data['image'] = [
            'src' => $product['image']['src'],
            'width' => $product['image']['width'],
            'height' => $product['image']['height'],
        ];
        $data['images'] = [];
        if(!empty($product['images'])) {
            foreach($product['images'] as $image) {
                $img = [
                    'src' => $image['src'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                ];
                array_push($data['images'], $img);
            }
        }

        $data['shop_id'] = $meta['shop']['id'];

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

        $data['link'] = $meta['shop']['domain'].'/products/'.$product['handle'];

        return $data;
    }
}