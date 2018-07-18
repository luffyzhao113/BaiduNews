<?php

namespace App\Repositories\Modules\News;

use luffyzhao\laravelTools\Repositories\Facades\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Model;

class Eloquent extends RepositoriesAbstract implements Interfaces
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function create(array $attributes = [])
    {
        $model = parent::create($attributes);

        if(isset($attributes['detail'])){
            $model->detail()->create($attributes['detail']);
        }

        return $model;
    }
}
