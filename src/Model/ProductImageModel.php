<?php declare(strict_types = 1);

namespace PwPop\SlimApp\models;

use Illuminate\Database\Eloquent\Model;
use PwPop\SlimApp\models\Product;

final class ProductImageModel extends Model {
    /**
     * @var string name of the table
     */
    protected $table = 'image_product';
    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['id','product_id','image'];
    /**
     * @var string The primary key associated with the table.
     */
    protected $primaryKey = 'id';
    /**
     * get the images for the products.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function images()
    {
        return $this->belongsTo(Product::class);
    }
}
