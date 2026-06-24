<?php

namespace App\Services;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigurationService
{
    public function __construct(
        protected ActivityLogger $logger
    ) {}

    public function all(string $modelClass): \Illuminate\Database\Eloquent\Collection
    {
        return $modelClass::all();
    }

    public function create(string $modelClass, array $data, Request $request, string $labelField = 'name'): Model
    {
        $model = $modelClass::create($data);

        $this->logAction($request, $model, $labelField, 'created');

        return $model;
    }

    public function update(Model $model, array $data, Request $request, string $labelField = 'name'): Model
    {
        $model->update($data);

        $this->logAction($request, $model, $labelField, 'updated');

        return $model;
    }

    public function delete(Model $model, Request $request, string $labelField = 'name'): void
    {
        $label = $model->{$labelField} ?? "#{$model->getKey()}";

        $model->delete();

        ActivityLogger::make($request)
            ->log(class_basename($model) . " \"{$label}\" deleted");
    }

    protected function logAction(Request $request, Model $model, string $labelField, string $action): void
    {
        $label = $model->{$labelField} ?? "#{$model->getKey()}";
        $classBase = class_basename($model);

        ActivityLogger::make($request)
            ->log("{$classBase} \"{$label}\" {$action}");
    }
}
