<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = ['title', 'author', 'summary', 'link', 'pull_at', 'status'];

    /**
     * 新闻详情
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail(){
        return $this->hasOne(NewDetail::class, 'id', 'id');
    }

    /**
     * 相关报道
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function correlations(){
        return $this->hasMany(NewCorrelation::class, 'new_id', 'id');
    }
}
