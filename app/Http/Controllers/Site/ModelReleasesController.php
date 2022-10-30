<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\User;
use Response;
use MotionArray\Services\Product\ModelReleaseService;
use Redirect;
use Auth;

class ModelReleasesController extends BaseController
{
    /**
     * @var ModelReleaseService
     */
    private $modelReleaseService;

    public function __construct(ModelReleaseService $modelReleaseService)
    {
        $this->modelReleaseService = $modelReleaseService;
    }

    /**
     * Download ModelRelease
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function download($id)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ModelRelease $modelRelease */
        $modelRelease = ModelRelease::find($id);
        if ($modelRelease === null) {
            return Response::json(['message' => 'Model release not found'], 404);
        }

        try {
            $signedUrl = $this->modelReleaseService->getUrlIfAuthorized($user, $modelRelease);
        } catch (UnauthorizedException $e) {
            return Response::json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return Response::json(['message' => 'Could not retrieve download URL'], 500);
        }

        return Redirect::to($signedUrl);
    }

    /**
     * Download ModelRelease
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ModelRelease $modelRelease */
        $modelRelease = ModelRelease::find($id);
        if ($modelRelease === null) {
            return Response::json(['message' => 'Model release not found'], 404);
        }

        try {
            $this->modelReleaseService->deleteIfAuthorized($user, $modelRelease);
        } catch (UnauthorizedException $e) {
            return Response::json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return Response::json(['message' => 'Could not delete model release'], 500);
        }

        return Response::json(null, 204);
    }

}
