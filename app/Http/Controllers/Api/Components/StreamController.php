<?php

namespace App\Http\Controllers\Api\Components;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\ActivityStreamModel;
use App\Model\ActivityStreamRightModel;
use App\Model\BlogCategoryRightModel;
use App\Model\BlogModel;
use App\Model\DepartmentModel;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class StreamController extends ApiController
{
    public function SaveStream(Request $request)
    {
        $category   = $request->category;
        $rights     = self::rights($request);
        $categoryRight = BlogCategoryRightModel::where(["blog_category_id"=>$category])->whereIn("access_code",$rights);
        if($categoryRight->count()==0){
            return response([
                'status' => false,
                'message' =>  "Yetkisiz İşlem",
            ], 200);
        }

        $blog = new BlogModel();
        $blog->category_id = $category;
        $blog->title        =  substr(strip_tags($request->message),0,255);
        $blog->detail_text  = $request->message;
        $blog->From         = $request->From ? 'D_'.$request->From : 'E_'.$request->Employee;
        $blog->is_active    = 1;
        if(!$blog->save()){
            return response([
                'status' => false,
                'message' =>  "Hata oluştu"
            ], 200);
        }

        $stream = new ActivityStreamModel();
        $stream->module_id = "blog";
        $stream->title        = $blog->title;
        $stream->message      = $blog->detail_text;
        $stream->From         = $request->From ? 'D_'.$request->From : 'E_'.$request->Employee;
        $stream->source_id    = $blog->id;
        $stream->is_active    = 1;
        if(!$stream->save()){
            return response([
                'status' => false,
                'message' =>  "Hata oluştu"
            ], 200);
        }
        $to = $request->to;
        $to[] = "E_".$request->Employee;
        foreach ($to as $item) {
            $streamRight = new ActivityStreamRightModel();
            $streamRight->access_code   = $item;
            $streamRight->stream_id     = $stream->id;
            $streamRight->save();
        }

        return response([
            'status' => true,
            'data' =>  $stream->id,
        ], 200);

    }


    public function streamList(Request $request)
    {
        $streamsQ = ActivityStreamModel::selectRaw("DISTINCT activity_stream.*");
        $streamsQ->leftJoin("activity_stream_right","activity_stream_right.stream_id","=","activity_stream.id");

        $rights = self::rights($request);
        $rights[] = "AU";
        $streams = $streamsQ->whereIn("activity_stream_right.access_code",$rights)
            ->where(["activity_stream.is_active"=>1])->orderBy("created_at","desc")->get();
        if($request->categoryId!==null){
            $stremk = [];
            foreach ($streams as $key=>$stream) {
                if($stream->module_id=="blog"){
                    $categoryCount = BlogModel::where(["id"=>$stream->source_id,"category_id"=>$request->categoryId])->count();
                    if($categoryCount>0)
                    {

                        $stremk[] = $stream;
                    }
                }

            }
            $streams = $stremk;
            //$streams = array_values($streams);
        }

        foreach ($streams as $key=>$stream) {
            $stream->setAttribute("SharedFrom",substr($stream->From,0,1) === 'E' ? EmployeeModel::find(substr($stream->From,2)) : DepartmentModel::find(substr($stream->From,2)));
        }

        return response([
            'status' => true,
            'data' =>  $streams
        ], 200);
    }

    public function toList(Request $request){
        $to[] = ["id"=>"AU","name"=>"Tüm Çalışanlar"];
        $employee = EmployeeModel::select("Employee.Id","Employee.FirstName","Employee.LastName")
            ->where(["Employee.Active"=>1])->get();
        foreach ($employee as $item) {
            $to[] = ["id"=>"E_".$item->Id,"name"=>$item->FirstName." ".$item->LastName];
        }
        return response([
            'status'    => true,
            'data'      =>  $to
        ], 200);
    }

    public function categoryList(Request $request)
    {
        $rights = self::rights($request);
        $blogCategory = BlogCategoryRightModel::selectRaw("DISTINCT blog_category.*")
            ->leftJoin("blog_category","blog_category.id","=","blog_category_right.blog_category_id")
            ->whereIn("blog_category_right.access_code",$rights)
            ->where(["blog_category.is_active"=>1])->get();

        return response([
            'status'    => true,
            'data'      =>  $blogCategory
        ], 200);
    }


    public function rights(Request $request)
    {
        $groups = EmployeeHasGroupModel::where(["EmployeeID"=>$request->Employee])->pluck("group_id");
        $rights[] = "E_".$request->Employee;
        foreach ($groups as $group) {
            $rights[] = "G_".$group;
        }

        return $rights;
    }
}
