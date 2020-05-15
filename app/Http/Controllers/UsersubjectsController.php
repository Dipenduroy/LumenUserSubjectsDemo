<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usersubject;

class UsersubjectsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Get user subjects.
     *
     * @return Response
     */
    public function index($userid) {
        return response()->json(Usersubject::where('user_id', $userid)->get());
    }

    /**
     * Add a user's subject.
     *
     * @return Response
     */
    public function store(Request $request,$userid) {
        $this->validate($request, [
            'user_subject' => 'required'
        ]);
        $user_subject = $request->input('user_subject', []);
        $data = [];
        foreach ($user_subject as $subject) {
            $data[] = array(
                'user_id' => $userid, 'subject' => $subject,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }
        Usersubject::insert($data);
        return response()->json(Usersubject::where('user_id', $userid)->get(),201);
    }

    /**
     * Delete a user's subject.
     *
     * @return Response
     */
    public function destroy($userid,$id) {
        Usersubject::findOrFail($id);                
        Usersubject::where('user_id', $userid)->where('id', $id)->delete();
        return response()->json([],204);
    }
    
    /**
     * Update a user's subject.
     *
     * @return Response
     */
    public function update(Request $request,$userid,$id) {
        $this->validate($request, [
            'user_subject' => 'required'
        ]);
        $user_subject = $request->input('user_subject');
        $model=Usersubject::findOrFail($id);
        $model->subject=$user_subject;
        $model->save();     
        return response()->json(Usersubject::where('user_id', $userid)->get(),200);
    }

}
