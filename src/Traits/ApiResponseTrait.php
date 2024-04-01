<?php

namespace GustavoSantarosa\HandlerBasicsExtension\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    /**
     * OkResponse function.
     */
    public function okResponse(
        array|object|null $data = null,
        ?string $message = null,
        array $arrayToAppend = [],
        bool $allowedInclude = false
    ): JsonResponse {
        return $this->customResponse(
            data: $data,
            message: $message ?? __('messages.successfully.show'),
            status: Response::HTTP_OK,
            arrayToAppend: $arrayToAppend,
            allowedInclude: $allowedInclude
        );
    }

    /**
     * BadRequestResponse function.
     */
    public function badRequestResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('Bad Request'),
            status: Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * ForbiddenResponse function.
     */
    public function forbiddenResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('Forbidden'),
            status: Response::HTTP_FORBIDDEN
        );
    }

    /**
     * UnauthorizedResponse function.
     */
    public function unauthorizedResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('messages.successfully.show'),
            status: Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * NotFoundResponse function.
     */
    public function notFoundResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = []
    ): void {
        $this->customResponse(
            message: $message ?? __('messages.errors.notfound'),
            data: $data,
            status: Response::HTTP_NOT_FOUND,
            arrayToAppend: $arrayToAppend
        );
    }

    /**
     * UnprocessableEntityResponse function.
     */
    public function unprocessableEntityResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = []
    ): void {
        $this->customResponse(
            message: $message ?? __('messages.errors.validation'),
            data: $data,
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            arrayToAppend: $arrayToAppend
        );
    }

    /**
     * InternalServerErrorResponse function.
     */
    public function internalServerErrorResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = []
    ): void {
        $this->customResponse(
            message: $message ?? __('A API está temporariamente em manutenção, tente novamente mais tarde!'),
            data: $data,
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            arrayToAppend: $arrayToAppend
        );
    }

    /**
     * AbortResponse function.
     */
    public function abortResponse(int $code = 0, ?string $message = null): void
    {
        $this->customResponse(
            message: $message,
            status: $code,
        );
    }

    public function customResponse(
        array|object|null $data = null,
        ?string $message = null,
        int $status = 200,
        bool $allowedInclude = false,
        array $arrayToAppend = []
    ): JsonResponse {
        $data = is_array($data) ? (object) $data : $data;

        $content = [
            'success' => $status >= 200 && $status < 300,
            'message' => $message ?? 'Response is successful!',
        ];

        if ($allowedInclude) {
            $content['allowed_includes'] = [];
        }

        if (!is_null($data)) {
            if ($data instanceof LengthAwarePaginator) {
                $content['data'] = $data->items();

                $content['pagination'] = [
                    'total'          => $data->total(),
                    'current_page'   => $data->currentPage(),
                    'next_page'      => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                    'last_page'      => $data->lastPage(),
                    'per_page'       => $data->perPage(),
                    'has_more_pages' => $data->hasMorePages(),
                ];
            } else {
                $content['data'] = $data;
            }

            if (isset($this->defaultService) && $this->defaultService->getModel()) {
                if ($allowedInclude) {
                    $content['allowed_includes'] = $this->defaultService->getModel()->allowedIncludes;
                }
            }
        }

        $content += $arrayToAppend;

        return response()->json($content, $status);
    }
}
