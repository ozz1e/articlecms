<?php

namespace App\Admin\Repositories;

use App\Models\Directory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Directory extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
