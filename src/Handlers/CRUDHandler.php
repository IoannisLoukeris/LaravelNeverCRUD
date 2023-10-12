<?php

namespace IoannisL\LaravelNeverCrud\Handlers;

use IoannisL\LaravelNeverCrud\DataServices\ModelDataService;
use IoannisL\LaravelNeverCrud\Responses\ErrorType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CRUDHandler
{
  protected ModelDataService $_mDS;
  protected array $metaData = [];

  public function __construct(ModelDataService $mDS)
  {
    $this->_mDS = $mDS;
  }

  public function list()
  {
    $data = null;
    $status = null;
    $message = null;
    try {
      $data = $this->_mDS->getAll();
      $status = 'success';
    } catch (QueryException $e) {
      Log::error($e->getMessage());
      $status = 'failure';
      $message = ErrorType::SERVER_ERROR;
    }

    return [
      'status' => $status,
      'message' => $message,
      'data' => $data,
    ];
  }

  public function create($input)
  {
    $status = null;
    $message = null;
    try {
      $newId = $this->_mDS->create($input);

      $this->fillSubtables($this->findSubtablesToFill($input));
      
      $status = 'success';
    } catch (QueryException $e) {
      Log::error($e->getMessage());
      $status = 'failure';
      $message = ErrorType::SERVER_ERROR;
    }

    return [
      'status' => $status,
      'message' => $message,
      'data' => $newId ?? null,
    ];
  }

  public function search(array $paginate = null, $request)
  {
    $data = null;
    $status = null;
    $message = null;
    try {
      $data = $this->_mDS->search($paginate, $request);
      $status = 'success';
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      $status = 'failure';
      $message = ErrorType::SERVER_ERROR;
    }

    return [
      'status' => $status,
      'message' => $message,
      'data' => $data,
    ];
  }

  public function show($id)
  {
    $status = null;
    $message = null;
    $data = null;
    try {
      $data = $this->_mDS->findById($id);
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
    return ['status' => $status, 'message' => $message, 'data' => $data];
  }

  public function update($idToUpdate, $input)
  {
    $status = null;
    $message = null;
    try {
      $this->_mDS->update($idToUpdate, $input);
      $status = 'success';
    } catch (QueryException $e) {
      Log::error($e->getMessage());
      $status = 'failure';
      $message = ErrorType::SERVER_ERROR;
    }
    return ['status' => $status, 'message' => $message];
  }

  public function delete($idToDelete)
  {
    $status = null;
    $message = null;
    $result = $this->_mDS->delete($idToDelete);
    if ($result) {
      $status = 'success';
    } else {
      Log::error('error while deleting ' . $idToDelete);
      $status = 'failure';
      $message = ErrorType::SERVER_ERROR;
    }
    return ['status' => $status, 'message' => $message];
  }

  protected function findSubtablesToFill(array $input)
  {
    if (empty($this->metaData['subTables'])) return [];

    return array_intersect_key($input, $this->metaData['subTables']);
  }

  protected function fillSubtables(array $subtables)
  {
    foreach ($subtables as [$table, $rows]){
      $handler = resolve('App/Handlers/'. $table .'Handler');
      $handler->saveBulk($rows, true);
    }
  }
}
