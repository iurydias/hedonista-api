<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Favorite;
use App\Category;
use App\Point;
use App\Subcategory;
use App\Helpers\Functions;

class HomeDataController extends Controller
{
    protected function get(Request $request)
    {
        $categories = Category::get();
        $categories->map(function ($item) {
            $pointsNumber = 0;
            $subcategories = Subcategory::where('fk_category', $item->id)->get();
            $subcategories->map(function ($item) use (&$pointsNumber){
              $pointsNumberBySubcategory = Point::where('fk_subcategory', $item->id)->get()->count();
              $pointsNumber += $pointsNumberBySubcategory;
            });
            $item['pointsNumber'] = $pointsNumber;
            return $item;
          });
        $data = $request->all();
        $user = User::where('api_token', $request->header('token'))->first();
        $favorites = Favorite::where('fk_user', $user->id)->with("point")->with("author")->get();
        $favorites->map(function ($item) {
            $point =  Point::where('id', $item->fk_point)->get()->first();
            $subcategory =  Subcategory::where('id', $point->fk_subcategory)->get()->first();
            $category =  Category::where('id', $subcategory->fk_category)->get()->first();
            $item['icon'] = $category->icon;
            return $item;
          });
        $dados['categories'] = $categories->toArray();
        $dados['favorites'] = $favorites->toArray();
        return Functions::sendResponse($dados, "");
    }
}
