<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\ExchangeRate\IndexExchangeRateRequest;
use App\Http\Requests\Admin\ExchangeRate\StoreExchangeRateRequest;
use App\Http\Requests\Admin\ExchangeRate\UpdateExchangeRateRequest;
use App\Http\Requests\Admin\ExchangeRate\SyncExchangeRateRequest;
use App\Http\Resources\Admin\ExchangeRateCollection;
use App\Http\Resources\Admin\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Services\Admin\ExchangeRateService;

class ExchangeRateController extends AdminController
{
    public function __construct(
        protected ExchangeRateService $service
    ) {
    }

    /**
     * List Exchange Rates
     */
    public function index(
        IndexExchangeRateRequest $request
    )
    {
        return new ExchangeRateCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Store Exchange Rate
     */
    public function store(
        StoreExchangeRateRequest $request
    )
    {
        $rate = $this->service->store(

            $request->validated()

        );

        return $this->success(

            new ExchangeRateResource($rate),

            'Exchange rate created successfully.'

        );
    }

    /**
     * Show Exchange Rate
     */
    public function show(
        ExchangeRate $exchangeRate
    )
    {
        return $this->success(

            new ExchangeRateResource(

                $this->service->show(
                    $exchangeRate
                )

            )

        );
    }

    /**
     * Update Exchange Rate
     */
    public function update(
        UpdateExchangeRateRequest $request,
        ExchangeRate $exchangeRate
    )
    {
        $rate = $this->service->update(

            $exchangeRate,

            $request->validated()

        );

        return $this->success(

            new ExchangeRateResource($rate),

            'Exchange rate updated successfully.'

        );
    }

    /**
     * Delete Exchange Rate
     */
    public function destroy(
        ExchangeRate $exchangeRate
    )
    {
        $this->service->destroy(
            $exchangeRate
        );

        return $this->success(

            [],

            'Exchange rate deleted successfully.'

        );
    }

    /**
     * Synchronize Exchange Rates
     */
    public function sync(
        SyncExchangeRateRequest $request
    )
    {
        return $this->success(

            $this->service->sync(

                $request->validated()

            ),

            'Exchange rates synchronized successfully.'

        );
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        return $this->success(

            $this->service->statistics()

        );
    }
}