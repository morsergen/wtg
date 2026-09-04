<?php

namespace App\Http\Controllers\Api;

use App\Actions\Reservations\CreateReservation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    /**
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/offers/{offer}/reservations',
        operationId: 'storeReservation',
        summary: 'Reserve one unit of an offer',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/StoreReservationRequest',
            ),
        ),
        tags: ['Reservations'],
        parameters: [
            new OA\Parameter(
                name: 'offer',
                description: 'Offer ID',
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
                response: 201,
                description: 'Reservation created',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ReservationResource',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Offer not found',
            ),
            new OA\Response(
                response: 409,
                description: 'Offer is no longer available',
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
        Offer $offer,
        StoreReservationRequest $request,
        CreateReservation $createReservation,
    ): JsonResponse {
        return (new ReservationResource($createReservation->handle($offer, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
