<?php

namespace App\Http\Controllers;

use App\Import;
use App\Imports\StandardOrderImport;
use App\Jobs\ProcessFileImport;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class FileImportController extends Controller
{
    /**
     * Show file import form
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $importTypes = config('import_types');
        $user = auth()->user();

        $availableImports = array_filter($importTypes, function ($type) use ($user) {
            return $user->can($type['permission_required']);
        });

        return view('imports.index', ['importTypes' => $availableImports, 'user' => $user]);
    }

    /**
     * Handle file upload
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function process(Request $request)
    {
        // Validate import type
        $request->validate([
            'import_type' => 'required',
        ]);

        $importType = $request->input('import_type');
        $configType = config("import_types.$importType");

        // Check if the config exists and if the user has permission
        if (!$configType || !auth()->user()->can($configType['permission_required'])) {
            return back()->with('error', 'Unauthorized or invalid import type.');
        }

        $files = $configType['files'];

        // Check if at least one file is uploaded
        $importedFiles = [];
        $importedFilesNames = [];
        foreach ($files as $key => $file) {
            if ($request->hasFile($key)) {
                $importedFiles[$key] = $request->file($key);
            }
        }
        if (empty($importedFiles)) {
            return back()->with('error', 'You must upload at least one file.');
        }

        try {
            foreach ($importedFiles as $key => $uploadedFile) {
                $originalFileName = $uploadedFile->getClientOriginalName();
                $filePath = $uploadedFile->store('imports');
                ProcessFileImport::dispatch($importType, $key, $filePath, $originalFileName);
                $importedFilesNames[] = $originalFileName;
            }

            return redirect()->back()->with(
                'success',
                'File(s) imported successfully: ' . implode(', ', $importedFilesNames)
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
