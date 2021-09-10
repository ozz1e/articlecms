<?php

namespace App\Admin\Repositories;

use App\Models\PostBlock as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PostBlock extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

}
