<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Image;

class ImageManagementController extends Controller
{
    /**
     * ImageManagementController constructor.
     */
    public function __construct(protected ImageService $imageService) {}

    /**
     * Remove Asset
     * * Delete any asset file and its registry record dynamically by ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $image = Image::findOrFail($id);

        // Authorization gate check can be added safely here:
        // $this->authorize('delete', $image->imageable);

        $this->imageService->delete($image);

        return response()->json([
            'message' => 'The asset file and its registry were deleted permanently.'
        ], Response::HTTP_OK); // 200 OK
    }

    /**
     * Assign Primary Badge
     * * Set a chosen target image as the primary view for its parent model.
     */
    public function makePrimary(int $id): JsonResponse
    {
        $image = Image::findOrFail($id);
        $this->imageService->setAsPrimary($image);

        return response()->json([
            'message' => 'The selected image is now defined as the primary asset.'
        ], Response::HTTP_OK); // 200 OK
    }
}
