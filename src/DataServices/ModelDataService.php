<?php

namespace IoannisL\LaravelNeverCrud\DataServices;

use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class ModelDataService
{
  /**
   * Model $_model
   **/
  protected Model $_model;

  public function __construct(Model $model)
  {
    $this->_model = $model;
  }

  public function create(array $input)
  {
    $tempModel = $this->_model->create($input);
    $pkColumn = $tempModel->getKeyName();
    $this->_model = $tempModel;
    return $this->_model->$pkColumn;
  }

  public function update($idToUpdate, $input)
  {
    $updateTarget = $this->_model->find($idToUpdate);
    $updateTarget->fill($input);
    $updateTarget->save();
  }

  public function delete($id)
  {
    return $this->_model->destroy($id);
  }

  public function getAll()
  {
    return $this->_model->get();
  }

  public function search(array $paginate = null, $request)
  {
    $queryBuilder = $this->searchableQueryBuilder($request);

    if ($paginate) {
      return $queryBuilder->paginate(
        $paginate['per_page'],
        '*',
        'data.paginate.page'
      );
    } else {
      return $queryBuilder->get();
    }
  }

  public function findById(string $id, array $relation = null)
  {
    if ($relation) {
      return $this->_model->with($relation)->find($id);
    } else {
      return $this->_model->find($id);
    }
  }

  public function findByWhere(array $query, string $orderBy = null)
  {
    if ($orderBy!==null) {
      return $this->_model->where($query)->orderBy($orderBy);
    }
    return $this->_model->where($query);
  }

  protected function searchableQueryBuilder($request)
  {
    return QueryBuilder::for($this->_model, $request)->allowedFilters(
      $this->_model->searchable
    );
  }
}
