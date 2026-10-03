<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Uploads;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /** Images dropped or pasted into the article editor. */
    public function image(Request $request)
    {
        $request->validate(['image' => 'required|' . Uploads::IMAGE_RULE]);

        return response()->json(['url' => asset(Uploads::storeImage($request->file('image'), 'blog'))]);
    }
}
