<?php declare(strict_types = 1);

namespace PwPop\SlimApp\models;

use Illuminate\Database\Eloquent\Model;
use PwPop\SlimApp\models\User;

final class Favorite extends Model{
    /**
     * @var string name of the table
     */
   protected $table = 'favorites';
   /**
     * @var array The attributes that are mass assignable.
     */
   protected $fillable = ['id','user_id','product_id',];
   /**
     * @var string The primary key associated with the table.
     */
   protected  $primaryKey = 'id';

   public function favorites() {
        return $this->belongsTo(User::class);
   }
}
