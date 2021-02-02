<?php declare(strict_types = 1);

namespace PwPop\SlimApp\models;

use Illuminate\Database\Eloquent\Model;

final class Category extends Model {
   /**
    * @var string name of the table
    */
   protected $table = 'category';
   /**
     * @var array The attributes that are mass assignable.
     */
   protected $fillable = ['id','name'];
   /**
     * @var string The primary key associated with the table.
     */
   protected  $primaryKey = 'id';
}
