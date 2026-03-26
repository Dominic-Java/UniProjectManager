<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Services\Projects\ProjectNotificationService;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectMaterialsController extends Controller
{
    public function store(
        Request $request,
        Project $project,
        ProjectNotificationService $projectNotificationService
    ): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);
        abort_unless(ClassroomAccess::canUploadClasswork($request->user(), $project), 403);

        if ($project->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa termen. Nu mai poti adauga materiale.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'material_file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip,rar,7z,png,jpg,jpeg,gif,webp,bmp',
                'max:51200',
            ],
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

        try {
            $projectNotificationService->notifyMaterialUploaded($project, $material, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            AuditLogger::log('projects.material.notification.failed', $request->user(), 'project_material', $material->id, [
                'project_id' => $project->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Materialul a fost incarcat cu succes.');
    }

    public function download(Request $request, ProjectMaterial $material): StreamedResponse|RedirectResponse
    {
        abort_unless($request->user(), 403);
        $material->loadMissing('project');
        abort_unless($material->project && ClassroomAccess::canAccessProject($request->user(), $material->project), 403);

        if (!Storage::disk('local')->exists($material->file_path)) {
            return back()->with('error', 'Materialul cautat nu mai este disponibil in sistem.');
        }

        AuditLogger::log('projects.material.download', $request->user(), 'project_material', $material->id, [
            'project_id' => $material->project_id,
        ]);

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }

    public function destroy(Request $request, ProjectMaterial $material): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);

        $material->loadMissing('project');
        abort_unless($material->project && ClassroomAccess::canUploadClasswork($request->user(), $material->project), 403);
        if ($material->project?->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa termen. Nu mai poti elimina materiale.');
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

        return back()->with('success', 'Materialul a fost eliminat.');
    }
}
