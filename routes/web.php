<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Intervention\Image\ImageManagerStatic as Image;
use Iman\Streamer\VideoStreamer;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('storage/image/{filename}', function ($filename)
{
    // local
    return Image::make(storage_path('public/image/' . $filename))->response();
    // hosting
    // return Image::make('/home/bare3321/lq.bakamla/public/storage/image/' . $filename)->response();
});
Route::get('storage/video/{filename}', function ($filename)
{
    //  $path = storage_path('public/video/' . $filename);
    // hosting
     $path = public_path('storage/video/' . $filename);
    
    VideoStreamer::streamFile($path);
});
Route::get('storage/document/{filename}', function ($filename){
        $file = storage_path("app/public/document/$filename");

        return response()->download($file);
});
Route::get('storage/sound/{filename}', function ($filename)
{
    $path = storage_path('app/public/sound/' . $filename);

    $headers = [
        'Content-Type' => 'audio/mp3', // Adjust the content type based on your audio file type
        // 'Content-Length' => filesize($path),
        'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    return response()->file($path, $headers);
});