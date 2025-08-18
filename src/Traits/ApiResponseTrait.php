<?php

declare(strict_types=1);

namespace QuantumTecnology\HandlerBasicsExtension\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use QuantumTecnology\HandlerBasicsExtension\Exceptions\ApiResponseException;

trait ApiResponseTrait
{
    /**
     * OkResponse function.
     */
    public function okResponse(
        array|object|null $data = null,
        ?string $message = null,
        array $arrayToAppend = [],
        bool $allowedInclude = false,
        bool $allowedFilters = false,
    ): JsonResponse {
        return $this->customResponse(
            data: $data,
            message: $message ?? __('messages.successfully.show'),
            status: Response::HTTP_OK,
            arrayToAppend: $arrayToAppend,
            allowedInclude: $allowedInclude,
            allowedFilters: $allowedFilters
        );
    }

    /**
     * BadRequestResponse function.
     */
    public function badRequestResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('Bad Request'),
            status: Response::HTTP_BAD_REQUEST,
            exception: true
        );
    }

    /**
     * ForbiddenResponse function.
     */
    public function forbiddenResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('Forbidden'),
            status: Response::HTTP_FORBIDDEN,
            exception: true
        );
    }

    /**
     * UnauthorizedResponse function.
     */
    public function unauthorizedResponse(?string $message = null): void
    {
        $this->customResponse(
            message: $message ?? __('messages.successfully.show'),
            status: Response::HTTP_UNAUTHORIZED,
            exception: true
        );
    }

    /**
     * NotFoundResponse function.
     */
    public function notFoundResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = [],
    ): void {
        $this->customResponse(
            message: $message ?? __('messages.errors.notfound'),
            data: $data,
            status: Response::HTTP_NOT_FOUND,
            arrayToAppend: $arrayToAppend,
            exception: true
        );
    }

    /**
     * UnprocessableEntityResponse function.
     */
    public function unprocessableEntityResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = [],
    ): void {
        $this->customResponse(
            message: $message ?? __('messages.errors.validation'),
            data: $data,
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            arrayToAppend: $arrayToAppend,
            exception: true
        );
    }

    /**
     * InternalServerErrorResponse function.
     */
    public function internalServerErrorResponse(
        ?string $message = null,
        array|object|null $data = null,
        array $arrayToAppend = [],
    ): void {
        $this->customResponse(
            message: $message ?? __('A API está temporariamente em manutenção, tente novamente mais tarde!'),
            data: $data,
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            arrayToAppend: $arrayToAppend,
            exception: true
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
        bool $allowedFilters = false,
        bool $exception = false,
        array $arrayToAppend = [],
    ): JsonResponse {
        $data = is_array($data) ? (object) $data : $data;

        $content = [
            'success' => $status >= 200 && $status < 300,
            'message' => $message ?? 'Response is successful!',
        ];

        if (
            count($this->getAllowedIncludes()) > 0
            && $allowedInclude
            && 'production' !== config('app.env')
        ) {
            $content['allowed_includes'] = $this->getAllowedIncludes();
        }

        if (
            count($this->allowedFilters ?? []) > 0
            && $allowedFilters
            && 'production' !== config('app.env')
        ) {
            $content['allowed_filters'] = $this->allowedFilters;
        }

        if (!is_null($data)) {
            if (isset($data->resource) && $data->resource instanceof LengthAwarePaginator) {
                $content['data'] = $data->items();

                $content['pagination'] = [
                    'total'          => $data->total(),
                    'current_page'   => $data->currentPage(),
                    'next_page'      => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                    'last_page'      => $data->lastPage(),
                    'per_page'       => $data->perPage(),
                    'max_per_page'   => config('perpage.max_per_page'),
                    'has_more_pages' => $data->hasMorePages(),
                ];
            } elseif (isset($data->resource) && $data->resource instanceof Paginator) {
                $content['data'] = $data->items();

                $content['pagination'] = [
                    'current_page'   => $data->currentPage(),
                    'next_page'      => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                    'per_page'       => $data->perPage(),
                    'max_per_page'   => config('perpage.max_per_page'),
                    'has_more_pages' => $data->hasMorePages(),
                ];
            } else {
                $content['data'] = $data;
            }
        }

        $content += $arrayToAppend;

        throw_if($exception, new ApiResponseException($status, $content));

        return response()->json($content, $status);
    }

    public function setAllowedIncludes(array $allowedIncludes): void
    {
        if (property_exists($this, 'allowedIncludes')) {
            $this->allowedIncludes = $allowedIncludes;
        }
    }

    public function getAllowedIncludes(): array
    {
        return property_exists($this, 'allowedIncludes') ? $this->allowedIncludes : [];
    }

    /**
     * CheckIncludes function.
     */
    public function checkIncludes(): void
    {
        $include = collect(explode(',', request()->get('includes', '')))
            ->map(function ($item) {
                [$item] = explode(':', $item);

                return $item;
            })
            ->implode(',');

        if ($include && $diff = array_diff(explode(',', $include), $this->getAllowedIncludes())) {
            $this->forbiddenResponse("The following includes are not allowed: '".implode(',', $diff)."', enabled: '".implode(',', $this->getAllowedIncludes())."'");
        }
    }
}
