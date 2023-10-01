<?php

namespace IoannisL\LaravelNeverCrud\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use IoannisL\LaravelNeverCrud\Responses\ErrorType;
use IoannisL\LaravelNeverCrud\Responses\JSONResponse;
use Spatie\QueryBuilder\QueryBuilder;

class CRUDController extends Controller
{
    protected array $_creationValidationRules;
    protected array $_updateValidationRules;
    protected Model $model;

    public function __construct(
        array $creationValidationRules,
        array $updateValidationRules = null,
        Model $model
    ) {
        $this->_creationValidationRules = $creationValidationRules;

        if ($updateValidationRules === null) {
            $this->_updateValidationRules = $creationValidationRules;
        } else {
            $this->_updateValidationRules = $updateValidationRules;
        }

        if (empty($this->_updateValidationRules['id'])) {
            $this->_updateValidationRules['id'] = 'required';
        }

        $this->model = $model;
    }

    /**
     * List.
     *
     * Returns a list of all the instances of the underlying model
     *
     * @return JSON representation of a list of all instances of the underlying model
     */
    public function list()
    {
        $data = null;
        $status = null;
        $message = null;
        try {
            $data = $this->model->get();
            $status = 'success';
        } catch (QueryException $e) {
            Log::error($e->getMessage());
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create.
     *
     * Creates a new option item for the first page
     *
     * @return JSON description of either the success or the error that occurred
     */
    public function create(Request $request)
    {
        $input = $request->all();
        return $this->createWithData($input);
    }

    protected function createWithData($input)
    {
        $validator = Validator::make($input, $this->_creationValidationRules);

        if ($validator->fails()) {
            Log::error($validator->errors()->all());
            return JSONResponse::failure(ErrorType::CREATE_VALIDATION_FAILED, 422);
        }

        $status = null;
        $message = null;
        try {
            $newId = $this->model->create($input);
            $status = 'success';
        } catch (QueryException $e) {
            Log::error($e->getMessage());
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult([
            'status' => $status,
            'message' => $message,
            'data' => $newId ?? null,
        ], 201);
    }

    /**
     * Show.
     *
     * Returns an instance of the underlying model by id
     *
     * @return JSON representation of an instance of the underlying model
     */
    public function show(Request $request)
    {
        $id = $request->route('id') ?? $request->input('id');
        $status = null;
        $message = null;
        $data = null;
        try {
            $data = $this->model->find($id);
            if (!$data) {
                $status = 'failure';
                $message = ErrorType::RESOURCE_NOT_FOUND;
            } else {
                $status = 'success';
            }
        } catch (QueryException $e) {
            Log::error($e->getMessage());
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult(['status' => $status, 'message' => $message, 'data' => $data]);
    }

    /**
     * Delete.
     *
     * Deletes an option item of the first page
     *
     * @param $idToDelete UUID of the option to delete
     *
     * @return JSON description of either the success or the error that occurred
     */
    public function delete(Request $request)
    {
        $idToDelete = $request->route('idToDelete') ?? $request->input('id');
        $status = null;
        $message = null;

        $result = $this->model->destroy($idToDelete);

        if ($result) {
            $status = 'success';
        } else {
            Log::error('error while deleting ' . $idToDelete);
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult(['status' => $status, 'message' => $message]);
    }

    /**
     * Update.
     *
     * Updates an option item of the first page
     *
     * @param $idToUpdate UUID instance used to delete the option
     *
     * @return JSON description of either the success or the error that occurred
     */
    public function update(Request $request)
    {
        $input = $request->all();
        $idToUpdate = $request->route('idToUpdate') ?? $request->input('id');

        return $this->updateWithData($input, $idToUpdate);
    }

    protected function updateWithData($input, $idToUpdate)
    {
        $validator = Validator::make($input, $this->_updateValidationRules);

        if ($validator->fails()) {
            Log::error($validator->errors()->all());
            return JSONResponse::failure(ErrorType::UPDATE_VALIDATION_FAILED, 422);
        }

        $status = null;
        $message = null;

        try {
            $updateTarget = $this->model->find($idToUpdate);
            $updateTarget->fill($input);
            $updateTarget->save();
            $status = 'success';
        } catch (QueryException $e) {
            Log::error($e->getMessage());
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult(['status' => $status, 'message' => $message]);
    }

    public function search(Request $request)
    {
        if ($request->has('filter')) {
            $query = $request->input('filter');
            if (isset($query['all'])) {
                unset($query['all']);
            }
            $request->query->set('filter', $query);
        }

        if ($request->has('include')) {
            $query = $request->input('include');
            $request->query->set('include', $query);
        }

        if ($request->has('paginate')) {
            $paginate = $request->input('paginate');
            $request->query->set('paginate', $paginate);
        }

        if ($request->has('sort')) {
            $sort = Str::snake($request->input('sort'));
            $request->query->set('sort', $sort);
        }

        $data = null;
        $status = null;
        $message = null;

        try {
            $queryBuilder = $this->searchableQueryBuilder($request);
            if ($paginate) {
                $data = $queryBuilder->paginate(
                    $paginate['per_page'],
                    '*',
                    'data.paginate.page'
                );
            } else {
                $data = $queryBuilder->get();
            }
            $status = 'success';
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $status = 'failure';
            $message = ErrorType::SERVER_ERROR;
        }

        return $this->handleResult([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function handleResult($CRUDresult, $successResponse = 200)
    {
        if ($CRUDresult['status'] == 'success') {
            return JSONResponse::success(
                $CRUDresult['data'] ?? null,
                $successResponse
            );
        }

        return JSONResponse::failure(
            $CRUDresult['message'],
            $CRUDresult['message'] === ErrorType::SERVER_ERROR ? 500 : 422,
            $CRUDresult['errors'] ?? null
        );
    }

    protected function searchableQueryBuilder($request)
    {
        return QueryBuilder::for($this->model, $request)->allowedFilters(
            $this->model->searchable
        );
    }
}
