<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContactUs\ContactUsRequest;
use App\Http\Resources\Api\V1\ContactUs\ContactUsResource;
use App\Models\ContactUs;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    use ApiResponse;
    //
    public function store(ContactUsRequest $request)
    {
        try {
            $data = $request->validated();

            $contact_us = ContactUs::create($data);

            return $this->sendResponse(new ContactUsResource($contact_us), 'Your comment has been sent to the admins.',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
