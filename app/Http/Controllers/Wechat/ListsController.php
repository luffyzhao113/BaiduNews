<?php

namespace App\Http\Controllers\Wechat;

use App\Searchs\Modules\Wechat\Detail\IndexSearch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Modules\News\Interfaces;

class ListsController extends Controller
{
    protected $repo;

    public function __construct(Interfaces $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request){
        $search = new IndexSearch($request->all());
        return $this->respondWithSuccess(
            $this->repo->paginate($search->toArray())
        );
    }

    public function show(Request $request, $id){
        return $this->respondWithSuccess(
            $this->repo->make(['detail'])->find($id)
        );
    }
}
