<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;

use App\Models\Appointment;
use Illuminate\Auth\Events\Validated;


class AppointmentController extends Controller
{
    public function index() 
    {

       return Appointment::query()
       ->with('client:id,first_name,last_name')
       ->when(request('status'), function($query){
            return $query->where('status',AppointmentStatus::from(request('status')));
       })
       ->latest()
       ->paginate()
       ->through(fn ($appointment) => [
            'id'=> $appointment->id,
            'start_time' => $appointment-> start_time->format('F j, Y, g:i a'),
            'end_time' => $appointment-> end_time->format('F j, Y, g:i a'),
            'status' => [
                'name' => $appointment->status->name,
                'color' => $appointment->status->color(),
                        ],
            'client' => $appointment->client,
       ]);

    }

    public function store()
    {
          request()->validate([
               'client_id'  => 'required',
               'title' => 'required',
               'start_time' => 'required',
               'end_time' => 'required',
               'description' => 'required',
          ],[
               'client_id.required' => 'The client name field is required.' 
          ]);
          Appointment::create([
               'title' => request('title'),
               'client_id' => request('client_id'),
               'start_time' => request('start_time'),
               'end_time' => request('end_time'),
               'description' => request('description'),
               'status' => AppointmentStatus::SCHEDULED,

          ]);

          return response()->json(['message' => 'Scheduled successfully']);

    }
}
