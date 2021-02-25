<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{

    public function connectUpload(Request $request)
    {
        foreach ($request as $name => $contents) {
            $sendData["multipart"][] = [
                'name' => $name,
                'contents' => $contents,
            ];
        }

        if (is_array($_FILES["file"]['tmp_name'])) {
            foreach ($_FILES["file"]["size"] as $key => $file) {
                if ($file <> 0) {
                    $sendData["multipart"][] = [
                        'name' => 'file[' . $key . ']',
                        'contents' => file_get_contents($_FILES["file"]["tmp_name"][$key]),
                        'filename' => $_FILES["file"]["name"][$key]
                    ];
                }
            }
        } else {
            if ($_FILES["file"]["size"] <> 0) {
                $sendData["multipart"][] = [
                    'name' => 'file',
                    'contents' => file_get_contents($_FILES["file"]["tmp_name"]),
                    'filename' => $_FILES["file"]["name"]
                ];
            }

        }

        try {
            $client = new \GuzzleHttp\Client();
            $rest = $client->post("http://lifi.asay.com.tr/connectUpload/connectUpload", $sendData);
            $resp = json_decode($rest->getBody());
            $this->response($resp, 200);
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }
}
