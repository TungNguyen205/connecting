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

        $productExist = $productRepo->checkProduct($this->_product['id'], $this->_shop['id']);
        if($productExist) {
            die();
        }
        $meta = [
            'shop' => $this->_shop
        ];
        $this->_product = ProductHelper::convertProductModel($this->_product, $meta);
        $product = $productRepo->createOrUpdate($this->_product);

        // post section
        $productDetail = $productRepo->detail($product->id, $this->_shop['id']);

        $autoPosts = $this->autoPostRepository->list($this->_shop['id']);

        if(!empty($autoPosts)) {
            foreach($autoPosts as $autoPost) {
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
                        case 'product_link':
                            $data[$tag] = $productDetail['link'];
                            break;
                        default:
                            break;
                    }
                }
                $message = PostHelper::convertMessage($autoPost['template']['content'], $data);

                $postParams = [
                    'post_type'             => $autoPost['post_type'],
                    'number_images'         => $autoPost['number_images'],
                    'meta_link'             => $productDetail['link'],
                    'product_id'            => $productDetail['id'],
                    'message'               => $message,
                    'social_id'             => $autoPost['social_id'],
                    'social_type'           => null,
                    'status'                => "published",
                    'pinterest_board_id'    => $autoPost['pinterest_board_id']
                ];

                $post = $this->postRepository->savePost($postParams, $this->_shop['id'], $this->_shop['user_id']);
                $postDetail = $this->postRepository->detail($post->id, $this->_shop['id'], $this->_shop['user_id']);
                $postResult = $this->social->postSocial($postDetail['social']['social_type'], $postDetail);
                if(!$postResult['status']) {
                    $params = [
                        'post_social_id' => $postResult['data']['post_social_id']
                    ];
                } else {
                    $params = [
                        'error_message' => $postResult['message']
                    ];
                }

                $this->postRepository->update($postDetail['id'], $params);
            }
        }
    }
}
