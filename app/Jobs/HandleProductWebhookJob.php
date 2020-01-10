<?php

namespace App\Jobs;

use App\Helpers\ProductHelper;
use App\Repository\ProductRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repository\PostRepository;
use App\Social\Social;
class HandleProductWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $_shop, $_product, $postRepository, $social;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop, $product)
    {
        $this->_shop = $shop;
        $this->_product = $product;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PostRepository $postRepository, Social $social)
    {
        $this->postRepository = $postRepository;
        $this->social = $social;
        $productRepo = new ProductRepository();
        $meta = [
            'shop_id' => $this->_shop['id']
        ];
        $this->_product = ProductHelper::convertProductModel($this->_product, $meta);
        $product = $productRepo->createOrUpdate($this->_product);

        $productDetail = $productRepo->detail($product->id, $this->_shop['id']);

        $postParams = [
            'post_type'     => 'image',
            'meta_link'     => null,
            'product_id'    => $productDetail['id'],
            'message'       => $productDetail['title'],
            'social_ids'    => ["1"],
            'social_id'     => 1,
            'social_type'   => "facebook",
            'status'        => "published"
        ];

        $post = $this->postRepository->savePost($postParams, $this->_shop['id'], $this->_shop['user_id']);
        $postDetail = $this->postRepository->detail($post->id, $this->_shop['id'], $this->_shop['user_id']);
        foreach($postDetail['socials'] as $social) {
            $data = $postDetail;
            $data['socials'] = $social;
            $this->social->postSocial($postDetail['social_type'], $data);
        }
    }
}
