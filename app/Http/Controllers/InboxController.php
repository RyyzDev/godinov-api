<?php

namespace App\Http\Controllers;
use App\Models\Inbox;
use Illuminate\Http\Request;
use App\Http\Resources\InboxResource;
use App\Http\Resources\DetailInboxResource;
use App\Http\Resources\EditInboxResource;
use Illuminate\Support\Facades\DB;


class InboxController extends Controller
{


	public function index(){
    	$inbox = Inbox::All();
    	//return response()->json($inbox);
    	return InboxResource::collection($inbox);

	}

	public function detail($id){
		$inbox = Inbox::findOrFail($id);
		return new DetailInboxResource($inbox);
	}

	public function updateStatus(Request $request, $id){
		$validated = $request->validate([
			'status' => 'required|boolean'
		]);

		$inbox = Inbox::findOrFail($id);
		$inbox->update($request->all());
		return (new EditInboxResource($inbox))
            ->additional([
                'status_code' => 200,
                'message' => 'Status berhasil diperbarui!'
            ]);
	}


	public function deleteInbox($id){
		$inbox = Inbox::findOrFail($id);
		$inbox->delete();
		return (new EditInboxResource($inbox))
		->additional([
			'status_code' => 200,
			'message' => 'Data berhasil dibuang!'
		]);
	}

    public function store(Request $request){
    	$request->validate([
    		'name'=>'required|max:255',
    		'email'=>'required|email',
    		'contact'=>'required|max:20',
    		'company'=>'nullable|max:100',
    		'address'=>'nullable',
    		'description'=>'nullable',
    		'status'=>'boolean',
    	]);


    	$inbox = Inbox::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'contact' => $request->contact,
    		'company' => $request->company,
    		'address' => $request->address,
    		'description' => $request->description,
    		'status'=>$request->status ?? 0,
    	]);

    	$message = "Data Berhasil Disimpan";
        if($message != null){
            return response()->json([
            'status_code' => 201,
            'message' => $message,
        ], 201);
        }
    
    }

    public function sumProcessed(Request $request){
        $totalProses = Inbox::where('status', 1)->sum('status');
        return response()->json([
            "status_code" => 200,
            "Total Sudah Diproses" => $totalProses
        ]);
    }

    public function sumInbox(Request $request){
        $inbox = Inbox::all();
        $totalInbox = $inbox->count();
        return response()->json([
            "status_code" => 200,
            "Total Inbox" => $totalInbox
        ]);
    }

     

    public function sumClients(Request $request){
        $totalClients = DB::table('inboxes')->count(DB::raw('DISTINCT name'));
        return response()->json(["Total Klien" => $totalClients]);
    }



}
