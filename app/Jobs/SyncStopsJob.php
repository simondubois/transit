<?php

namespace App\Jobs;

use App\Enums\LogStatus;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Webmozart\Assert\Assert;
use ZipArchive;

class SyncStopsJob
{
    use CreateLog;
    use Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->startedAt = now();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $archivePath = $this->download();
        $xmlPath = $this->extract($archivePath);
        $stopsData = $this->parse($xmlPath);

        $this->delete();
        $this->create($stopsData);

        $this->createLog(LogStatus::Completed);
    }

    /**
     * Download Stop archive from API to /storage/app/stops.zip.
     */
    protected function download(): string
    {
        $this->createLog(LogStatus::DownloadingStops);

        Http::sink(storage_path('app/stops.zip'))
            ->withHeader('Accept-Encoding', 'deflate')
            ->get('https://opendata.samtrafiken.se/stopsregister-netex-sweden/sweden.zip', [
                'key' => config('trafiklab.stops.key'),
            ])
            ->throw();

        return storage_path('app/stops.zip');
    }

    /**
     * Extract Stop archive from the provided path to /storage/app/stops.zip.
     */
    protected function extract(string $archivePath): string
    {
        $this->createLog(LogStatus::ExtractingStops);

        $archive = new ZipArchive();
        Assert::true($archive->open($archivePath));
        Assert::true($archive->renameName('_stops.xml', 'stops.xml'));
        Assert::true($archive->extractTo(storage_path('app'), 'stops.xml'));
        Assert::true($archive->close());

        return storage_path('app/stops.xml');
    }

    /**
     * Parse XML Stops data stored at the provided location.
     *
     * @return Collection<string, array<string, string>>
     */
    public function parse(string $xmlPath): Collection
    {
        $this->createLog(LogStatus::ParsingStops);

        /** @var Collection<string, array<string, string>> */
        $stopsData = collect();

        foreach ((new SimpleXMLElement($xmlPath, 0, true))->dataObjects->SiteFrame->stopPlaces->StopPlace as $node) {
            foreach ($node->keyList->KeyValue as $key) {
                if ($key->Key->__toString() === 'rikshallplats') {
                    $stopsData->put($node->Name->__toString(), [
                        'name' => $node->Name->__toString(),
                        'code' => $key->Value->__toString(),
                    ]);
                    continue 2;
                }
            }
        }

        return $stopsData;
    }

    /**
     * Delete all existing stops.
     */
    protected function delete(): void
    {
        $this->createLog(LogStatus::DeletingStops);

        Stop::truncate();
    }

    /**
     * Create new Stops from the provided data.
     *
     * @param Collection<string, array<string, string>> $stopsData
     */
    protected function create(Collection $stopsData): void
    {
        $this->createLog(LogStatus::CreatingStops);

        $stopsData->chunk(20000)->each(
            fn (Collection $stopsData) => Stop::insert($stopsData->all())
        );
    }

    /**
     * Holder to attach to newly created logs.
     */
    protected function logHolder(): ?Model
    {
        return null;
    }
}
