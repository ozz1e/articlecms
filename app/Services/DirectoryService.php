<?php


namespace App\Services;


use App\Models\Directory;

class DirectoryService
{
    //模板id
    protected $id;

    public function setId( $id )
    {
        $this->id = $id;
        return $this;
    }

    public function delete()
    {
        return (Directory::destroy($this->id) !== 0);
    }

}
