<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StoreAcademyServiceRequest;
use App\Http\Requests\Academy\UpdateAcademyServiceRequest;
use App\Http\Resources\Player\AcademyServiceResource;
use App\Models\AcademyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcademyServiceController extends BaseController
{
    public function index(Request $request)
    {
        $services = AcademyService::latest()->paginate($request->input('per_page', 15));
        $services->through(fn ($service) => (new AcademyServiceResource($service))->resolve());

        return $this->sendPaginatedResponse($services, 'Academy services retrieved successfully');
    }

    public function store(StoreAcademyServiceRequest $request)
    {
        $data = $request->safe()->except('images');
        $data['images'] = $this->storeImages($request->file('images', []));
        $service = AcademyService::create($data);

        return $this->sendResponse(new AcademyServiceResource($service), 'Academy service created successfully');
    }

    public function show(AcademyService $service)
    {
        return $this->sendResponse(new AcademyServiceResource($service), 'Academy service retrieved successfully');
    }

    public function update(UpdateAcademyServiceRequest $request, AcademyService $service)
    {
        $data = $request->safe()->except('images');

        if ($request->hasFile('images')) {
            collect($service->images ?? [])->each(fn ($path) => Storage::delete($path));
            $data['images'] = $this->storeImages($request->file('images', []));
        }

        $service->update($data);

        return $this->sendResponse(new AcademyServiceResource($service->fresh()), 'Academy service updated successfully');
    }

    public function destroy(AcademyService $service)
    {
        collect($service->images ?? [])->each(fn ($path) => Storage::delete($path));
        $service->delete();

        return $this->sendResponse(null, 'Academy service deleted successfully');
    }

    private function storeImages(array $images): array
    {
        return collect($images)
            ->map(fn ($image) => $image->store('academy_services'))
            ->all();
    }
}
