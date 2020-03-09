<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function index(Request $request)
    {
        $query = [
            'type'        => $request->input('type'),
            'destination' => $request->input('destination'),
            'app.name' => $request->input('app_name'),
            'app.id' => $request->input('app_id'),
            'size' => $request->input('limit'),
            'from' => $request->input('offset'),
        ];

        if ($query['type'] || $query['destination'] || $query['app.name'] || $query['app.id']) {
            $images = $this->image->getImagesFiltered($query);
        } else {
            $images = $this->image->getAllImages($query);
        }

        return response()->json($images);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        $response = $this->image->addImage($payload);

        if (@$response['status'] === 400) {
            $res = [
                'error' => [
                    'message' => 'Wrong payload, please verify',
                    'reason'  => str_replace(" within [_doc]", "", @$response['error']['reason'])
                ]
            ];
            return response()->json($res, 400);
        } else if (@$response['error']) {
            return response()->json($response, 400);
        } else {
            return response()->json($response, 201);
        }
        // return response()->json(@$response['error'] ? ['error' => 'Wrong payload, please verify', 'message' => str_replace(" within [_doc]", "", @$response['error']['reason'])] : $response, @$response['error'] ? 400 : 201);
        // return response()->json(@$response['error'] ? ['error' => 'Wrong payload, please verify', 'message' => str_replace(" within [_doc]", "", @$response['error']['reason'])] : ['message' => 'Image created successfully'], @$response['error'] ? 400 : 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $image = $this->image->getImage($id);

        return response()->json($image ? $image : ['message' => 'Image not found'], $image ? 200 : 404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $payload = $request->all();
        $response = $this->image->updateImage($payload, $id);
        if (@$response['status'] === 400) {
            $res = [
                'error' => [
                    'message' => 'Wrong payload, please verify',
                    'reason'  => str_replace(" within [_doc]", "", @$response['error']['reason'])
                ]
            ];
            return response()->json($res, 400);
        } else if (@$response['error']) {
            return response()->json($response, 400);
        } else {
            return response()->json(['message' => 'Image updated successfully'], 200);
        }
        // return response()->json(@$response['error'] ? ['error' => 'Wrong payload, please verify', 'message' => str_replace(" within [_doc]", "", @$response['error']['reason'])] : ['message' => 'Image created successfully'], @$response['error'] ? 400 : 201);
        // return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    // public function destroy(Image $image)
    public function destroy($id)
    {
        $image = $this->image->deleteImage($id);

        return response()->json(@$image['result'] === 'not_found' ? ['message' => 'Image not found'] : ['message' => 'Image deleted successfully'], @$image['result'] === 'not_found' ? 404 : 200);
    }
}
