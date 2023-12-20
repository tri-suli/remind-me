<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository
{
    /**
     * The eloquent model instance
     *
     * @var Model|mixed
     */
    protected Model $model;

    /**
     * Create a new repository instance
     *
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->model = app()->make($this->eloquent());
    }

    /**
     * Get all records of eloquent model
     *
     * @param  array  $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Create a new database record and return the eloquent
     * model instance
     *
     * @param  array  $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * Get one record by id
     *
     * @param  int  $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Update existing record by id
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return Model
     */
    public function update(int $id, array $attributes): Model
    {
        $this->find($id)->update($attributes);

        return $this->find($id);
    }

    /**
     * Delete one record by id
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }

    /**
     * Get all records with specified limit
     *
     * @param  int  $limit
     * @return Collection
     */
    public function take(int $limit): Collection
    {
        return $this->model->limit($limit)->get();
    }

    /**
     * Get the total database records of eloquent model
     *
     * @return int
     */
    public function count(): int
    {
        return $this->all()->count();
    }

    /**
     * Get the eloquent model instance
     *
     * @return Model
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * Get eloquent model abstract
     *
     * @return string
     */
    abstract protected function eloquent(): string;
}
