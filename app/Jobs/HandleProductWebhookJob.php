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
use App\Repository\AutoPostRepository;
use App\Helpers\Common;
use App\Helpers\PostHelper;
class HandleProductWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $_shop, $_product, $postRepository, $social, $autoPostRepository;


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
    public function handle(PostRepository $postRepository, Social $social, AutoPostRepository $autoPostRepository)
    {
        $this->postRepository = $postRepository;
        $this->social = $social;
        $this->autoPostRepository = $autoPostRepository;
        $productRepo = new ProductRepository();
        $meta = [
            'shop' => $this->_shop
        ];
        $this->_product = ProductHelper::convertProductModel($this->_product, $meta);
        $product = $productRepo->createOrUpdate($this->_product);

        // post section
        $productDetail = $productRepo->detail($product->id, $this->_shop['id']);

        $autoPost = $this->autoPostRepository->detail($this->_shop['id']);

        if(!empty($autoPost)) {
            $tags = Common::getTagList();
            $data = [];
            foreach($tags as $k=>$tag) {
                switch($k) {
                    case 'product_title':
                        $data[$tag] = $productDetail['title'];
                        break;
                    case 'min_price':
                        $data[$tag] = $productDetail['price']['min_price'];
                        break;
                    case 'currency':
                        $data[$tag] = $this->_shop['currency'];
                        break;
                    default:
                        break;
                }
            }
            $message = PostHelper::convertMessage($autoPost['template']['content'], $data);

            $postParams = [
                'post_type'     => 'image',
                'meta_link'     => null,
                'product_id'    => $productDetail['id'],
                'message'       => $message,
                'social_ids'    => $autoPost['social_ids'],
                'social_id'     => null,
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
}
