<?php

namespace App\Http\Controllers\Api;

use App\Actions\Properties\SearchProperties;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexPropertyRequest;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PropertyController extends Controller
{
    #[OA\Get(
        path: '/api/properties',
        operationId: 'indexProperties',
        summary: 'Search properties with available offers',
        tags: ['Properties'],
        parameters: [
            new OA\Parameter(
                name: 'city',
                description: 'Property city',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'Barcelona',
                    maxLength: 100,
                ),
            ),
            new OA\Parameter(
                name: 'check_in',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date',
                    example: '2026-10-10',
                ),
            ),
            new OA\Parameter(
                name: 'check_out',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date',
                    example: '2026-10-15',
                ),
            ),
            new OA\Parameter(
                name: 'guests',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 2,
                    maximum: 65535,
                    minimum: 1,
                ),
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1,
                    minimum: 1,
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated properties ordered by the best offer price',
                content: new OA\JsonContent(
                    required: [
                        'data',
                        'links',
                        'meta',
                    ],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/PropertyResource',
                            ),
                        ),
                        new OA\Property(
                            property: 'links',
                            required: [
                                'prev',
                                'next',
                            ],
                            properties: [
                                new OA\Property(
                                    property: 'prev',
                                    type: 'string',
                                    nullable: true,
                                ),
                                new OA\Property(
                                    property: 'next',
                                    type: 'string',
                                    nullable: true,
                                ),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            required: ['per_page'],
                            properties: [
                                new OA\Property(
                                    property: 'per_page',
                                    type: 'integer',
                                    example: 15,
                                ),
                            ],
                            type: 'object',
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
    public function index(
        IndexPropertyRequest $request,
        SearchProperties $searchProperties,
    ): AnonymousResourceCollection {
        return PropertyResource::collection($searchProperties->handle($request->toData()));
    }
}
