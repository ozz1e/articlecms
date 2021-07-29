<?php

namespace App\Admin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;

class Editor extends \Dcat\Admin\Repositories\EloquentRepository
{
    protected $eloquentClass = \App\Models\Editor::class;
}
