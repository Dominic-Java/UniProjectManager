<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectMaterialsController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        if ($project->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa deadline. Nu mai poti adauga materiale.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'material_file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $validated['material_file'];
        $originalName = (string) $file->getClientOriginalName();
        $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'material';
        $storedName = now()->format('Ymd_His') . '_' . Str::lower(Str::random(10)) . '_' . $safeOriginalName;

        $filePath = $file->storeAs(
            'projects/' . $project->id . '/materials',
            $storedName,
            'local'
        );

        $material = ProjectMaterial::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'file_path' => $filePath,
            'original_name' => $originalName,
            'mime_type' => $file->getClientMimeType(),
            'file_size_bytes' => $file->getSize() ?? 0,
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        AuditLogger::log('projects.material.upload', $request->user(), 'project_material', $material->id, [
            'project_id' => $project->id,
            'material_title' => $material->title,
        ]);

        return back()->with('success', 'Materialul a fost incarcat.');
    }

    public function download(Request $request, ProjectMaterial $material): StreamedResponse|RedirectResponse
    {
        abort_unless($request->user(), 403);

        if (!Storage::disk('local')->exists($material->file_path)) {
            return back()->with('error', 'Materialul nu mai exista in storage.');
        }

        AuditLogger::log('projects.material.download', $request->user(), 'project_material', $material->id, [
            'project_id' => $material->project_id,
        ]);

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }

    public function destroy(Request $request, ProjectMaterial $material): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $material->loadMissing('project');
        if ($material->project?->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa deadline. Nu mai poti sterge materiale.');
        }

        if (Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $materialId = $material->id;
        $projectId = $material->project_id;
        $material->delete();

        AuditLogger::log('projects.material.delete', $request->user(), 'project_material', $materialId, [
            'project_id' => $projectId,
        ]);

        return back()->with('success', 'Materialul a fost sters.');
    }
}
