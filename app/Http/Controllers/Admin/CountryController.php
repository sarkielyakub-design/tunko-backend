<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Country\IndexCountryRequest;
use App\Http\Requests\Admin\Country\StoreCountryRequest;
use App\Http\Requests\Admin\Country\UpdateCountryRequest;
use App\Http\Resources\Admin\CountryCollection;
use App\Http\Resources\Admin\CountryResource;
use App\Models\Country;
use App\Services\Admin\CountryService;

class CountryController extends AdminController
{
    public function __construct(
        protected CountryService $service
    ) {
    }

    /**
     * List Countries
     */
    public function index(
        IndexCountryRequest $request
    )
    {
        return new CountryCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Create Country
     */
    public function store(
        StoreCountryRequest $request
    )
    {
        $country = $this->service->store(
            $request->validated()
        );

        return $this->success(

            new CountryResource($country),

            'Country created successfully.',

            201

        );
    }

    /**
     * Show Country
     */
    public function show(
        Country $country
    )
    {
        return $this->success(

            new CountryResource(

                $this->service->show($country)

            )

        );
    }

    /**
     * Update Country
     */
    public function update(
        UpdateCountryRequest $request,
        Country $country
    )
    {
        $country = $this->service->update(

            $country,

            $request->validated()

        );

        return $this->success(

            new CountryResource($country),

            'Country updated successfully.'

        );
    }

    /**
     * Delete Country
     */
    public function destroy(
        Country $country
    )
    {
        $this->service->destroy($country);

        return $this->success(

            null,

            'Country deleted successfully.'

        );
    }

    /**
     * Activate Country
     */
    public function activate(
        Country $country
    )
    {
        return $this->success(

            new CountryResource(

                $this->service->activate($country)

            ),

            'Country activated successfully.'

        );
    }

    /**
     * Deactivate Country
     */
    public function deactivate(
        Country $country
    )
    {
        return $this->success(

            new CountryResource(

                $this->service->deactivate($country)

            ),

            'Country deactivated successfully.'

        );
    }

    /**
     * Update Exchange Rate
     */
    public function updateExchangeRate(
        Country $country
    )
    {
        request()->validate([

            'exchange_rate' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);

        return $this->success(

            new CountryResource(

                $this->service->updateExchangeRate(

                    $country,

                    request('exchange_rate')

                )

            ),

            'Exchange rate updated successfully.'

        );
    }
}