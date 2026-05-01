<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Store a newly created material.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_slug' => ['required', 'string'],
            'topic_index' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'type' => ['required', 'in:material,video,link'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $filePath = null;
        $originalName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filePath = $file->store('materials', 'public');
        }

        $icon = 'doc';
        if ($data['type'] === 'video') {
            $icon = 'video';
        }

        $material = Material::create([
            'subject_slug' => $data['subject_slug'],
            'topic_index' => $data['topic_index'],
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'type' => $data['type'],
            'file_path' => $filePath,
            'original_filename' => $originalName,
            'icon' => $icon,
        ]);

        return response()->json(['material' => $material], 201);
    }
}
