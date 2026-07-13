<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    private const DIRECTORY = 'images/office-logo';

    /**
     * Resolve the public directory for the office logo.
     */
    private function logoDir(): string
    {
        return public_path(self::DIRECTORY);
    }

    /**
     * Get the current office logo URL.
     */
    public function getOfficeLogo(): JsonResponse
    {
        $dir = $this->logoDir();

        if (! File::isDirectory($dir)) {
            return response()->json(['logo_url' => null]);
        }

        $files = File::files($dir);

        if (empty($files)) {
            return response()->json(['logo_url' => null]);
        }

        return response()->json([
            'logo_url' => asset(self::DIRECTORY . '/' . $files[0]->getFilename()),
        ]);
    }

    /**
     * Upload or replace the office logo.
     */
    public function uploadOfficeLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
        ]);

        $file = $request->file('logo');
        $dir = $this->logoDir();

        // Ensure directory exists
        File::ensureDirectoryExists($dir);

        // Delete existing logos
        File::cleanDirectory($dir);

        // Store new logo with original extension
        $extension = $file->getClientOriginalExtension();
        $filename = 'office-logo.' . $extension;
        $file->move($dir, $filename);

        return response()->json([
            'logo_url' => asset(self::DIRECTORY . '/' . $filename),
            'message' => 'Office logo updated successfully.',
        ]);
    }

    /**
     * Remove the office logo.
     */
    public function deleteOfficeLogo(): JsonResponse
    {
        $dir = $this->logoDir();

        if (File::isDirectory($dir)) {
            File::cleanDirectory($dir);
        }

        return response()->json([
            'logo_url' => null,
            'message' => 'Office logo removed.',
        ]);
    }
}
