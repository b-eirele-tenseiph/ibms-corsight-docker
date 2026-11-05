<?php

namespace App\Exceptions;

use App\Services\FlashMessage\FlashMessageService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // render               (1.0) Renders custom error page for HTTP errors.
    // getErrorData         (2.0) Prepares error data array with status, title,
    //                            and message for display.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> render
     * <Function> Renders custom error page for HTTP errors.
     *
     * @param $request
     * @param \Throwable $error
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function render($request, \Throwable $error): Response|null
    {
        // Don't handle authentication and validation exceptions
        if ($error instanceof AuthenticationException || $error instanceof ValidationException) {
            return null;
        }

        $status = $error instanceof HttpExceptionInterface ? $error->getStatusCode() : 500;

        $isProduction = !app()->environment(['local', 'testing']);

        if ($status === 419) {
            return back()->with(FlashMessageService::info(__('flash.expired')));
        }

        if ($status >= 400 && $isProduction) {
            return inertia(
                'error/Index',
                $this->getErrorData($status, $error)
            )
                ->toResponse($request)
                ->setStatusCode($status);
        }

        return null;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getErrorData
     * <Function> Prepares error data array with status, title, and message for display.
     *
     * @param int $status
     * @param \Throwable $error
     * @return array
     */
    private function getErrorData(int $status, \Throwable $e): array
    {
        $errors = __('errors.error_messages');

        $data = $errors[$status] ?? ['title' => $status, 'message' => $e->getMessage()];

        // Override with custom exception message if provided
        if (isset($errors[$status]) && $e instanceof HttpExceptionInterface && $e->getMessage()) {
            $data['message'] = $e->getMessage();
        }

        return [
            'status' => $status,
            'title' => $data['title'],
            'message' => $data['message'],
        ];
    }
}
