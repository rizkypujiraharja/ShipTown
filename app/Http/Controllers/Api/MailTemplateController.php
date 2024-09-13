<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailTemplate\UpdateRequest;
use App\Http\Requests\MailTemplateIndexRequest;
use App\Http\Resources\MailTemplateResource;
use App\Models\MailTemplate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MailTemplateIndexRequest $request): AnonymousResourceCollection
    {
        $mailTemplates = MailTemplate::all();

        return MailTemplateResource::collection($mailTemplates);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, MailTemplate $mailTemplate): MailTemplateResource
    {
        $data = $request->validated();

        if (isset($data['to'])) {
            $data['to'] = implode(', ', $data['to']);
        }

        $mailTemplate->update($data);

        return MailTemplateResource::make($mailTemplate);
    }
}
