<?php declare(strict_types = 1);

namespace PwPop\SlimApp\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class Product extends Model{

   use SoftDeletes;
   /**
     * @var string  name of the table
     */
   protected $table = 'product';
   /**
     * @var array string The attributes that are mass assignable.
     */
   protected $fillable = [
       'id',
       'title',
       'description',
       'price',
       'category_id',
       'image',
       'user_id',
       'sold_out',
   ];
   /**
     * @var string  The primary key associated with the table.
     */
   protected $primaryKey = 'id';
   /**
     * Get the images for the products.
     * @return \Illuminate\Database\Eloquent\Builder
     */
   public function images()
   {
      return $this->hasMany(ProductImageModel::class);
   }

   public function products()
   {
      return $this->belongsTo(User::class);
   }
}
