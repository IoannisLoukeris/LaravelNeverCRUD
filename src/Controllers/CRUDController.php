<?php

namespace LaravelNeverCrud\Controllers;

use App\Http\Controllers\Controller;
use LaravelNeverCrud\Handlers\CRUDHandler;
use LaravelNeverCrud\Responses\ErrorType;
use LaravelNeverCrud\Responses\JSONResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CRUDController extends Controller
{
  protected array $_creationValidationRules;
  protected array $_updateValidationRules;
  protected CRUDHandler $crudHandler;

  public function __construct(
    CRUDHandler $crudHandler,
    array $creationValidationRules,
    array $updateValidationRules = null
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

    $this->crudHandler = $crudHandler;
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
    $CRUDList = $this->crudHandler->list();

    return $this->handleResult($CRUDList);
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
    $validator = Validator::make($input, $this->_creationValidationRules);

    if ($validator->fails()) {
      Log::error($validator->errors()->all());
      return JSONResponse::failure(ErrorType::CREATE_VALIDATION_FAILED, 422);
    }

    $CRUDcreate = $this->crudHandler->create($input);

    return $this->handleResult($CRUDcreate, 201);
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
    $CRUDshow = $this->crudHandler->show($id);

    return $this->handleResult($CRUDshow);
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
    $CRUDdelete = $this->crudHandler->delete($idToDelete);

    return $this->handleResult($CRUDdelete);
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

    $validator = Validator::make($input, $this->_updateValidationRules);

    if ($validator->fails()) {
      Log::error($validator->errors()->all());
      return JSONResponse::failure(ErrorType::UPDATE_VALIDATION_FAILED, 422);
    }

    $CRUDupdate = $this->crudHandler->update($idToUpdate, $input);

    return $this->handleResult($CRUDupdate);
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

    $CRUDsearch = $this->crudHandler->search($request->query('paginate'), $request);

    return $this->handleResult($CRUDsearch);
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
}
