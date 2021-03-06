<?php
namespace PhpBelfast\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 * @package PhpBelfast\Models
 */
class Post extends Model {


    public function author(){
        return $this->belongsTo('\PhpBelfast\Models\Author');
    }

}