<?php

namespace App\Http\Controllers;

use App\Exports\DynamicExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportedDataController extends Controller
{
    /**
     * Display the paginated list of records.
     */
    public function index(string $type, string $file, Request $request)
    {
        $file = $this->normalizeFileName($file);
        $config = $this->getFileConfig($type, $file);

        $modelClass = $this->getModelClass($file);
        $query = $modelClass::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $this->applySearchFilter($query, $config, $search);
        }

        $records = $query->paginate(10);

        return view('files.index', [
            'records' => $records,
            'config' => $config,
            'type' => $type,
            'file' => $file,
            'title' => ucfirst($type) . ' - ' . $config['label'],
        ]);
    }

    /**
     * Delete a specific record.
     */
    public function destroy(string $type, string $file, int $id): RedirectResponse
    {
        $modelClass = $this->getModelClass($file);
        $record = $modelClass::findOrFail($id);

        $record->delete();

        return redirect()->back()->with('success', 'Record deleted successfully.');
    }

    /**
     * Export the current list of records to an XLSX file.
     */
    public function export(string $type, string $file, Request $request)
    {
        $file = $this->normalizeFileName($file);
        $config = $this->getFileConfig($type, $file);

        $modelClass = $this->getModelClass($file);
        $query = $modelClass::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $this->applySearchFilter($query, $config, $search);
        }

        $records = $query->get();
        $data = $this->prepareExportData($records, $config);

        return Excel::download(new DynamicExport($data->toArray()), "$file-export.xlsx");
    }

    /**
     * Normalize the file name to replace hyphens with underscores.
     */
    private function normalizeFileName(string $file): string
    {
        return str_replace('-', '_', $file);
    }

    /**
     * Get the configuration for the given file type and name.
     */
    private function getFileConfig(string $type, string $file): array
    {
        $config = config("import_types.$type.files.$file");

        if (!$config) {
            abort(404, 'Configuration not found.');
        }

        return $config;
    }

    /**
     * Get the model class for the given file.
     */
    private function getModelClass(string $file): string
    {
        $modelName = Str::studly($file);
        $modelClass = "App\\$modelName";

        if (!class_exists($modelClass)) {
            abort(404, "Model $modelName not found.");
        }

        return $modelClass;
    }

    /**
     * Apply the search filter to the query.
     */
    private function applySearchFilter($query, array $config, string $search): void
    {
        $query->where(function ($q) use ($config, $search) {
            foreach (array_keys($config['headers_to_db']) as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Prepare data for export.
     */
    private function prepareExportData($records, array $config): \Illuminate\Support\Collection
    {
        return $records->map(function ($record) use ($config) {
            return collect($config['headers_to_db'])->mapWithKeys(function ($details, $header) use ($record) {
                return [$details['label'] => $record->$header];
            });
        });
    }
}
