<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(Request $request, Topic $topic)
    {
        $topics =  $topic->WithOrder($request->get('order'))->paginate(20);
        return view('topics.index',compact('topics'));
    }

    public function create(Topic $topic)
    {
        $categories = Category::all();
        return view('topics.create_and_edit',compact('topic', 'categories'));
    }

    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = \Auth::id();
        $topic->save();

        return redirect()->route('topics.show', $topic->id)->with('message', 'Created successfully.');
    }

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        $data = [
            'success' => false,
            'msg' => '上传失败',
            'file_path' => ''
        ];

        // 判断是否有文件上传
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($file, 'topics', \Auth::id(), 1024);
            // 保存成功
            if( $result) {
                $data['file_path'] = $result['path'];
                $data['msg'] = '上传成功';
                $data['success'] = true;
            }
        }

        return $data;
    }
}
