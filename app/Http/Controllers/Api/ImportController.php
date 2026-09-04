<?php

namespace App\Http\Controllers\Api;

use App\Actions\Imports\AcceptImport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use App\Http\Resources\ImportStatusResource;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    #[OA\Post(
        path: '/api/imports',
        operationId: 'storeImport',
        summary: 'Queue an offer import',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/StoreImportRequest',
            ),
        ),
        tags: ['Imports'],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Import accepted for asynchronous processing',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ImportResource',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Request validation failed',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ValidationError',
                ),
            ),
        ],
    )]
    public function store(
        StoreImportRequest $request,
        AcceptImport $acceptImport,
    ): JsonResponse {
        return (new ImportResource($acceptImport->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    #[OA\Get(
        path: '/api/imports/{import}',
        operationId: 'showImport',
        summary: 'Get import processing status',
        tags: ['Imports'],
        parameters: [
            new OA\Parameter(
                name: 'import',
                description: 'Import ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    format: 'int64',
                    minimum: 1,
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Import status',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ImportStatusResource',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Import not found',
            ),
        ],
    )]
    public function show(Import $import): ImportStatusResource
    {
        return new ImportStatusResource(
            $import->load('supplier'),
        );
    }
}
