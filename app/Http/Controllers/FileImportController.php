<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFileImport;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

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
        $importType = $request->input('import_type');
        $config = config("import_types.$importType");

        if (!$config || !auth()->user()->can($config['permission_required'])) {
            return back()->with('error', 'Unauthorized or invalid import type.');
        }

        $files = $config['files'];
        foreach ($files as $key => $file) {
            if ($request->hasFile($key)) {
                $uploadedFile = $request->file($key);
                ProcessFileImport::dispatch($uploadedFile, $file, $importType);
            }
        }

        return back()->with('success', 'Import is in progress.');
    }
}
