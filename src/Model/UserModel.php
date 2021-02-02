<?php declare(strict_types = 1);

namespace PwPop\SlimApp\models;

use Illuminate\Database\Eloquent\Model;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;

final class User extends Model {
   /**
    * @var string  name of the table
    */
   protected $table = 'user';
   /**
    * @var array The attributes that are mass assignable.
    */
   protected $fillable = [
       'id',
       'name',
       'username',
       'email',
       'birthdate',
       'phone',
       'password',
       'image',
       'validated',
       'validation_code',
       'session_id',
       'is_active',
   ];
   /**
    * @var string The primary key associated with the table.
    */
   protected $primaryKey = 'id';

   public function favorites() {
      return $this->hasMany(Favorite::class);
   }  

   public function products() {
      return $this->hasMany(Product::class);
   }
}
