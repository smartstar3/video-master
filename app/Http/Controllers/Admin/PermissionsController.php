<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Permission;
use View;
use Request;
use Redirect;

class PermissionsController extends BaseController
{


    /**
     * Permission Repository
     *
     * @var Permission
     */
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $permissions = $this->permission->all();

        return View::make('admin.permissions.index', compact('permissions'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('admin.permissions.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $this->permission->fill(Request::all());

        if ($this->permission->save()) {
            return Redirect::route('mabackend.permissions.index');
        }

        return Redirect::route('mabackend.permissions.create')
            ->withInput()
            ->withErrors($this->permission->errors);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $permission = $this->permission->findOrFail($id);

        return View::make('admin.permissions.show', compact('permission'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $permission = $this->permission->find($id);

        if (is_null($permission)) {
            return Redirect::route('mabackend.permissions.index');
        }

        return View::make('admin.permissions.edit', compact('permission'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Request::all(), '_method');

        $permission = $this->permission->find($id);

        if ($permission->update($input)) {
            return Redirect::route('mabackend.permissions.index');
        }

        return Redirect::route('mabackend.permissions.edit', $id)
            ->withInput()
            ->withErrors($permission->errors);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $this->permission->find($id)->delete();

        return Redirect::route('mabackend.permissions.index');
    }

}
