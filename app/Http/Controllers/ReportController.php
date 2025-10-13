<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    //

    public function postList(Request $request)
    {
        return view('posts.report');
    }

    public function commentList(Request $request)
    {
        return view('comments.report');
    }

    public function userList(Request $request)
    {
        return view('users.report');
    }
}
